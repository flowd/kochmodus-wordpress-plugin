<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress;

use Flowd\KochmodusWordpressPlugin\Application\Settings\SettingsServiceInterface;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;

/**
 * Passes the configured button defaults to the block editor so the
 * block preview matches the frontend rendering.
 */
final class EditorAssetsRegistrar
{
    private const EDITOR_SCRIPT_HANDLE = 'kochmodus-button-editor-script';

    private SettingsServiceInterface $settingsService;

    public function __construct(SettingsServiceInterface $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function enqueue(): void
    {
        $settings = $this->settingsService->getSettings();
        if (!$settings instanceof PluginSettings) {
            return;
        }

        $defaults = [
            'label' => $settings->defaultLabel() !== null
                ? $settings->defaultLabel()->value()
                : '',
            'backgroundColor' => $settings->defaultBackgroundColor() !== null
                ? $settings->defaultBackgroundColor()->value()
                : '',
            'hoverBackgroundColor' => $settings->defaultHoverBackgroundColor() !== null
                ? $settings->defaultHoverBackgroundColor()->value()
                : '',
            'color' => $settings->defaultColor() !== null
                ? $settings->defaultColor()->value()
                : '',
        ];

        wp_add_inline_script(
            self::EDITOR_SCRIPT_HANDLE,
            'window.kochmodusEditorDefaults = ' . wp_json_encode($defaults) . ';',
            'before'
        );
    }
}
