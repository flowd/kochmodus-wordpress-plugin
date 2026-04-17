<?php

declare(strict_types = 1);

namespace Kochmodus\Domain\Button;

use InvalidArgumentException;

final class RecipeUri
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);
        if ($trimmed === '' || filter_var($trimmed, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(
                sprintf('Invalid recipe URI: "%s".', $value)
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
