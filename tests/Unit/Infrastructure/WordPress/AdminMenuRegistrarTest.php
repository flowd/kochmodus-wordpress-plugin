<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Tests\Unit\Infrastructure\WordPress;

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\expect;
use Flowd\KochmodusWordpressPlugin\Application\Settings\SettingsServiceInterface;
use Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress\AdminMenuRegistrar;
use Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress\SettingsPageRenderer;
use Flowd\KochmodusWordpressPlugin\Tests\Unit\Infrastructure\WordPressTestCase;
use Mockery;
use Mockery\MockInterface;

final class AdminMenuRegistrarTest extends WordPressTestCase
{
    public function test_register_adds_options_page_and_enqueue_hook(): void
    {
        expect('add_options_page')
            ->once()
            ->andReturn('settings_page_kochmodus-settings');
        expectAdded('admin_enqueue_scripts')->once();

        $registrar = new AdminMenuRegistrar($this->createRenderer());
        $registrar->register();
    }

    public function test_register_skips_enqueue_hook_when_page_not_added(): void
    {
        expect('add_options_page')
            ->once()
            ->andReturn(false);
        expectAdded('admin_enqueue_scripts')->never();

        $registrar = new AdminMenuRegistrar($this->createRenderer());
        $registrar->register();
    }

    public function test_enqueue_assets_loads_color_picker_on_settings_page(): void
    {
        expect('add_options_page')
            ->once()
            ->andReturn('settings_page_kochmodus-settings');
        expect('wp_enqueue_style')
            ->once()
            ->with('wp-color-picker');
        expect('wp_enqueue_script')
            ->once()
            ->with('wp-color-picker');
        expect('wp_add_inline_script')
            ->once()
            ->with('wp-color-picker', Mockery::type('string'));

        $registrar = new AdminMenuRegistrar($this->createRenderer());
        $registrar->register();
        $registrar->enqueueAssets('settings_page_kochmodus-settings');
    }

    public function test_enqueue_assets_does_nothing_on_other_admin_pages(): void
    {
        expect('add_options_page')
            ->once()
            ->andReturn('settings_page_kochmodus-settings');
        expect('wp_enqueue_style')->never();
        expect('wp_enqueue_script')->never();
        expect('wp_add_inline_script')->never();

        $registrar = new AdminMenuRegistrar($this->createRenderer());
        $registrar->register();
        $registrar->enqueueAssets('edit.php');
    }

    private function createRenderer(): SettingsPageRenderer
    {
        /** @var SettingsServiceInterface&MockInterface $settingsService */
        $settingsService = Mockery::mock(SettingsServiceInterface::class);
        return new SettingsPageRenderer($settingsService);
    }
}
