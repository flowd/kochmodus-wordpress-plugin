<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Tests\Unit\Domain\Settings;

use Flowd\KochmodusWordpressPlugin\Domain\Button\ButtonLabel;
use Flowd\KochmodusWordpressPlugin\Domain\Button\Color;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\AccessToken;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;
use PHPUnit\Framework\TestCase;

final class PluginSettingsTest extends TestCase
{
    public function test_it_constructs_with_valid_value_objects(): void
    {
        $settings = new PluginSettings(
            new AccessToken('token123')
        );

        $this->assertInstanceOf(PluginSettings::class, $settings);
    }

    public function test_getters_return_correct_value_objects(): void
    {
        $accessToken = new AccessToken('token123');

        $settings = new PluginSettings($accessToken);

        $this->assertSame($accessToken, $settings->accessToken());
    }

    public function test_default_colors_are_null_when_not_provided(): void
    {
        $settings = new PluginSettings(new AccessToken('token123'));

        $this->assertNull($settings->defaultBackgroundColor());
        $this->assertNull($settings->defaultHoverBackgroundColor());
        $this->assertNull($settings->defaultColor());
    }

    public function test_default_label_is_null_when_not_provided(): void
    {
        $settings = new PluginSettings(new AccessToken('token123'));

        $this->assertNull($settings->defaultLabel());
    }

    public function test_getter_returns_default_label_when_provided(): void
    {
        $label = new ButtonLabel('Jetzt kochen!');

        $settings = new PluginSettings(
            new AccessToken('token123'),
            null,
            null,
            null,
            $label
        );

        $this->assertSame($label, $settings->defaultLabel());
    }

    public function test_getters_return_default_colors_when_provided(): void
    {
        $background = new Color('#ff0000');
        $hoverBackground = new Color('#cc0000');
        $color = new Color('#ffffff');

        $settings = new PluginSettings(
            new AccessToken('token123'),
            $background,
            $hoverBackground,
            $color
        );

        $this->assertSame($background, $settings->defaultBackgroundColor());
        $this->assertSame($hoverBackground, $settings->defaultHoverBackgroundColor());
        $this->assertSame($color, $settings->defaultColor());
    }
}
