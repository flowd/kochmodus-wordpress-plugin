<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Tests\Unit\Domain\Button;

use Flowd\KochmodusWordpressPlugin\Domain\Button\RecipeUri;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RecipeUriTest extends TestCase
{
    public function test_it_creates_from_valid_url(): void
    {
        $uri = new RecipeUri('https://example.com/rezept/panna-cotta/');

        $this->assertSame('https://example.com/rezept/panna-cotta/', $uri->value());
    }

    public function test_it_trims_whitespace(): void
    {
        $uri = new RecipeUri('  https://example.com/rezept/  ');

        $this->assertSame('https://example.com/rezept/', $uri->value());
    }

    public function test_it_throws_on_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RecipeUri('');
    }

    public function test_it_throws_on_invalid_url(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RecipeUri('not-a-url');
    }

    public function test_it_accepts_http_scheme(): void
    {
        $uri = new RecipeUri('http://example.com/rezept/');

        $this->assertSame('http://example.com/rezept/', $uri->value());
    }

    /** @dataProvider dangerousSchemeProvider */
    public function test_it_rejects_non_http_schemes(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RecipeUri($input);
    }

    /** @return array<string, array{0: string}> */
    public static function dangerousSchemeProvider(): array
    {
        return [
            'javascript scheme' => ['javascript:alert(1)'],
            'data scheme' => ['data:text/html,<script>alert(1)</script>'],
            'file scheme' => ['file:///etc/passwd'],
            'ftp scheme' => ['ftp://example.com/recipe'],
            'vbscript scheme' => ['vbscript:msgbox(1)'],
        ];
    }

    public function test_to_string_returns_value(): void
    {
        $uri = new RecipeUri('https://example.com/rezept/');

        $this->assertSame('https://example.com/rezept/', (string)$uri);
    }
}
