<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Tests\Unit\Infrastructure\WordPress;

use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;
use Flowd\KochmodusWordpressPlugin\Application\Settings\SettingsServiceInterface;
use Flowd\KochmodusWordpressPlugin\Domain\Button\ButtonLabel;
use Flowd\KochmodusWordpressPlugin\Domain\Button\Color;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\AccessToken;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;
use Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress\SettingsPageRenderer;
use Flowd\KochmodusWordpressPlugin\Tests\Unit\Infrastructure\WordPressTestCase;
use Mockery;
use Mockery\MockInterface;

final class SettingsPageRendererTest extends WordPressTestCase
{
    public function test_sanitize_settings_returns_sanitized_array_for_valid_input(): void
    {
        expect('sanitize_text_field')
            ->times(5)
            ->andReturnUsing(static function (string $value): string {
                return trim($value);
            });

        $renderer = new SettingsPageRenderer($this->createSettingsService());

        $result = $renderer->sanitizeSettings(['access_token' => '  token-abc  ']);

        $this->assertSame([
            'access_token' => 'token-abc',
            'label' => '',
            'background_color' => '',
            'hover_background_color' => '',
            'color' => '',
        ], $result);
    }

    public function test_sanitize_settings_accepts_valid_colors_and_label(): void
    {
        expect('sanitize_text_field')
            ->times(5)
            ->andReturnUsing(static function (string $value): string {
                return trim($value);
            });

        $renderer = new SettingsPageRenderer($this->createSettingsService());

        $result = $renderer->sanitizeSettings([
            'access_token' => 'token-abc',
            'label' => 'Jetzt kochen!',
            'background_color' => '#ff0000',
            'hover_background_color' => '#cc0000',
            'color' => 'rgb(255, 255, 255)',
        ]);

        $this->assertSame([
            'access_token' => 'token-abc',
            'label' => 'Jetzt kochen!',
            'background_color' => '#ff0000',
            'hover_background_color' => '#cc0000',
            'color' => 'rgb(255, 255, 255)',
        ], $result);
    }

    public function test_sanitize_settings_rejects_invalid_color_with_validation_error(): void
    {
        expect('sanitize_text_field')
            ->times(3)
            ->andReturnUsing(static function (string $value): string {
                return trim($value);
            });
        expect('add_settings_error')
            ->once()
            ->with('kochmodus_settings', 'validation_error', Mockery::type('string'));
        expect('get_option')
            ->once()
            ->with('kochmodus_settings', null)
            ->andReturn(['access_token' => 'previous']);

        $renderer = new SettingsPageRenderer($this->createSettingsService());

        $result = $renderer->sanitizeSettings([
            'access_token' => 'token-abc',
            'background_color' => 'not-a-color',
        ]);

        $this->assertSame(['access_token' => 'previous'], $result);
    }

    public function test_sanitize_settings_returns_existing_option_when_input_is_not_array(): void
    {
        expect('add_settings_error')
            ->once()
            ->with('kochmodus_settings', 'invalid_input', Mockery::type('string'));
        expect('get_option')
            ->once()
            ->with('kochmodus_settings', null)
            ->andReturn(['access_token' => 'previous']);

        $renderer = new SettingsPageRenderer($this->createSettingsService());

        $result = $renderer->sanitizeSettings('not-an-array');

        $this->assertSame(['access_token' => 'previous'], $result);
    }

    public function test_sanitize_settings_returns_empty_array_when_no_existing_option(): void
    {
        expect('add_settings_error')->once();
        expect('get_option')
            ->once()
            ->with('kochmodus_settings', null)
            ->andReturnNull();

        $renderer = new SettingsPageRenderer($this->createSettingsService());

        $result = $renderer->sanitizeSettings(null);

        $this->assertSame([], $result);
    }

