<?php

declare(strict_types = 1);

namespace Kochmodus\Application\Settings;

use Kochmodus\Domain\Settings\PluginSettings;

interface SettingsServiceInterface
{
    public function getSettings(): ?PluginSettings;

    public function saveSettings(SaveSettingsCommand $command): PluginSettings;

    public function deleteSettings(): void;
}
