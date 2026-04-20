<?php

declare(strict_types = 1);

namespace Kochmodus\Tests\Unit\Infrastructure\WordPress;

use function Brain\Monkey\Functions\expect;
use Brain\Monkey\Functions;
use Kochmodus\Application\Button\RenderButtonServiceInterface;
use Kochmodus\Application\Settings\SettingsServiceInterface;
use Kochmodus\Domain\Settings\AccessToken;
use Kochmodus\Domain\Settings\PluginSettings;
use Kochmodus\Infrastructure\WordPress\BlockRegistrar;
use Kochmodus\Infrastructure\WordPress\ScriptEnqueuerInterface;
use Kochmodus\Tests\Unit\Infrastructure\WordPressTestCase;
use Mockery;
use Mockery\MockInterface;

final class BlockRegistrarTest extends WordPressTestCase
{
    public function test_register_calls_register_block_type(): void
    {
        expect('register_block_type')
            ->once()
            ->with(Mockery::type('string'), Mockery::type('array'));

        $registrar = $this->createRegistrar();
        $registrar->register();
    }

    public function test_render_block_returns_comment_when_not_configured(): void
    {
        /** @var SettingsServiceInterface&MockInterface $settingsService */
        $settingsService = Mockery::mock(SettingsServiceInterface::class);
        $settingsService->shouldReceive('getSettings')->once()->andReturnNull();

        $registrar = $this->createRegistrar($settingsService);

        $result = $registrar->renderBlock([]);

        $this->assertStringContainsString('not configured', $result);
    }

    public function test_render_block_renders_button_with_defaults(): void
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

        $registrar = new BlockRegistrar($settingsService, $renderService, $scriptEnqueuer);

        $result = $registrar->renderBlock([]);

        $this->assertSame('<kochmodus-button></kochmodus-button>', $result);
    }

    public function test_render_block_passes_custom_attributes(): void
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

        $registrar = new BlockRegistrar($settingsService, $renderService, $scriptEnqueuer);

        $result = $registrar->renderBlock([
            'label' => 'Jetzt kochen!',
            'recipeUri' => 'https://other.com/rezept/',
        ]);

        $this->assertSame('<kochmodus-button></kochmodus-button>', $result);
    }

    public function test_render_block_passes_color_attributes(): void
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
                '#ff0000',
                '#cc0000',
                '#ffffff',
                Mockery::type('callable')
            )
            ->andReturn('<kochmodus-button></kochmodus-button>');

        /** @var ScriptEnqueuerInterface&MockInterface $scriptEnqueuer */
        $scriptEnqueuer = Mockery::mock(ScriptEnqueuerInterface::class);
        $scriptEnqueuer->shouldReceive('markNeeded')->once();

        $registrar = new BlockRegistrar($settingsService, $renderService, $scriptEnqueuer);

        $result = $registrar->renderBlock([
            'backgroundColor' => '#ff0000',
            'hoverBackgroundColor' => '#cc0000',
            'color' => '#ffffff',
        ]);

        $this->assertSame('<kochmodus-button></kochmodus-button>', $result);
    }

    private function createRegistrar(
        ?SettingsServiceInterface $settingsService = null,
        ?RenderButtonServiceInterface $renderService = null,
        ?ScriptEnqueuerInterface $scriptEnqueuer = null
    ): BlockRegistrar {
        /** @var SettingsServiceInterface&MockInterface $ss */
        $ss = $settingsService ?? Mockery::mock(SettingsServiceInterface::class);
        /** @var RenderButtonServiceInterface&MockInterface $rs */
        $rs = $renderService ?? Mockery::mock(RenderButtonServiceInterface::class);
        /** @var ScriptEnqueuerInterface&MockInterface $se */
        $se = $scriptEnqueuer ?? Mockery::mock(ScriptEnqueuerInterface::class);

        return new BlockRegistrar($ss, $rs, $se);
    }

    private function createSettings(): PluginSettings
    {
        return new PluginSettings(
            new AccessToken('token123'),
        );
    }
}
