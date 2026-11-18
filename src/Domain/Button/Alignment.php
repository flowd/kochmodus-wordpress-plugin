<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Domain\Button;

use InvalidArgumentException;

/**
 * Horizontal position of the button inside its wrapper. Restricted to a fixed
 * list because the value ends up in an inline style.
 */
final class Alignment
{
    private const ALLOWED = ['left', 'center', 'right'];

    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new InvalidArgumentException('Invalid alignment value.');
        }
        $this->value = $normalized;
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
