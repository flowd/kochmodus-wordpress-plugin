<?php

declare(strict_types = 1);

namespace Kochmodus\Tests\Unit\Domain\Settings;

use InvalidArgumentException;
use Kochmodus\Domain\Settings\AccessToken;
use PHPUnit\Framework\TestCase;

final class AccessTokenTest extends TestCase
{
    public function test_it_creates_from_valid_string(): void
    {
        $token = new AccessToken('abc123');

        $this->assertSame('abc123', $token->value());
    }

    public function test_it_trims_whitespace(): void
    {
        $token = new AccessToken('  abc123  ');

        $this->assertSame('abc123', $token->value());
    }

    public function test_it_throws_on_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AccessToken('');
    }

    public function test_it_throws_on_whitespace_only(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AccessToken('   ');
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $a = new AccessToken('abc123');
        $b = new AccessToken('abc123');

        $this->assertTrue($a->equals($b));
    }

    public function test_equals_returns_false_for_different_value(): void
    {
        $a = new AccessToken('abc123');
        $b = new AccessToken('xyz789');

        $this->assertFalse($a->equals($b));
    }

    public function test_to_string_returns_value(): void
    {
        $token = new AccessToken('abc123');

        $this->assertSame('abc123', (string)$token);
    }
}
