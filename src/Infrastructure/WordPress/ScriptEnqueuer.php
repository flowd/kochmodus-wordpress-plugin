<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress;

final class ScriptEnqueuer implements ScriptEnqueuerInterface
{
    private const SCRIPT_HANDLE = 'kochmodus-widget';

    private const DEFAULT_WIDGET_SCRIPT_URL = 'https://app.kochmodus.de/build/assets/kochmodus-widget.js';

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

        wp_enqueue_script(
            self::SCRIPT_HANDLE,
            $this->getWidgetScriptUrl(),
            [],
            // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- external widget URL, versioned by its host
            null,
            ['in_footer' => true]
        );

        add_filter('script_loader_tag', [$this, 'filterScriptTag'], 10, 3);
    }

    /**
     * The widget is an ES module; wp_enqueue_script() cannot set type="module"
     * (wp_enqueue_script_module() requires WP 6.5, we support 6.0), so the tag
     * is rebuilt via the script_loader_tag filter.
     */
    public function filterScriptTag(string $tag, string $handle, string $src): string
    {
        if ($handle !== self::SCRIPT_HANDLE) {
            return $tag;
        }

        return wp_get_script_tag([
            'type' => 'module',
            'src' => esc_url($src),
            'id' => self::SCRIPT_HANDLE . '-js',
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
