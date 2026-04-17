<?php

declare(strict_types = 1);

namespace Kochmodus\Tests\Unit\Application\Settings;

use InvalidArgumentException;
use Kochmodus\Application\Settings\SaveSettingsCommand;
use Kochmodus\Application\Settings\SettingsService;
use Kochmodus\Domain\Settings\AccessToken;
use Kochmodus\Domain\Settings\PluginSettings;
use Kochmodus\Domain\Settings\SettingsRepositoryInterface;
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
