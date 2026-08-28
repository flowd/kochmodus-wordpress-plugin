<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Domain\Button;

use InvalidArgumentException;

final class Color
{
    private const HEX_PATTERN = '/^#(?:[0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/';
    private const RGB_PATTERN = '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(?:,\s*(?:0|1|0?\.\d+)\s*)?\)$/';

    private string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);
        if (!$this->isValid($trimmed)) {
            throw new InvalidArgumentException(
                sprintf('Invalid color value: "%s".', $value)
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

    private function isValid(string $value): bool
    {
        if ($value === '') {
            return false;
        }
        return preg_match(self::HEX_PATTERN, $value) === 1
            || preg_match(self::RGB_PATTERN, $value) === 1;
    }
}
