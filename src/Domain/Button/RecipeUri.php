<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Domain\Button;

use InvalidArgumentException;

final class RecipeUri
{
    private string $value;

    private const ALLOWED_SCHEMES = ['http', 'https'];

    public function __construct(string $value)
    {
        $trimmed = trim($value);
        if ($trimmed === '' || filter_var($trimmed, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(
                sprintf('Invalid recipe URI: "%s".', $value)
            );
        }

        $scheme = strtolower((string)parse_url($trimmed, PHP_URL_SCHEME));
        if (!in_array($scheme, self::ALLOWED_SCHEMES, true)) {
            throw new InvalidArgumentException(
                sprintf('Recipe URI must use http or https scheme: "%s".', $value)
            );
        }

        $this->value = $trimmed;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
