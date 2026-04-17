<?php

declare(strict_types = 1);

namespace Kochmodus\Tests\Unit\Infrastructure\Persistence;

use Brain\Monkey\Functions;
use Kochmodus\Domain\Settings\AccessToken;
use Kochmodus\Domain\Settings\PluginSettings;
use Kochmodus\Infrastructure\Persistence\WordPressSettingsRepository;
use Kochmodus\Tests\Unit\Infrastructure\WordPressTestCase;

final class WordPressSettingsRepositoryTest extends WordPressTestCase
{
    public function test_find_returns_null_when_option_not_set(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('kochmodus_settings', null)
            ->andReturn(null);

        $repository = new WordPressSettingsRepository();

        $this->assertNull($repository->find());
    }

    public function test_find_returns_plugin_settings_from_option_array(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('kochmodus_settings', null)
            ->andReturn([
                'access_token' => 'token123',
            ]);

        $repository = new WordPressSettingsRepository();
        $settings = $repository->find();

        $this->assertInstanceOf(PluginSettings::class, $settings);
        $this->assertSame('token123', $settings->accessToken()->value());
    }

    public function test_find_returns_null_when_option_data_is_corrupt(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('kochmodus_settings', null)
            ->andReturn([
                'access_token' => '',
            ]);

        $repository = new WordPressSettingsRepository();

        $this->assertNull($repository->find());
    }

    public function test_find_returns_null_when_option_is_not_array(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('kochmodus_settings', null)
            ->andReturn('invalid');

        $repository = new WordPressSettingsRepository();

        $this->assertNull($repository->find());
    }

    public function test_save_calls_update_option_with_serialized_data(): void
    {
        Functions\expect('update_option')
            ->once()
            ->with('kochmodus_settings', [
                'access_token' => 'token123',
            ]);

        $settings = new PluginSettings(
            new AccessToken('token123')
        );

        $repository = new WordPressSettingsRepository();
        $repository->save($settings);
    }

    public function test_delete_calls_delete_option(): void
    {
        Functions\expect('delete_option')
            ->once()
            ->with('kochmodus_settings');

        $repository = new WordPressSettingsRepository();
        $repository->delete();
    }
}
