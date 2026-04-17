<?php

declare(strict_types = 1);

namespace Kochmodus\Tests\Unit\Infrastructure\WordPress;

use Brain\Monkey\Functions;
use Kochmodus\Application\Button\RenderButtonServiceInterface;
use Kochmodus\Application\Settings\SettingsServiceInterface;
use Kochmodus\Domain\Settings\AccessToken;
use Kochmodus\Domain\Settings\PluginSettings;
use Kochmodus\Infrastructure\WordPress\ScriptEnqueuerInterface;
use Kochmodus\Infrastructure\WordPress\ShortcodeRegistrar;
use Kochmodus\Tests\Unit\Infrastructure\WordPressTestCase;
use Mockery;
use Mockery\MockInterface;

final class ShortcodeRegistrarTest extends WordPressTestCase
{
    public function test_register_adds_shortcode(): void
    {
        Functions\expect('add_shortcode')
            ->once()
            ->with('kochmodus_button', Mockery::type('array'));

        $registrar = $this->createRegistrar();
        $registrar->register();
    }

    public function test_handle_shortcode_returns_comment_when_not_configured(): void
    {
        /** @var SettingsServiceInterface&MockInterface $settingsService */
        $settingsService = Mockery::mock(SettingsServiceInterface::class);
        $settingsService->shouldReceive('getSettings')->once()->andReturnNull();

        $registrar = $this->createRegistrar($settingsService);

        $result = $registrar->handleShortcode([]);

        $this->assertStringContainsString('not configured', $result);
    }

    public function test_handle_shortcode_renders_button_with_defaults(): void
    {
        $settings = $this->createSettings();

        /** @var SettingsServiceInterface&MockInterface $settingsService */
        $settingsService = Mockery::mock(SettingsServiceInterface::class);
        $settingsService->shouldReceive('getSettings')->once()->andReturn($settings);

        /** @var RenderButtonServiceInterface&MockInterface $renderService */
        $renderService = Mockery::mock(RenderButtonServiceInterface::class);
        $renderService->shouldReceive('render')
            ->once()
            ->with(
                $settings,
                'Kochmodus starten',
                null,
                null,
                null,
                null,
                Mockery::type('callable')
            )
            ->andReturn('<kochmodus-button></kochmodus-button>');

        /** @var ScriptEnqueuerInterface&MockInterface $scriptEnqueuer */
        $scriptEnqueuer = Mockery::mock(ScriptEnqueuerInterface::class);
        $scriptEnqueuer->shouldReceive('markNeeded')->once();

        Functions\expect('shortcode_atts')
            ->once()
            ->andReturn(['label' => 'Kochmodus starten', 'recipe_uri' => '', 'background_color' => '', 'hover_background_color' => '', 'color' => '']);

        $registrar = new ShortcodeRegistrar($settingsService, $renderService, $scriptEnqueuer);

        $result = $registrar->handleShortcode([]);

        $this->assertSame('<kochmodus-button></kochmodus-button>', $result);
    }

    public function test_handle_shortcode_passes_custom_attributes(): void
    {
        $settings = $this->createSettings();

        /** @var SettingsServiceInterface&MockInterface $settingsService */
        $settingsService = Mockery::mock(SettingsServiceInterface::class);
        $settingsService->shouldReceive('getSettings')->once()->andReturn($settings);

        /** @var RenderButtonServiceInterface&MockInterface $renderService */
        $renderService = Mockery::mock(RenderButtonServiceInterface::class);
        $renderService->shouldReceive('render')
            ->once()
            ->with(
                $settings,
                'Jetzt kochen!',
                'https://other.com/rezept/',
                null,
                null,
                null,
                Mockery::type('callable')
            )
            ->andReturn('<kochmodus-button></kochmodus-button>');

        /** @var ScriptEnqueuerInterface&MockInterface $scriptEnqueuer */
        $scriptEnqueuer = Mockery::mock(ScriptEnqueuerInterface::class);
        $scriptEnqueuer->shouldReceive('markNeeded')->once();

        Functions\expect('shortcode_atts')
            ->once()
            ->andReturn(['label' => 'Jetzt kochen!', 'recipe_uri' => 'https://other.com/rezept/', 'background_color' => '', 'hover_background_color' => '', 'color' => '']);

        $registrar = new ShortcodeRegistrar($settingsService, $renderService, $scriptEnqueuer);

        $result = $registrar->handleShortcode([
            'label' => 'Jetzt kochen!',
            'recipe_uri' => 'https://other.com/rezept/',
        ]);

        $this->assertSame('<kochmodus-button></kochmodus-button>', $result);
    }

    public function test_handle_shortcode_marks_script_as_needed(): void
    {
        $settings = $this->createSettings();

        /** @var SettingsServiceInterface&MockInterface $settingsService */
        $settingsService = Mockery::mock(SettingsServiceInterface::class);
        $settingsService->shouldReceive('getSettings')->once()->andReturn($settings);

        /** @var RenderButtonServiceInterface&MockInterface $renderService */
        $renderService = Mockery::mock(RenderButtonServiceInterface::class);
        $renderService->shouldReceive('render')->andReturn('');

        /** @var ScriptEnqueuerInterface&MockInterface $scriptEnqueuer */
        $scriptEnqueuer = Mockery::mock(ScriptEnqueuerInterface::class);
        $scriptEnqueuer->shouldReceive('markNeeded')->once();

        Functions\expect('shortcode_atts')
            ->andReturn(['label' => 'Kochmodus starten', 'recipe_uri' => '', 'background_color' => '', 'hover_background_color' => '', 'color' => '']);

        $registrar = new ShortcodeRegistrar($settingsService, $renderService, $scriptEnqueuer);
        $registrar->handleShortcode([]);
    }

    private function createRegistrar(
        ?SettingsServiceInterface $settingsService = null,
        ?RenderButtonServiceInterface $renderService = null,
        ?ScriptEnqueuerInterface $scriptEnqueuer = null
    ): ShortcodeRegistrar {
        /** @var SettingsServiceInterface&MockInterface $ss */
        $ss = $settingsService ?? Mockery::mock(SettingsServiceInterface::class);
        /** @var RenderButtonServiceInterface&MockInterface $rs */
        $rs = $renderService ?? Mockery::mock(RenderButtonServiceInterface::class);
        /** @var ScriptEnqueuerInterface&MockInterface $se */
        $se = $scriptEnqueuer ?? Mockery::mock(ScriptEnqueuerInterface::class);

        return new ShortcodeRegistrar($ss, $rs, $se);
    }

    private function createSettings(): PluginSettings
    {
        return new PluginSettings(
            new AccessToken('token123'),
        );
    }
}
