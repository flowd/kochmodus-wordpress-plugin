<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Application\Settings;

use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;

interface SettingsServiceInterface
{
    public function getSettings(): ?PluginSettings;

    public function saveSettings(SaveSettingsCommand $command): PluginSettings;

    public function deleteSettings(): void;
}
