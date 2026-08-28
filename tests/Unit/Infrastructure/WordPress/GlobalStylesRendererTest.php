<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Tests\Unit\Infrastructure\WordPress;

use Flowd\KochmodusWordpressPlugin\Application\Settings\SettingsServiceInterface;
use Flowd\KochmodusWordpressPlugin\Domain\Button\ButtonLabel;
use Flowd\KochmodusWordpressPlugin\Domain\Button\Color;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\AccessToken;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;
use Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress\GlobalStylesRenderer;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

final class GlobalStylesRendererTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_prints_nothing_when_not_configured(): void
    {
        $renderer = new GlobalStylesRenderer($this->createSettingsService(null));

        $this->expectOutputString('');
        $renderer->printStyles();
    }

    public function test_prints_nothing_when_no_default_colors_set(): void
    {
        $settings = new PluginSettings(
            new AccessToken('token123'),
            null,
            null,
            null,
            new ButtonLabel('Jetzt kochen!')
        );

        $renderer = new GlobalStylesRenderer($this->createSettingsService($settings));

        $this->expectOutputString('');
        $renderer->printStyles();
    }

    public function test_prints_all_default_colors_as_root_css_variables(): void
    {
        $settings = new PluginSettings(
            new AccessToken('token123'),
            new Color('#ff0000'),
            new Color('#cc0000'),
            new Color('#ffffff')
        );

        $renderer = new GlobalStylesRenderer($this->createSettingsService($settings));

        $this->expectOutputString(
            '<style id="kochmodus-global-styles">:root { --kochmodus-button-background: #ff0000; --kochmodus-button-hover-background: #cc0000; --kochmodus-button-color: #ffffff; }</style>' . "\n"
        );
        $renderer->printStyles();
    }

    public function test_prints_only_set_default_colors(): void
    {
        $settings = new PluginSettings(
            new AccessToken('token123'),
            new Color('#ff0000')
        );

        $renderer = new GlobalStylesRenderer($this->createSettingsService($settings));

        $this->expectOutputString(
            '<style id="kochmodus-global-styles">:root { --kochmodus-button-background: #ff0000; }</style>' . "\n"
        );
        $renderer->printStyles();
    }

    private function createSettingsService(?PluginSettings $settings): SettingsServiceInterface
    {
        /** @var SettingsServiceInterface&MockInterface $service */
        $service = Mockery::mock(SettingsServiceInterface::class);
        $service->shouldReceive('getSettings')->once()->andReturn($settings);
        return $service;
    }
}
