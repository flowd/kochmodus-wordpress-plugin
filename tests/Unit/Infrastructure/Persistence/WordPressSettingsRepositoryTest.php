<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Tests\Unit\Infrastructure\Persistence;

use function Brain\Monkey\Functions\expect;
use Flowd\KochmodusWordpressPlugin\Domain\Button\ButtonLabel;
use Flowd\KochmodusWordpressPlugin\Domain\Button\Color;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\AccessToken;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;
use Flowd\KochmodusWordpressPlugin\Infrastructure\Persistence\WordPressSettingsRepository;
use Flowd\KochmodusWordpressPlugin\Tests\Unit\Infrastructure\WordPressTestCase;

final class WordPressSettingsRepositoryTest extends WordPressTestCase
{
    public function test_find_returns_null_when_option_not_set(): void
    {
        expect('get_option')
            ->once()
            ->with('kochmodus_settings', null)
            ->andReturn(null);

        $repository = new WordPressSettingsRepository();

        $this->assertNull($repository->find());
    }

    public function test_find_returns_plugin_settings_from_option_array(): void
    {
        expect('get_option')
            ->once()
            ->with('kochmodus_settings', null)
            ->andReturn([
                'access_token' => 'token123',
            ]);

        $repository = new WordPressSettingsRepository();
        $settings = $repository->find();

        $this->assertInstanceOf(PluginSettings::class, $settings);
        $this->assertSame('token123', $settings->accessToken()->value());
        $this->assertNull($settings->defaultBackgroundColor());
        $this->assertNull($settings->defaultHoverBackgroundColor());
        $this->assertNull($settings->defaultColor());
        $this->assertNull($settings->defaultLabel());
    }

    public function test_find_returns_plugin_settings_with_default_label(): void
    {
        expect('get_option')
            ->once()
            ->with('kochmodus_settings', null)
            ->andReturn([
                'access_token' => 'token123',
                'label' => 'Jetzt kochen!',
            ]);

        $repository = new WordPressSettingsRepository();
        $settings = $repository->find();

        $this->assertInstanceOf(PluginSettings::class, $settings);
        $this->assertNotNull($settings->defaultLabel());
        $this->assertSame('Jetzt kochen!', $settings->defaultLabel()->value());
    }

    public function test_find_returns_plugin_settings_with_default_colors(): void
    {
        expect('get_option')
            ->once()
            ->with('kochmodus_settings', null)
            ->andReturn([
                'access_token' => 'token123',
                'background_color' => '#ff0000',
                'hover_background_color' => '#cc0000',
                'color' => '#ffffff',
            ]);

        $repository = new WordPressSettingsRepository();
        $settings = $repository->find();

        $this->assertInstanceOf(PluginSettings::class, $settings);
        $this->assertNotNull($settings->defaultBackgroundColor());
        $this->assertSame('#ff0000', $settings->defaultBackgroundColor()->value());
        $this->assertNotNull($settings->defaultHoverBackgroundColor());
        $this->assertSame('#cc0000', $settings->defaultHoverBackgroundColor()->value());
        $this->assertNotNull($settings->defaultColor());
        $this->assertSame('#ffffff', $settings->defaultColor()->value());
    }

    public function test_find_ignores_invalid_stored_colors(): void
    {
        expect('get_option')
            ->once()
            ->with('kochmodus_settings', null)
            ->andReturn([
                'access_token' => 'token123',
                'background_color' => 'not-a-color',
                'hover_background_color' => '',
                'color' => '#ffffff',
            ]);

        $repository = new WordPressSettingsRepository();
        $settings = $repository->find();

        $this->assertInstanceOf(PluginSettings::class, $settings);
        $this->assertSame('token123', $settings->accessToken()->value());
        $this->assertNull($settings->defaultBackgroundColor());
        $this->assertNull($settings->defaultHoverBackgroundColor());
        $this->assertNotNull($settings->defaultColor());
        $this->assertSame('#ffffff', $settings->defaultColor()->value());
    }

    public function test_find_returns_null_when_option_data_is_corrupt(): void
    {
        expect('get_option')
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
        expect('get_option')
            ->once()
            ->with('kochmodus_settings', null)
            ->andReturn('invalid');

        $repository = new WordPressSettingsRepository();

        $this->assertNull($repository->find());
    }

    public function test_save_calls_update_option_with_serialized_data(): void
    {
        expect('update_option')
            ->once()
            ->with('kochmodus_settings', [
                'access_token' => 'token123',
                'label' => '',
                'background_color' => '',
                'hover_background_color' => '',
                'color' => '',
            ]);

        $settings = new PluginSettings(
            new AccessToken('token123')
        );

        $repository = new WordPressSettingsRepository();
        $repository->save($settings);
    }

    public function test_save_serializes_default_colors_and_label(): void
    {
        expect('update_option')
            ->once()
            ->with('kochmodus_settings', [
                'access_token' => 'token123',
                'label' => 'Jetzt kochen!',
                'background_color' => '#ff0000',
                'hover_background_color' => '#cc0000',
                'color' => '#ffffff',
            ]);

        $settings = new PluginSettings(
            new AccessToken('token123'),
            new Color('#ff0000'),
            new Color('#cc0000'),
            new Color('#ffffff'),
            new ButtonLabel('Jetzt kochen!')
        );

        $repository = new WordPressSettingsRepository();
        $repository->save($settings);
    }

    public function test_delete_calls_delete_option(): void
    {
        expect('delete_option')
            ->once()
            ->with('kochmodus_settings');

        $repository = new WordPressSettingsRepository();
        $repository->delete();
    }
}
