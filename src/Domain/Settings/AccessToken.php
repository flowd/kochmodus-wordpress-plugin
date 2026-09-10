<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Domain\Settings;

use InvalidArgumentException;

final class AccessToken
{
    private const MAX_LENGTH = 255;

    private string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Access token must not be empty.');
        }
        if (strlen($trimmed) > self::MAX_LENGTH) {
            throw new InvalidArgumentException('Access token must not exceed 255 characters.');
        }
        $this->value = $trimmed;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
