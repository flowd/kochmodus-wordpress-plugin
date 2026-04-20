<?php

declare(strict_types = 1);

namespace Kochmodus\Domain\Settings;

use InvalidArgumentException;

final class AccessToken
{
    private const MAX_LENGTH = 255;

    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Access token must not be empty.');
        }
        if (strlen($trimmed) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Access token must not exceed %d characters.', self::MAX_LENGTH)
            );
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
