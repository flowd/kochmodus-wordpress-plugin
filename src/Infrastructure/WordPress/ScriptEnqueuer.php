<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress;

final class ScriptEnqueuer implements ScriptEnqueuerInterface
{
    private const DEFAULT_WIDGET_SCRIPT_URL = 'https://kochmodus.de/build/assets/kochmodus-widget.js';

    private bool $needed = false;

    public function markNeeded(): void
    {
        $this->needed = true;
    }

    public function isNeeded(): bool
    {
        return $this->needed;
    }

    public function maybeEnqueue(): void
    {
        if (!$this->needed) {
            return;
        }

        wp_print_script_tag([
            'type' => 'module',
            'src' => esc_url($this->getWidgetScriptUrl()),
        ]);
    }

    private function getWidgetScriptUrl(): string
    {
        if (defined('KOCHMODUS_WIDGET_SCRIPT_URL')) {
            return (string)constant('KOCHMODUS_WIDGET_SCRIPT_URL');
        }

        return self::DEFAULT_WIDGET_SCRIPT_URL;
    }
}
