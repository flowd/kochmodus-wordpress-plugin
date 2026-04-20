<?php

declare(strict_types = 1);

namespace Kochmodus\Infrastructure\Persistence;

use InvalidArgumentException;
use Kochmodus\Domain\Settings\AccessToken;
use Kochmodus\Domain\Settings\PluginSettings;
use Kochmodus\Domain\Settings\SettingsRepositoryInterface;

final class WordPressSettingsRepository implements SettingsRepositoryInterface
{
    private const OPTION_KEY = 'kochmodus_settings';

    public function find(): ?PluginSettings
    {
        $data = get_option(self::OPTION_KEY, null);

        if (!is_array($data)) {
            return null;
        }

        $accessToken = $data['access_token'] ?? '';

        try {
            return new PluginSettings(
                new AccessToken(is_string($accessToken) ? $accessToken : '')
            );
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    public function save(PluginSettings $settings): void
    {
        update_option(self::OPTION_KEY, [
            'access_token' => $settings->accessToken()->value(),
        ]);
    }

    public function delete(): void
    {
        delete_option(self::OPTION_KEY);
    }
}
