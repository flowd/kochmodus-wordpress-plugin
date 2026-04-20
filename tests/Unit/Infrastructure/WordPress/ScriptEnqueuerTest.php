<?php

declare(strict_types = 1);

namespace Kochmodus\Tests\Unit\Infrastructure\WordPress;

use Brain\Monkey\Functions;
use Kochmodus\Infrastructure\WordPress\ScriptEnqueuer;
use Kochmodus\Tests\Unit\Infrastructure\WordPressTestCase;

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

    public function test_maybe_enqueue_outputs_nothing_when_not_needed(): void
    {
        $enqueuer = new ScriptEnqueuer();

        ob_start();
        $enqueuer->maybeEnqueue();
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }

    public function test_maybe_enqueue_outputs_default_script_url_when_needed(): void
    {
        Functions\expect('esc_url')
            ->once()
            ->with('https://kochmodus.de/build/assets/kochmodus-widget.js')
            ->andReturn('https://kochmodus.de/build/assets/kochmodus-widget.js');

        $enqueuer = new ScriptEnqueuer();
        $enqueuer->markNeeded();

        ob_start();
        $enqueuer->maybeEnqueue();
        $output = ob_get_clean();

        $this->assertSame(
            '<script type="module" src="https://kochmodus.de/build/assets/kochmodus-widget.js"></script>' . "\n",
            $output
        );
    }

    public function test_maybe_enqueue_outputs_script_tag_with_type_module(): void
    {
        Functions\expect('esc_url')
            ->once()
            ->andReturnFirstArg();

        $enqueuer = new ScriptEnqueuer();
        $enqueuer->markNeeded();

        ob_start();
        $enqueuer->maybeEnqueue();
        $output = ob_get_clean();

        $this->assertStringContainsString('type="module"', $output);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_maybe_enqueue_uses_widget_script_url_constant_when_defined(): void
    {
        define('KOCHMODUS_WIDGET_SCRIPT_URL', 'https://cdn.example.com/widget.js');

        Functions\expect('esc_url')
            ->once()
            ->with('https://cdn.example.com/widget.js')
            ->andReturnFirstArg();

        $enqueuer = new ScriptEnqueuer();
        $enqueuer->markNeeded();

        ob_start();
        $enqueuer->maybeEnqueue();
        $output = ob_get_clean();

        $this->assertSame(
            '<script type="module" src="https://cdn.example.com/widget.js"></script>' . "\n",
            $output
        );
    }
}
