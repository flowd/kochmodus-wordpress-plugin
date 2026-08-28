<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Tests\Unit\Application\Settings;

use Flowd\KochmodusWordpressPlugin\Application\Settings\SaveSettingsCommand;
use Flowd\KochmodusWordpressPlugin\Application\Settings\SettingsService;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\AccessToken;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\SettingsRepositoryInterface;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

final class SettingsServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_get_settings_returns_null_when_not_configured(): void
    {
        /** @var SettingsRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(SettingsRepositoryInterface::class);
        $repository->shouldReceive('find')->once()->andReturnNull();

        $service = new SettingsService($repository);

        $this->assertNull($service->getSettings());
    }

    public function test_get_settings_returns_settings_from_repository(): void
    {
        $settings = new PluginSettings(
            new AccessToken('token123')
        );

        /** @var SettingsRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(SettingsRepositoryInterface::class);
        $repository->shouldReceive('find')->once()->andReturn($settings);

        $service = new SettingsService($repository);

        $this->assertSame($settings, $service->getSettings());
    }

    public function test_save_settings_creates_value_objects_and_persists(): void
    {
        /** @var SettingsRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(SettingsRepositoryInterface::class);
        $repository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (PluginSettings $settings): bool {
                return $settings->accessToken()->value() === 'token123';
            }));

        $service = new SettingsService($repository);
        $command = new SaveSettingsCommand('token123');

        $result = $service->saveSettings($command);

        $this->assertSame('token123', $result->accessToken()->value());
    }

    public function test_save_settings_persists_default_colors(): void
    {
        /** @var SettingsRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(SettingsRepositoryInterface::class);
        $repository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (PluginSettings $settings): bool {
                return $settings->defaultBackgroundColor() !== null
                    && $settings->defaultBackgroundColor()->value() === '#ff0000'
                    && $settings->defaultHoverBackgroundColor() !== null
                    && $settings->defaultHoverBackgroundColor()->value() === '#cc0000'
                    && $settings->defaultColor() !== null
                    && $settings->defaultColor()->value() === '#ffffff';
            }));

        $service = new SettingsService($repository);
        $command = new SaveSettingsCommand('token123', '#ff0000', '#cc0000', '#ffffff');

        $result = $service->saveSettings($command);

        $this->assertNotNull($result->defaultBackgroundColor());
        $this->assertSame('#ff0000', $result->defaultBackgroundColor()->value());
    }

    public function test_save_settings_keeps_default_colors_null_when_not_provided(): void
    {
        /** @var SettingsRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(SettingsRepositoryInterface::class);
        $repository->shouldReceive('save')->once();

        $service = new SettingsService($repository);
        $command = new SaveSettingsCommand('token123');

        $result = $service->saveSettings($command);

        $this->assertNull($result->defaultBackgroundColor());
        $this->assertNull($result->defaultHoverBackgroundColor());
        $this->assertNull($result->defaultColor());
    }

    public function test_save_settings_treats_empty_color_strings_as_null(): void
    {
        /** @var SettingsRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(SettingsRepositoryInterface::class);
        $repository->shouldReceive('save')->once();

        $service = new SettingsService($repository);
        $command = new SaveSettingsCommand('token123', '', '', '');

        $result = $service->saveSettings($command);

        $this->assertNull($result->defaultBackgroundColor());
        $this->assertNull($result->defaultHoverBackgroundColor());
        $this->assertNull($result->defaultColor());
    }

    public function test_save_settings_persists_default_label(): void
    {
        /** @var SettingsRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(SettingsRepositoryInterface::class);
        $repository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (PluginSettings $settings): bool {
                return $settings->defaultLabel() !== null
                    && $settings->defaultLabel()->value() === 'Jetzt kochen!';
            }));

        $service = new SettingsService($repository);
        $command = new SaveSettingsCommand('token123', null, null, null, 'Jetzt kochen!');

        $result = $service->saveSettings($command);

        $this->assertNotNull($result->defaultLabel());
        $this->assertSame('Jetzt kochen!', $result->defaultLabel()->value());
    }

    public function test_save_settings_treats_empty_label_as_null(): void
    {
        /** @var SettingsRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(SettingsRepositoryInterface::class);
        $repository->shouldReceive('save')->once();

        $service = new SettingsService($repository);
        $command = new SaveSettingsCommand('token123', null, null, null, '');

        $result = $service->saveSettings($command);

        $this->assertNull($result->defaultLabel());
    }

    public function test_save_settings_throws_on_invalid_color(): void
    {
        /** @var SettingsRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(SettingsRepositoryInterface::class);
        $repository->shouldNotReceive('save');

        $service = new SettingsService($repository);
        $command = new SaveSettingsCommand('token123', 'not-a-color');

        $this->expectException(InvalidArgumentException::class);
        $service->saveSettings($command);
    }

    public function test_save_settings_throws_on_invalid_access_token(): void
    {
        /** @var SettingsRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(SettingsRepositoryInterface::class);
        $repository->shouldNotReceive('save');

        $service = new SettingsService($repository);
        $command = new SaveSettingsCommand('');

        $this->expectException(InvalidArgumentException::class);
        $service->saveSettings($command);
    }

    public function test_delete_settings_calls_repository_delete(): void
    {
        /** @var SettingsRepositoryInterface&MockInterface $repository */
        $repository = Mockery::mock(SettingsRepositoryInterface::class);
        $repository->shouldReceive('delete')->once();

        $service = new SettingsService($repository);
        $service->deleteSettings();
    }
}
