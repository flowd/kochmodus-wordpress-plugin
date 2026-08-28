<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress;

final class AdminMenuRegistrar
{
    private SettingsPageRenderer $renderer;

    private ?string $pageHook = null;

    public function __construct(SettingsPageRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function register(): void
    {
        $hook = add_options_page(
            __('Kochmodus Settings', 'kochmodus'),
            __('Kochmodus', 'kochmodus'),
            'manage_options',
            'kochmodus-settings',
            [$this->renderer, 'render']
        );

        if (!is_string($hook)) {
            return;
        }

        $this->pageHook = $hook;
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function enqueueAssets(string $hook): void
    {
        if ($this->pageHook === null || $hook !== $this->pageHook) {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_add_inline_script(
            'wp-color-picker',
            'jQuery(function ($) { $(".kochmodus-color-field").wpColorPicker(); });'
        );
    }
}
