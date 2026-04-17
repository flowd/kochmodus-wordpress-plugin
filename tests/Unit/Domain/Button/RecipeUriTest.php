<?php

declare(strict_types = 1);

namespace Kochmodus\Tests\Unit\Domain\Button;

use InvalidArgumentException;
use Kochmodus\Domain\Button\RecipeUri;
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

    public function test_to_string_returns_value(): void
    {
        $uri = new RecipeUri('https://example.com/rezept/');

        $this->assertSame('https://example.com/rezept/', (string)$uri);
    }
}