    public function test_sanitize_settings_rejects_empty_access_token_with_validation_error(): void
    {
        expect('sanitize_text_field')
            ->once()
            ->with('')
            ->andReturn('');
        expect('add_settings_error')
            ->once()
            ->with('kochmodus_settings', 'validation_error', Mockery::on(static function ($message): bool {
                return is_string($message) && strpos($message, 'Access Token:') === 0;
            }));
        expect('get_option')
            ->once()
            ->andReturn([]);

        $renderer = new SettingsPageRenderer($this->createSettingsService());

        $result = $renderer->sanitizeSettings(['access_token' => '']);

        $this->assertSame([], $result);
    }

    public function test_sanitize_settings_uses_empty_string_when_access_token_key_missing(): void
    {
        expect('sanitize_text_field')
            ->once()
            ->with('')
            ->andReturn('');
        expect('add_settings_error')->once();
        expect('get_option')->once()->andReturn([]);

        $renderer = new SettingsPageRenderer($this->createSettingsService());

        $result = $renderer->sanitizeSettings([]);

        $this->assertSame([], $result);
    }

    public function test_register_settings_registers_all_sections_and_fields(): void
    {
        expect('register_setting')
            ->once()
            ->with('kochmodus_settings_group', 'kochmodus_settings', Mockery::type('array'));
        expect('add_settings_section')->twice();
        expect('add_settings_field')->times(5);

        $renderer = new SettingsPageRenderer($this->createSettingsService());
        $renderer->registerSettings();
    }

    public function test_render_label_field_outputs_saved_default_label(): void
    {
        when('esc_attr')->returnArg();

        $settings = new PluginSettings(
            new AccessToken('token123'),
            null,
            null,
            null,
            new ButtonLabel('Jetzt kochen!')
        );

        $renderer = new SettingsPageRenderer($this->createSettingsService($settings));

        ob_start();
        $renderer->renderLabelField();
        $output = (string)ob_get_clean();

        $this->assertStringContainsString('name="kochmodus_settings[label]"', $output);
        $this->assertStringContainsString('value="Jetzt kochen!"', $output);
        $this->assertStringContainsString('placeholder="' . ButtonLabel::DEFAULT . '"', $output);
    }

    public function test_render_label_field_outputs_empty_value_when_not_configured(): void
    {
        when('esc_attr')->returnArg();

        $renderer = new SettingsPageRenderer($this->createSettingsService());

        ob_start();
        $renderer->renderLabelField();
        $output = (string)ob_get_clean();

        $this->assertStringContainsString('value=""', $output);
    }

    public function test_render_color_field_outputs_saved_color_and_picker_class(): void
    {
        when('esc_attr')->returnArg();

        $settings = new PluginSettings(
            new AccessToken('token123'),
            new Color('#ff0000')
        );

        $renderer = new SettingsPageRenderer($this->createSettingsService($settings));

        ob_start();
        $renderer->renderColorField(['key' => 'background_color']);
        $output = (string)ob_get_clean();

        $this->assertStringContainsString('name="kochmodus_settings[background_color]"', $output);
        $this->assertStringContainsString('value="#ff0000"', $output);
        $this->assertStringContainsString('kochmodus-color-field', $output);
    }

    public function test_render_color_field_outputs_empty_value_when_color_not_set(): void
    {
        when('esc_attr')->returnArg();

        $settings = new PluginSettings(new AccessToken('token123'));

        $renderer = new SettingsPageRenderer($this->createSettingsService($settings));

        ob_start();
        $renderer->renderColorField(['key' => 'color']);
        $output = (string)ob_get_clean();

        $this->assertStringContainsString('name="kochmodus_settings[color]"', $output);
        $this->assertStringContainsString('value=""', $output);
    }

    /**
     * @param PluginSettings|null $settings Passing a value sets up a getSettings expectation.
     */
    private function createSettingsService(?PluginSettings $settings = null): SettingsServiceInterface
    {
        /** @var SettingsServiceInterface&MockInterface $service */
        $service = Mockery::mock(SettingsServiceInterface::class);
        $service->shouldReceive('getSettings')->andReturn($settings)->byDefault();
        return $service;
    }
}
