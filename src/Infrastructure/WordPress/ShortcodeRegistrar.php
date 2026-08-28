<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress;

use Flowd\KochmodusWordpressPlugin\Application\Button\RenderButtonServiceInterface;
use Flowd\KochmodusWordpressPlugin\Application\Settings\SettingsServiceInterface;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;

final class ShortcodeRegistrar
{
    private SettingsServiceInterface $settingsService;

    private RenderButtonServiceInterface $renderService;

    private ScriptEnqueuerInterface $scriptEnqueuer;

    public function __construct(
        SettingsServiceInterface $settingsService,
        RenderButtonServiceInterface $renderService,
        ScriptEnqueuerInterface $scriptEnqueuer
    ) {
        $this->settingsService = $settingsService;
        $this->renderService = $renderService;
        $this->scriptEnqueuer = $scriptEnqueuer;
    }

    public function register(): void
    {
        add_shortcode('kochmodus_button', [$this, 'handleShortcode']);
    }

    /**
     * @param array<string, string>|string $atts Shortcode attributes
     */
    public function handleShortcode($atts): string
    {
        $settings = $this->settingsService->getSettings();
        if (!$settings instanceof PluginSettings) {
            return '<!-- Kochmodus: Plugin not configured -->';
        }

        if (!is_array($atts)) {
            $atts = [];
        }

        /** @var array<string, string> $atts */
        $atts = shortcode_atts([
            'label' => '',
            'recipe_uri' => '',
            'background_color' => '',
            'hover_background_color' => '',
            'color' => '',
        ], $atts, 'kochmodus_button');

        $this->scriptEnqueuer->markNeeded();

        return $this->renderService->render(
            $settings,
            $atts['label'] !== '' ? $atts['label'] : null,
            $atts['recipe_uri'] !== '' ? $atts['recipe_uri'] : null,
            $atts['background_color'] !== '' ? $atts['background_color'] : null,
            $atts['hover_background_color'] !== '' ? $atts['hover_background_color'] : null,
            $atts['color'] !== '' ? $atts['color'] : null,
            function (string $value): string {
                return esc_attr($value);
            }
        );
    }
}
