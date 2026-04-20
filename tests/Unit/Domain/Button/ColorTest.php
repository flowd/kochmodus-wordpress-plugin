<?php

declare(strict_types = 1);

namespace Kochmodus\Tests\Unit\Domain\Button;

use InvalidArgumentException;
use Kochmodus\Domain\Button\Color;
use PHPUnit\Framework\TestCase;

final class ColorTest extends TestCase
{
    /**
     * @dataProvider validColorProvider
     */
    public function test_it_accepts_valid_colors(string $input): void
    {
        $color = new Color($input);

        $this->assertSame($input, $color->value());
    }

    /** @return array<string, array{0: string}> */
    public static function validColorProvider(): array
    {
        return [
            'hex 3' => ['#f00'],
            'hex 4 with alpha' => ['#f00a'],
            'hex 6 lowercase' => ['#ff0000'],
            'hex 6 uppercase' => ['#FFAABB'],
            'hex 8 with alpha' => ['#ff0000ff'],
            'rgb' => ['rgb(255, 0, 0)'],
            'rgb no spaces' => ['rgb(255,0,0)'],
            'rgba' => ['rgba(255, 0, 0, 0.5)'],
            'rgba integer alpha' => ['rgba(0, 0, 0, 1)'],
        ];
    }

    /**
     * @dataProvider invalidColorProvider
     */
    public function test_it_rejects_invalid_colors(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Color($input);
    }

    /** @return array<string, array{0: string}> */
    public static function invalidColorProvider(): array
    {
        return [
            'empty' => [''],
            'whitespace only' => ['   '],
            'named color' => ['red'],
            'hex too short' => ['#f'],
            'hex too long' => ['#ff00000f0f'],
            'hex invalid chars' => ['#gghhii'],
            'hex with trailing semicolon' => ['#ff0000;'],
            'rgb with extra property injection' => ['rgb(255,0,0); background: url(https://evil.example)'],
            'css injection with semicolon' => ['red; position: fixed'],
            'url function injection' => ['url(https://evil.example)'],
            'expression injection' => ['expression(alert(1))'],
            'import injection' => ['@import url(https://evil.example)'],
            'hex with space' => ['#ff 0000'],
            'garbage' => ['not-a-color'],
            'rgb missing paren' => ['rgb(255, 0, 0'],
            'rgb with quotes' => ['rgb(255, 0, "0")'],
        ];
    }

    public function test_it_trims_whitespace(): void
    {
        $color = new Color('  #ff0000  ');

        $this->assertSame('#ff0000', $color->value());
    }

    public function test_to_string_returns_value(): void
    {
        $color = new Color('#ff0000');

        $this->assertSame('#ff0000', (string)$color);
    }
}
