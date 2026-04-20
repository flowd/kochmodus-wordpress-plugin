<?php

declare(strict_types = 1);

namespace Kochmodus\Tests\Unit\Infrastructure\DependencyInjection;

use Kochmodus\Infrastructure\DependencyInjection\Container;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

final class ContainerTest extends TestCase
{
    public function test_get_returns_instance_from_factory(): void
    {
        $container = new Container();
        $service = new stdClass();
        $container->set(stdClass::class, static function () use ($service): stdClass {
            return $service;
        });

        $this->assertSame($service, $container->get(stdClass::class));
    }

    public function test_get_caches_instance_across_calls(): void
    {
        $container = new Container();
        $calls = 0;
        $container->set(stdClass::class, static function () use (&$calls): stdClass {
            $calls++;
            return new stdClass();
        });

        $first = $container->get(stdClass::class);
        $second = $container->get(stdClass::class);

        $this->assertSame($first, $second);
        $this->assertSame(1, $calls);
    }

    public function test_factory_receives_container_for_dependency_resolution(): void
    {
        $container = new Container();
        $dependency = new stdClass();
        $dependency->name = 'dep';
        $container->set('dep', static function () use ($dependency): stdClass {
            return $dependency;
        });
        $container->set('consumer', static function (Container $c): stdClass {
            $obj = new stdClass();
            $obj->dep = $c->get('dep');
            return $obj;
        });

        $consumer = $container->get('consumer');

        $this->assertSame($dependency, $consumer->dep);
    }

    public function test_get_throws_when_service_not_registered(): void
    {
        $container = new Container();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Service not found: missing');

        $container->get('missing');
    }

    public function test_has_returns_false_for_unregistered_service(): void
    {
        $container = new Container();

        $this->assertFalse($container->has('missing'));
    }

    public function test_has_returns_true_after_factory_is_set(): void
    {
        $container = new Container();
        $container->set(stdClass::class, static function (): stdClass {
            return new stdClass();
        });

        $this->assertTrue($container->has(stdClass::class));
    }
}
