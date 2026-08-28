<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Application\Settings;

use Flowd\KochmodusWordpressPlugin\Domain\Button\ButtonLabel;
use Flowd\KochmodusWordpressPlugin\Domain\Button\Color;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\AccessToken;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\SettingsRepositoryInterface;

final class SettingsService implements SettingsServiceInterface
{
    private SettingsRepositoryInterface $repository;

    public function __construct(SettingsRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getSettings(): ?PluginSettings
    {
        return $this->repository->find();
    }

    public function saveSettings(SaveSettingsCommand $command): PluginSettings
    {
        $settings = new PluginSettings(
            new AccessToken($command->accessToken()),
            $this->toColor($command->backgroundColor()),
            $this->toColor($command->hoverBackgroundColor()),
            $this->toColor($command->color()),
            $this->toLabel($command->label())
        );

        $this->repository->save($settings);

        return $settings;
    }

    private function toColor(?string $value): ?Color
    {
        if ($value === null || $value === '') {
            return null;
        }

        return new Color($value);
    }

    private function toLabel(?string $value): ?ButtonLabel
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return new ButtonLabel($value);
    }

    public function deleteSettings(): void
    {
        $this->repository->delete();
    }
}
