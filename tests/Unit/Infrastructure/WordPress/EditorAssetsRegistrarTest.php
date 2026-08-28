<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Tests\Unit\Infrastructure\WordPress;

use function Brain\Monkey\Functions\expect;
use Flowd\KochmodusWordpressPlugin\Application\Settings\SettingsServiceInterface;
use Flowd\KochmodusWordpressPlugin\Domain\Button\ButtonLabel;
use Flowd\KochmodusWordpressPlugin\Domain\Button\Color;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\AccessToken;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;
use Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress\EditorAssetsRegistrar;
use Flowd\KochmodusWordpressPlugin\Tests\Unit\Infrastructure\WordPressTestCase;
use Mockery;
use Mockery\MockInterface;

final class EditorAssetsRegistrarTest extends WordPressTestCase
{
    public function test_enqueue_does_nothing_when_not_configured(): void
    {
        expect('wp_add_inline_script')->never();

        $registrar = new EditorAssetsRegistrar($this->createSettingsService(null));
        $registrar->enqueue();
    }

    public function test_enqueue_adds_defaults_as_inline_script(): void
    {
        $settings = new PluginSettings(
            new AccessToken('token123'),
            new Color('#ff0000'),
            new Color('#cc0000'),
            new Color('#ffffff'),
            new ButtonLabel('Jetzt kochen!')
        );

        expect('wp_json_encode')
            ->once()
            ->with([
                'label' => 'Jetzt kochen!',
                'backgroundColor' => '#ff0000',
                'hoverBackgroundColor' => '#cc0000',
                'color' => '#ffffff',
            ])
            ->andReturnUsing(static function (array $data): string {
                return json_encode($data);
            });
        expect('wp_add_inline_script')
            ->once()
            ->with(
                'kochmodus-button-editor-script',
                Mockery::on(static function (string $script): bool {
                    return strpos($script, 'window.kochmodusEditorDefaults') !== false
                        && strpos($script, 'Jetzt kochen!') !== false
                        && strpos($script, '#ff0000') !== false;
                }),
                'before'
            );

        $registrar = new EditorAssetsRegistrar($this->createSettingsService($settings));
        $registrar->enqueue();
    }

    public function test_enqueue_uses_empty_strings_for_unset_defaults(): void
    {
        $settings = new PluginSettings(new AccessToken('token123'));

        expect('wp_json_encode')
            ->once()
            ->with([
                'label' => '',
                'backgroundColor' => '',
                'hoverBackgroundColor' => '',
                'color' => '',
            ])
            ->andReturnUsing(static function (array $data): string {
                return json_encode($data);
            });
        expect('wp_add_inline_script')->once();

        $registrar = new EditorAssetsRegistrar($this->createSettingsService($settings));
        $registrar->enqueue();
    }

    private function createSettingsService(?PluginSettings $settings): SettingsServiceInterface
    {
        /** @var SettingsServiceInterface&MockInterface $service */
        $service = Mockery::mock(SettingsServiceInterface::class);
        $service->shouldReceive('getSettings')->once()->andReturn($settings);
        return $service;
    }
}
