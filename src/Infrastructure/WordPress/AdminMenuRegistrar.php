<?php

declare(strict_types = 1);

namespace Kochmodus\Infrastructure\WordPress;

final class AdminMenuRegistrar
{
    private \Kochmodus\Infrastructure\WordPress\SettingsPageRenderer $renderer;

    public function __construct(SettingsPageRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function register(): void
    {
        add_options_page(
            'Kochmodus Settings',
            'Kochmodus',
            'manage_options',
            'kochmodus-settings',
            [$this->renderer, 'render']
        );
    }
}
