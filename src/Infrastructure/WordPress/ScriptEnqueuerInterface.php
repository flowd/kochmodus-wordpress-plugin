<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress;

interface ScriptEnqueuerInterface
{
    public function markNeeded(): void;

    public function isNeeded(): bool;

    public function maybeEnqueue(): void;
}
