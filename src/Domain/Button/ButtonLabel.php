<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Domain\Button;

final class ButtonLabel
{
    public const DEFAULT = 'Kochmodus starten';

    private string $value;

    public function __construct(string $value = self::DEFAULT)
    {
        $trimmed = trim($value);
        $this->value = $trimmed !== '' ? $trimmed : self::DEFAULT;
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
