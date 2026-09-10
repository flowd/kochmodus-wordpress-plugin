<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Domain\Button;

use InvalidArgumentException;

final class RecipeUri
{
    private string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);
        if ($trimmed === '' || filter_var($trimmed, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid recipe URI.');
        }

        if (stripos($trimmed, 'http://') !== 0 && stripos($trimmed, 'https://') !== 0) {
            throw new InvalidArgumentException('Recipe URI must use http or https scheme.');
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
