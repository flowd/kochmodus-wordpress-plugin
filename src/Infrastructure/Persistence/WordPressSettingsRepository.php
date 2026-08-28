<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Infrastructure\Persistence;

use Flowd\KochmodusWordpressPlugin\Domain\Button\ButtonLabel;
use Flowd\KochmodusWordpressPlugin\Domain\Button\Color;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\AccessToken;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\SettingsRepositoryInterface;
use InvalidArgumentException;

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
                new AccessToken(is_string($accessToken) ? $accessToken : ''),
                $this->toColor($data['background_color'] ?? null),
                $this->toColor($data['hover_background_color'] ?? null),
                $this->toColor($data['color'] ?? null),
                $this->toLabel($data['label'] ?? null)
            );
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    public function save(PluginSettings $settings): void
    {
        update_option(self::OPTION_KEY, [
            'access_token' => $settings->accessToken()->value(),
            'label' => $settings->defaultLabel() !== null
                ? $settings->defaultLabel()->value()
                : '',
            'background_color' => $settings->defaultBackgroundColor() !== null
                ? $settings->defaultBackgroundColor()->value()
                : '',
            'hover_background_color' => $settings->defaultHoverBackgroundColor() !== null
                ? $settings->defaultHoverBackgroundColor()->value()
                : '',
            'color' => $settings->defaultColor() !== null
                ? $settings->defaultColor()->value()
                : '',
        ]);
    }

    /** @param mixed $value */
    private function toColor($value): ?Color
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        try {
            return new Color($value);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /** @param mixed $value */
    private function toLabel($value): ?ButtonLabel
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return new ButtonLabel($value);
    }

    public function delete(): void
    {
        delete_option(self::OPTION_KEY);
    }
}
