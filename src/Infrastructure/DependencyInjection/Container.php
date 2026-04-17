<?php

declare(strict_types = 1);

namespace Kochmodus\Infrastructure\DependencyInjection;

use RuntimeException;

final class Container
{
    /** @var array<string, callable> */
    private $factories = [];

    /** @var array<string, object> */
    private $instances = [];

    public function set(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     * @return T
     */
    public function get(string $id): object
    {
        if (!isset($this->instances[$id])) {
            if (!isset($this->factories[$id])) {
                throw new RuntimeException("Service not found: {$id}");
            }
            $this->instances[$id] = ($this->factories[$id])($this);
        }
        return $this->instances[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->factories[$id]) || isset($this->instances[$id]);
    }
}
