<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Tests\Unit\Domain\Button;

use Flowd\KochmodusWordpressPlugin\Domain\Button\Alignment;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AlignmentTest extends TestCase
{
    /** @dataProvider validAlignmentProvider */
    public function test_it_accepts_valid_alignments(string $input, string $expected): void
    {
        $alignment = new Alignment($input);

        $this->assertSame($expected, $alignment->value());
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function validAlignmentProvider(): array
    {
        return [
            'left' => ['left', 'left'],
            'center' => ['center', 'center'],
            'right' => ['right', 'right'],
            'uppercase' => ['CENTER', 'center'],
            'surrounding whitespace' => ['  right  ', 'right'],
        ];
    }

    /** @dataProvider invalidAlignmentProvider */
    public function test_it_rejects_invalid_alignments(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Alignment($input);
    }

    /** @return array<string, array{0: string}> */
    public static function invalidAlignmentProvider(): array
    {
        return [
            'empty' => [''],
            'whitespace only' => ['   '],
            'justify' => ['justify'],
            'block alignment' => ['wide'],
            'css injection' => ['center; position: fixed'],
            'garbage' => ['middle'],
        ];
    }

    public function test_to_string_returns_value(): void
    {
        $alignment = new Alignment('center');

        $this->assertSame('center', (string)$alignment);
    }
}
