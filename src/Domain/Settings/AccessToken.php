<?php

declare(strict_types = 1);

namespace Kochmodus\Domain\Settings;

use InvalidArgumentException;

final class AccessToken
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Access token must not be empty.');
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
