<?php

declare(strict_types = 1);

namespace Kochmodus\Tests\Unit\Infrastructure;

use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;
use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

abstract class WordPressTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        setUp();
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }
}
