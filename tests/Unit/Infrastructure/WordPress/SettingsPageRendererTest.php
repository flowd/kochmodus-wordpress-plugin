<?php

declare(strict_types = 1);

namespace Kochmodus\Tests\Unit\Infrastructure\WordPress;

use Brain\Monkey\Functions;
use Kochmodus\Application\Settings\SettingsServiceInterface;
use Kochmodus\Infrastructure\WordPress\SettingsPageRenderer;
use Kochmodus\Tests\Unit\Infrastructure\WordPressTestCase;
use Mockery;
use Mockery\MockInterface;

final class SettingsPageRendererTest extends WordPressTestCase
{
    public function test_sanitize_settings_returns_sanitized_array_for_valid_input(): void
    {
        Functions\expect('sanitize_text_field')
            ->once()
            ->with('  token-abc  ')
            ->andReturn('token-abc');

        $renderer = new SettingsPageRenderer($this->createSettingsService());

        $result = $renderer->sanitizeSettings(['access_token' => '  token-abc  ']);

        $this->assertSame(['access_token' => 'token-abc'], $result);
    }

    public function test_sanitize_settings_returns_existing_option_when_input_is_not_array(): void
    {
        Functions\expect('add_settings_error')
            ->once()
            ->with('kochmodus_settings', 'invalid_input', Mockery::type('string'));
        Functions\expect('get_option')
            ->once()
            ->with('kochmodus_settings', null)
            ->andReturn(['access_token' => 'previous']);

        $renderer = new SettingsPageRenderer($this->createSettingsService());

        $result = $renderer->sanitizeSettings('not-an-array');

        $this->assertSame(['access_token' => 'previous'], $result);
    }

    public function test_sanitize_settings_returns_empty_array_when_no_existing_option(): void
    {
        Functions\expect('add_settings_error')->once();
        Functions\expect('get_option')
            ->once()
            ->with('kochmodus_settings', null)
            ->andReturnNull();

        $renderer = new SettingsPageRenderer($this->createSettingsService());

        $result = $renderer->sanitizeSettings(null);

        $this->assertSame([], $result);
    }

    public function test_sanitize_settings_rejects_empty_access_token_with_validation_error(): void
    {
        Functions\expect('sanitize_text_field')
            ->once()
            ->with('')
            ->andReturn('');
        Functions\expect('add_settings_error')
            ->once()
            ->with('kochmodus_settings', 'validation_error', Mockery::on(static function ($message): bool {
                return is_string($message) && strpos($message, 'Access Token:') === 0;
            }));
        Functions\expect('get_option')
            ->once()
            ->andReturn([]);

        $renderer = new SettingsPageRenderer($this->createSettingsService());

        $result = $renderer->sanitizeSettings(['access_token' => '']);

        $this->assertSame([], $result);
    }

    public function test_sanitize_settings_uses_empty_string_when_access_token_key_missing(): void
    {
        Functions\expect('sanitize_text_field')
            ->once()
            ->with('')
            ->andReturn('');
        Functions\expect('add_settings_error')->once();
        Functions\expect('get_option')->once()->andReturn([]);

        $renderer = new SettingsPageRenderer($this->createSettingsService());

        $result = $renderer->sanitizeSettings([]);

        $this->assertSame([], $result);
    }

    private function createSettingsService(): SettingsServiceInterface
    {
        /** @var SettingsServiceInterface&MockInterface $service */
        $service = Mockery::mock(SettingsServiceInterface::class);
        return $service;
    }
}
