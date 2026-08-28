<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Tests\Unit\Domain\Button;

use Flowd\KochmodusWordpressPlugin\Domain\Button\ButtonLabel;
use PHPUnit\Framework\TestCase;

final class ButtonLabelTest extends TestCase
{
    public function test_it_uses_default_when_no_value_given(): void
    {
        $label = new ButtonLabel();

        $this->assertSame('Kochmodus starten', $label->value());
    }

    public function test_it_uses_custom_value(): void
    {
        $label = new ButtonLabel('Rezept kochen');

        $this->assertSame('Rezept kochen', $label->value());
    }

    public function test_it_falls_back_to_default_on_empty_string(): void
    {
        $label = new ButtonLabel('');

        $this->assertSame('Kochmodus starten', $label->value());
    }

    public function test_it_falls_back_to_default_on_whitespace_only(): void
    {
        $label = new ButtonLabel('   ');

        $this->assertSame('Kochmodus starten', $label->value());
    }

    public function test_it_trims_whitespace(): void
    {
        $label = new ButtonLabel('  Rezept kochen  ');

        $this->assertSame('Rezept kochen', $label->value());
    }

    public function test_to_string_returns_value(): void
    {
        $label = new ButtonLabel('Rezept kochen');

        $this->assertSame('Rezept kochen', (string)$label);
    }
}
