<?php

declare(strict_types = 1);

namespace Kochmodus\Application\Settings;

use Kochmodus\Domain\Settings\AccessToken;
use Kochmodus\Domain\Settings\PluginSettings;
use Kochmodus\Domain\Settings\SettingsRepositoryInterface;

final class SettingsService implements SettingsServiceInterface
{
    /** @var SettingsRepositoryInterface */
    private $repository;

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
            new AccessToken($command->accessToken())
        );

        $this->repository->save($settings);

        return $settings;
    }

    public function deleteSettings(): void
    {
        $this->repository->delete();
    }
}
