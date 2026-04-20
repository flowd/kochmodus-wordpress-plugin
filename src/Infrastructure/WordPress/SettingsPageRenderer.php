<?php

declare(strict_types = 1);

namespace Kochmodus\Infrastructure\WordPress;

use InvalidArgumentException;
use Kochmodus\Application\Settings\SettingsServiceInterface;
use Kochmodus\Domain\Settings\AccessToken;

final class SettingsPageRenderer
{
    private \Kochmodus\Application\Settings\SettingsServiceInterface $settingsService;

    public function __construct(SettingsServiceInterface $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function registerSettings(): void
    {
        register_setting(
            'kochmodus_settings_group',
            'kochmodus_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitizeSettings'],
            ]
        );

        add_settings_section(
            'kochmodus_main_section',
            'Kochmodus Configuration',
            function (): void {
                echo '<p>Configure your Kochmodus widget integration.</p>';
            },
            'kochmodus-settings'
        );

        add_settings_field(
            'kochmodus_access_token',
            'Access Token',
            [$this, 'renderAccessTokenField'],
            'kochmodus-settings',
            'kochmodus_main_section'
        );
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        include dirname(__DIR__, 3) . '/templates/admin/settings-page.php';
    }

    public function renderAccessTokenField(): void
    {
        $settings = $this->settingsService->getSettings();
        $value = $settings !== null ? $settings->accessToken()->value() : '';
        printf(
            '<input type="text" id="kochmodus_access_token" name="kochmodus_settings[access_token]" value="%s" class="regular-text" required />',
            esc_attr($value)
        );
    }

    /**
     * @param mixed $input
     * @return array<string, string>
     */
    public function sanitizeSettings($input): array
    {
        if (!is_array($input)) {
            add_settings_error(
                'kochmodus_settings',
                'invalid_input',
                'Invalid settings data.'
            );
            return $this->getCurrentOptionOrDefault();
        }

        $accessToken = sanitize_text_field($input['access_token'] ?? '');

        try {
            new AccessToken($accessToken);
        } catch (InvalidArgumentException $e) {
            add_settings_error('kochmodus_settings', 'validation_error', 'Access Token: ' . $e->getMessage());
            return $this->getCurrentOptionOrDefault();
        }

        return [
            'access_token' => $accessToken,
        ];
    }

    /** @return array<string, string> */
    private function getCurrentOptionOrDefault(): array
    {
        $current = get_option('kochmodus_settings', null);
        return is_array($current) ? $current : [];
    }
}
