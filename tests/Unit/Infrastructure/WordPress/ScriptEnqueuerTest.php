<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Tests\Unit\Infrastructure\WordPress;

use function Brain\Monkey\Functions\expect;
use Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress\ScriptEnqueuer;
use Flowd\KochmodusWordpressPlugin\Tests\Unit\Infrastructure\WordPressTestCase;

final class ScriptEnqueuerTest extends WordPressTestCase
{
    public function test_is_needed_returns_false_initially(): void
    {
        $enqueuer = new ScriptEnqueuer();

        $this->assertFalse($enqueuer->isNeeded());
    }

    public function test_mark_needed_sets_flag(): void
    {
        $enqueuer = new ScriptEnqueuer();

        $enqueuer->markNeeded();

        $this->assertTrue($enqueuer->isNeeded());
    }

    public function test_maybe_enqueue_prints_nothing_when_not_needed(): void
    {
        expect('wp_print_script_tag')->never();

        $enqueuer = new ScriptEnqueuer();

        $enqueuer->maybeEnqueue();
    }

    public function test_maybe_enqueue_prints_default_script_url_as_module_when_needed(): void
    {
        expect('esc_url')
            ->once()
            ->with('https://kochmodus.de/build/assets/kochmodus-widget.js')
            ->andReturnFirstArg();

        expect('wp_print_script_tag')
            ->once()
            ->with([
                'type' => 'module',
                'src' => 'https://kochmodus.de/build/assets/kochmodus-widget.js',
            ]);

        $enqueuer = new ScriptEnqueuer();
        $enqueuer->markNeeded();

        $enqueuer->maybeEnqueue();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_maybe_enqueue_uses_widget_script_url_constant_when_defined(): void
    {
        define('KOCHMODUS_WIDGET_SCRIPT_URL', 'https://cdn.example.com/widget.js');

        expect('esc_url')
            ->once()
            ->with('https://cdn.example.com/widget.js')
            ->andReturnFirstArg();

        expect('wp_print_script_tag')
            ->once()
            ->with([
                'type' => 'module',
                'src' => 'https://cdn.example.com/widget.js',
            ]);

        $enqueuer = new ScriptEnqueuer();
        $enqueuer->markNeeded();

        $enqueuer->maybeEnqueue();
    }
}
