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

    public function test_maybe_enqueue_enqueues_nothing_when_not_needed(): void
    {
        expect('wp_enqueue_script')->never();

        $enqueuer = new ScriptEnqueuer();

        $enqueuer->maybeEnqueue();
    }

    public function test_maybe_enqueue_enqueues_default_script_url_when_needed(): void
    {
        $enqueuer = new ScriptEnqueuer();

        expect('wp_enqueue_script')
            ->once()
            ->with(
                'kochmodus-widget',
                'https://app.kochmodus.de/build/assets/kochmodus-widget.js',
                [],
                null,
                ['in_footer' => true]
            );

        expect('add_filter')
            ->once()
            ->with('script_loader_tag', [$enqueuer, 'filterScriptTag'], 10, 3);

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

        $enqueuer = new ScriptEnqueuer();

        expect('wp_enqueue_script')
            ->once()
            ->with(
                'kochmodus-widget',
                'https://cdn.example.com/widget.js',
                [],
                null,
                ['in_footer' => true]
            );

        expect('add_filter')
            ->once()
            ->with('script_loader_tag', [$enqueuer, 'filterScriptTag'], 10, 3);

        $enqueuer->markNeeded();

        $enqueuer->maybeEnqueue();
    }

    public function test_filter_script_tag_rewrites_widget_script_to_module(): void
    {
        expect('esc_url')
            ->once()
            ->with('https://app.kochmodus.de/build/assets/kochmodus-widget.js')
            ->andReturnFirstArg();

        expect('wp_get_script_tag')
            ->once()
            ->with([
                'type' => 'module',
                'src' => 'https://app.kochmodus.de/build/assets/kochmodus-widget.js',
                'id' => 'kochmodus-widget-js',
            ])
            ->andReturn('<script type="module" src="https://app.kochmodus.de/build/assets/kochmodus-widget.js" id="kochmodus-widget-js"></script>' . "\n");

        $enqueuer = new ScriptEnqueuer();

        $tag = $enqueuer->filterScriptTag(
            '<script src="https://app.kochmodus.de/build/assets/kochmodus-widget.js" id="kochmodus-widget-js"></script>',
            'kochmodus-widget',
            'https://app.kochmodus.de/build/assets/kochmodus-widget.js'
        );

        $this->assertSame(
            '<script type="module" src="https://app.kochmodus.de/build/assets/kochmodus-widget.js" id="kochmodus-widget-js"></script>' . "\n",
            $tag
        );
    }

    public function test_filter_script_tag_leaves_other_handles_untouched(): void
    {
        expect('wp_get_script_tag')->never();

        $enqueuer = new ScriptEnqueuer();

        $tag = '<script src="https://example.com/other.js" id="other-js"></script>';

        $this->assertSame($tag, $enqueuer->filterScriptTag($tag, 'other', 'https://example.com/other.js'));
    }
}
