<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Domain\Settings;

interface SettingsRepositoryInterface
{
    /**
     * @return PluginSettings|null Null when settings have not been saved yet.
     */
    public function find(): ?PluginSettings;

    public function save(PluginSettings $settings): void;

    public function delete(): void;
}
