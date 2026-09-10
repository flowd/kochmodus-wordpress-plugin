<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress;

use Flowd\KochmodusWordpressPlugin\Application\Settings\SettingsServiceInterface;
use Flowd\KochmodusWordpressPlugin\Domain\Button\Color;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;

/**
 * Enqueues the configured default button colors as CSS custom properties on
 * :root so hand-coded <kochmodus-button> elements pick them up as well.
 * Per-button inline styles (set by RenderButtonService) override them via CSS
 * specificity.
 */
final class GlobalStylesRenderer
{
    private const STYLE_HANDLE = 'kochmodus-global-styles';

    private SettingsServiceInterface $settingsService;

    public function __construct(SettingsServiceInterface $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function enqueueStyles(): void
    {
        $settings = $this->settingsService->getSettings();
        if (!$settings instanceof PluginSettings) {
            return;
        }

        $variables = [];

        if ($settings->defaultBackgroundColor() instanceof Color) {
            $variables[] = '--kochmodus-button-background: ' . $settings->defaultBackgroundColor()->value() . ';';
        }

        if ($settings->defaultHoverBackgroundColor() instanceof Color) {
            $variables[] = '--kochmodus-button-hover-background: ' . $settings->defaultHoverBackgroundColor()->value() . ';';
        }

        if ($settings->defaultColor() instanceof Color) {
            $variables[] = '--kochmodus-button-color: ' . $settings->defaultColor()->value() . ';';
        }

        if ($variables === []) {
            return;
        }

        wp_register_style(self::STYLE_HANDLE, false, [], false);
        wp_enqueue_style(self::STYLE_HANDLE);
        wp_add_inline_style(self::STYLE_HANDLE, ':root { ' . implode(' ', $variables) . ' }');
    }
}
