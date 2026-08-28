<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress;

use Flowd\KochmodusWordpressPlugin\Application\Settings\SettingsServiceInterface;
use Flowd\KochmodusWordpressPlugin\Domain\Button\ButtonLabel;
use Flowd\KochmodusWordpressPlugin\Domain\Button\Color;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\AccessToken;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;
use InvalidArgumentException;

final class SettingsPageRenderer
{
    private SettingsServiceInterface $settingsService;

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
            __('Kochmodus Configuration', 'kochmodus'),
            function (): void {
                echo '<p>' . esc_html__('Configure your Kochmodus widget integration.', 'kochmodus') . '</p>';
            },
            'kochmodus-settings'
        );

        add_settings_field(
            'kochmodus_access_token',
            __('Access Token', 'kochmodus'),
            [$this, 'renderAccessTokenField'],
            'kochmodus-settings',
            'kochmodus_main_section'
        );

        add_settings_section(
            'kochmodus_appearance_section',
            __('Button Defaults', 'kochmodus'),
            function (): void {
                echo '<p>' . esc_html__('Optional defaults for all Kochmodus buttons. Leave empty to use the widget defaults. Values set on an individual post or page always take precedence.', 'kochmodus') . '</p>';
            },
            'kochmodus-settings'
        );

        add_settings_field(
            'kochmodus_label',
            __('Button Label', 'kochmodus'),
            [$this, 'renderLabelField'],
            'kochmodus-settings',
            'kochmodus_appearance_section'
        );

        foreach ($this->colorFields() as $key => $label) {
            add_settings_field(
                'kochmodus_' . $key,
                $label,
                [$this, 'renderColorField'],
                'kochmodus-settings',
                'kochmodus_appearance_section',
                ['key' => $key]
            );
        }
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
        $value = $settings instanceof PluginSettings ? $settings->accessToken()->value() : '';
        printf(
            '<input type="text" id="kochmodus_access_token" name="kochmodus_settings[access_token]" value="%s" class="regular-text" required />',
            esc_attr($value)
        );
    }

    public function renderLabelField(): void
    {
        $settings = $this->settingsService->getSettings();
        $value = '';
        if ($settings instanceof PluginSettings && $settings->defaultLabel() !== null) {
            $value = $settings->defaultLabel()->value();
        }
        printf(
            '<input type="text" id="kochmodus_label" name="kochmodus_settings[label]" value="%s" class="regular-text" placeholder="%s" />',
            esc_attr($value),
            esc_attr(ButtonLabel::DEFAULT)
        );
        echo '<p class="description">' . esc_html__('Leave empty to use the built-in default.', 'kochmodus') . '</p>';
    }

    /** @param array<string, string> $args */
    public function renderColorField(array $args): void
    {
        $key = $args['key'];
        $settings = $this->settingsService->getSettings();
        $value = '';
        if ($settings instanceof PluginSettings) {
            $color = $this->settingsColorByKey($settings, $key);
            $value = $color !== null ? $color->value() : '';
        }
        printf(
            '<input type="text" id="kochmodus_%1$s" name="kochmodus_settings[%1$s]" value="%2$s" class="regular-text kochmodus-color-field" placeholder="%3$s" pattern="\s*(#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})|rgba?\([^)]*\))\s*" />',
            esc_attr($key),
            esc_attr($value),
            esc_attr__('e.g. #16a34a', 'kochmodus')
        );
        echo '<p class="description">' . esc_html__('Hex (#rrggbb) or rgb()/rgba(). Leave empty for the widget default.', 'kochmodus') . '</p>';
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
                __('Invalid settings data.', 'kochmodus')
            );
            return $this->getCurrentOptionOrDefault();
        }

        $rawToken = $input['access_token'] ?? '';
        $accessToken = sanitize_text_field(is_string($rawToken) ? $rawToken : '');

        try {
            new AccessToken($accessToken);
        } catch (InvalidArgumentException $e) {
            add_settings_error(
                'kochmodus_settings',
                'validation_error',
                sprintf(
                    /* translators: %s: validation error message. */
                    __('Access Token: %s', 'kochmodus'),
                    $e->getMessage()
                )
            );
            return $this->getCurrentOptionOrDefault();
        }

        $rawLabel = $input['label'] ?? '';

        $sanitized = [
            'access_token' => $accessToken,
            'label' => sanitize_text_field(is_string($rawLabel) ? $rawLabel : ''),
        ];

        foreach ($this->colorFields() as $key => $label) {
            $rawColor = $input[$key] ?? '';
            $colorValue = sanitize_text_field(is_string($rawColor) ? $rawColor : '');

            if ($colorValue === '') {
                $sanitized[$key] = '';
                continue;
            }

            try {
                $sanitized[$key] = (new Color($colorValue))->value();
            } catch (InvalidArgumentException $e) {
                add_settings_error(
                    'kochmodus_settings',
                    'validation_error',
                    sprintf(
                        /* translators: 1: settings field label, 2: validation error message. */
                        __('%1$s: %2$s', 'kochmodus'),
                        $label,
                        $e->getMessage()
                    )
                );
                return $this->getCurrentOptionOrDefault();
            }
        }

        return $sanitized;
    }

    /** @return array<string, string> */
    private function colorFields(): array
    {
        return [
            'background_color' => __('Background Color', 'kochmodus'),
            'hover_background_color' => __('Hover Background Color', 'kochmodus'),
            'color' => __('Label Color', 'kochmodus'),
        ];
    }

    private function settingsColorByKey(PluginSettings $settings, string $key): ?Color
    {
        switch ($key) {
            case 'background_color':
                return $settings->defaultBackgroundColor();
            case 'hover_background_color':
                return $settings->defaultHoverBackgroundColor();
            case 'color':
                return $settings->defaultColor();
            default:
                return null;
        }
    }

    /** @return array<string, string> */
    private function getCurrentOptionOrDefault(): array
    {
        $current = get_option('kochmodus_settings', null);
        if (!is_array($current)) {
            return [];
        }

        $result = [];
        foreach ($current as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
