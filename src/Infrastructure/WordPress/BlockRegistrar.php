<?php

declare(strict_types = 1);

namespace Kochmodus\Infrastructure\WordPress;

use Kochmodus\Application\Button\RenderButtonServiceInterface;
use Kochmodus\Application\Settings\SettingsServiceInterface;
use Kochmodus\Domain\Button\ButtonLabel;

final class BlockRegistrar
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
        register_block_type(
            dirname(__DIR__, 3) . '/blocks/kochmodus-button',
            [
                'render_callback' => [$this, 'renderBlock'],
            ]
        );
    }

    /** @param array<string, mixed> $attributes */
    public function renderBlock(array $attributes): string
    {
        $settings = $this->settingsService->getSettings();
        if ($settings === null) {
            return '<!-- Kochmodus: Plugin not configured -->';
        }

        $label = isset($attributes['label']) && is_string($attributes['label']) ? $attributes['label'] : ButtonLabel::DEFAULT;
        $recipeUri = isset($attributes['recipeUri']) && is_string($attributes['recipeUri']) && $attributes['recipeUri'] !== '' ? $attributes['recipeUri'] : null;
        $backgroundColor = isset($attributes['backgroundColor']) && is_string($attributes['backgroundColor']) && $attributes['backgroundColor'] !== '' ? $attributes['backgroundColor'] : null;
        $hoverBackgroundColor = isset($attributes['hoverBackgroundColor']) && is_string($attributes['hoverBackgroundColor']) && $attributes['hoverBackgroundColor'] !== '' ? $attributes['hoverBackgroundColor'] : null;
        $color = isset($attributes['color']) && is_string($attributes['color']) && $attributes['color'] !== '' ? $attributes['color'] : null;

        $this->scriptEnqueuer->markNeeded();

        return $this->renderService->render(
            $settings,
            $label,
            $recipeUri,
            $backgroundColor,
            $hoverBackgroundColor,
            $color,
            function (string $value): string {
                return esc_attr($value);
            }
        );
    }
}
