<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress;

use Flowd\KochmodusWordpressPlugin\Infrastructure\DependencyInjection\Container;

final class Plugin
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function boot(): void
    {
        // Translations are loaded automatically by WordPress since 4.6,
        // no load_plugin_textdomain() call needed.

        // Admin settings
        add_action('admin_menu', [$this->container->get(AdminMenuRegistrar::class), 'register']);
        add_action('admin_init', [$this->container->get(SettingsPageRenderer::class), 'registerSettings']);

        // Shortcode
        $this->container->get(ShortcodeRegistrar::class)->register();

        // Gutenberg block
        add_action('init', [$this->container->get(BlockRegistrar::class), 'register']);

        // Button defaults for the block editor preview
        add_action('enqueue_block_editor_assets', [$this->container->get(EditorAssetsRegistrar::class), 'enqueue']);

        // Global default colors as CSS custom properties (also styles hand-coded buttons)
        add_action('wp_enqueue_scripts', [$this->container->get(GlobalStylesRenderer::class), 'enqueueStyles']);

        // Conditional script loading: the needed-flag is set while content renders,
        // so enqueue in wp_footer — priority 1 runs before wp_print_footer_scripts (20).
        add_action('wp_footer', [$this->container->get(ScriptEnqueuer::class), 'maybeEnqueue'], 1);
    }

    public static function activate(): void
    {
        // Settings are created lazily on first save.
    }

    public static function deactivate(): void
    {
        // Settings are preserved on deactivation.
    }
}
