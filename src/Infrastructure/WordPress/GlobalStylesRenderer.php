<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress;

use Flowd\KochmodusWordpressPlugin\Application\Settings\SettingsServiceInterface;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;

/**
 * Prints the configured default button colors as CSS custom properties on :root
 * so hand-coded <kochmodus-button> elements pick them up as well. Per-button
 * inline styles (set by RenderButtonService) override them via CSS specificity.
 */
final class GlobalStylesRenderer
{
    private SettingsServiceInterface $settingsService;

    public function __construct(SettingsServiceInterface $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function printStyles(): void
    {
        $settings = $this->settingsService->getSettings();
        if (!$settings instanceof PluginSettings) {
            return;
        }

        $variables = [];

        if ($settings->defaultBackgroundColor() !== null) {
            $variables[] = '--kochmodus-button-background: ' . $settings->defaultBackgroundColor()->value() . ';';
        }

        if ($settings->defaultHoverBackgroundColor() !== null) {
            $variables[] = '--kochmodus-button-hover-background: ' . $settings->defaultHoverBackgroundColor()->value() . ';';
        }

        if ($settings->defaultColor() !== null) {
            $variables[] = '--kochmodus-button-color: ' . $settings->defaultColor()->value() . ';';
        }

        if ($variables === []) {
            return;
        }

        printf(
            '<style id="kochmodus-global-styles">:root { %s }</style>' . "\n",
            implode(' ', $variables)
        );
    }
}
