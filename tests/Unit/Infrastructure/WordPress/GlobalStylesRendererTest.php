<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Tests\Unit\Infrastructure\WordPress;

use function Brain\Monkey\Functions\expect;
use Flowd\KochmodusWordpressPlugin\Application\Settings\SettingsServiceInterface;
use Flowd\KochmodusWordpressPlugin\Domain\Button\ButtonLabel;
use Flowd\KochmodusWordpressPlugin\Domain\Button\Color;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\AccessToken;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;
use Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress\GlobalStylesRenderer;
use Flowd\KochmodusWordpressPlugin\Tests\Unit\Infrastructure\WordPressTestCase;
use Mockery;
use Mockery\MockInterface;

final class GlobalStylesRendererTest extends WordPressTestCase
{
    public function test_enqueues_nothing_when_not_configured(): void
    {
        expect('wp_register_style')->never();
        expect('wp_enqueue_style')->never();
        expect('wp_add_inline_style')->never();

        $renderer = new GlobalStylesRenderer($this->createSettingsService(null));

        $renderer->enqueueStyles();
    }

    public function test_enqueues_nothing_when_no_default_colors_set(): void
    {
        expect('wp_register_style')->never();
        expect('wp_enqueue_style')->never();
        expect('wp_add_inline_style')->never();

        $settings = new PluginSettings(
            new AccessToken('token123'),
            null,
            null,
            null,
            new ButtonLabel('Jetzt kochen!')
        );

        $renderer = new GlobalStylesRenderer($this->createSettingsService($settings));

        $renderer->enqueueStyles();
    }

    public function test_enqueues_all_default_colors_as_root_css_variables(): void
    {
        $this->expectStyleEnqueue(
            ':root { --kochmodus-button-background: #ff0000; --kochmodus-button-hover-background: #cc0000; --kochmodus-button-color: #ffffff; }'
        );

        $settings = new PluginSettings(
            new AccessToken('token123'),
            new Color('#ff0000'),
            new Color('#cc0000'),
            new Color('#ffffff')
        );

        $renderer = new GlobalStylesRenderer($this->createSettingsService($settings));

        $renderer->enqueueStyles();
    }

    public function test_enqueues_only_set_default_colors(): void
    {
        $this->expectStyleEnqueue(':root { --kochmodus-button-background: #ff0000; }');

        $settings = new PluginSettings(
            new AccessToken('token123'),
            new Color('#ff0000')
        );

        $renderer = new GlobalStylesRenderer($this->createSettingsService($settings));

        $renderer->enqueueStyles();
    }

    private function expectStyleEnqueue(string $expectedCss): void
    {
        expect('wp_register_style')
            ->once()
            ->with('kochmodus-global-styles', false, [], KOCHMODUS_VERSION);

        expect('wp_enqueue_style')
            ->once()
            ->with('kochmodus-global-styles');

        expect('wp_add_inline_style')
            ->once()
            ->with('kochmodus-global-styles', $expectedCss);
    }

    private function createSettingsService(?PluginSettings $settings): SettingsServiceInterface
    {
        /** @var SettingsServiceInterface&MockInterface $service */
        $service = Mockery::mock(SettingsServiceInterface::class);
        $service->shouldReceive('getSettings')->once()->andReturn($settings);
        return $service;
    }
}
