<?php

declare(strict_types = 1);

namespace Kochmodus\Infrastructure\WordPress;

use Kochmodus\Infrastructure\DependencyInjection\Container;

final class Plugin
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function boot(): void
    {
        // Admin settings
        add_action('admin_menu', [$this->container->get(AdminMenuRegistrar::class), 'register']);
        add_action('admin_init', [$this->container->get(SettingsPageRenderer::class), 'registerSettings']);

        // Shortcode
        $this->container->get(ShortcodeRegistrar::class)->register();

        // Gutenberg block
        add_action('init', [$this->container->get(BlockRegistrar::class), 'register']);

        // Conditional script loading
        add_action('wp_footer', [$this->container->get(ScriptEnqueuer::class), 'maybeEnqueue']);
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
