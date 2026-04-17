<?php

declare(strict_types = 1);

namespace Kochmodus\Tests\Unit\Domain\Settings;

use Kochmodus\Domain\Settings\AccessToken;
use Kochmodus\Domain\Settings\PluginSettings;
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
}
