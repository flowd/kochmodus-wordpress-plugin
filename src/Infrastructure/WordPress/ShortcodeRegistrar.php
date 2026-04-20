<?php

declare(strict_types = 1);

namespace Kochmodus\Infrastructure\WordPress;

use Kochmodus\Application\Button\RenderButtonServiceInterface;
use Kochmodus\Application\Settings\SettingsServiceInterface;
use Kochmodus\Domain\Button\ButtonLabel;

final class ShortcodeRegistrar
{
    private \Kochmodus\Application\Settings\SettingsServiceInterface $settingsService;

    private \Kochmodus\Application\Button\RenderButtonServiceInterface $renderService;

    private \Kochmodus\Infrastructure\WordPress\ScriptEnqueuerInterface $scriptEnqueuer;

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
        if ($settings === null) {
            return '<!-- Kochmodus: Plugin not configured -->';
        }

        if (!is_array($atts)) {
            $atts = [];
        }

        /** @var array<string, string> $atts */
        $atts = shortcode_atts([
            'label' => ButtonLabel::DEFAULT,
            'recipe_uri' => '',
            'background_color' => '',
            'hover_background_color' => '',
            'color' => '',
        ], $atts, 'kochmodus_button');

        $this->scriptEnqueuer->markNeeded();

        return $this->renderService->render(
            $settings,
            $atts['label'],
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
