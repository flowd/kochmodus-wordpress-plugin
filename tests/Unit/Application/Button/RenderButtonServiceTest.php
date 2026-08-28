<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Tests\Unit\Application\Button;

use Flowd\KochmodusWordpressPlugin\Application\Button\RenderButtonService;
use Flowd\KochmodusWordpressPlugin\Domain\Button\ButtonLabel;
use Flowd\KochmodusWordpressPlugin\Domain\Button\Color;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\AccessToken;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;
use PHPUnit\Framework\TestCase;

final class RenderButtonServiceTest extends TestCase
{
    private PluginSettings $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->settings = new PluginSettings(
            new AccessToken('mytoken'),
        );
    }

    public function test_render_produces_correct_html_without_recipe_uri(): void
    {
        $service = new RenderButtonService();

        $html = $service->render($this->settings);

        $this->assertStringContainsString('<kochmodus-button', $html);
        $this->assertStringContainsString('</kochmodus-button>', $html);
        $this->assertStringContainsString('label="Kochmodus starten"', $html);
        $this->assertStringContainsString('data-kochmodus-access-token="mytoken"', $html);
        $this->assertStringNotContainsString('data-kochmodus-recipe-uri', $html);
        $this->assertStringNotContainsString('style=', $html);
    }

    public function test_render_includes_recipe_uri_when_explicitly_set(): void
    {
        $service = new RenderButtonService();

        $html = $service->render(
            $this->settings,
            'Kochmodus starten',
            'https://example.com/rezept/'
        );

        $this->assertStringContainsString(
            'data-kochmodus-recipe-uri="https://example.com/rezept/"',
            $html
        );
    }

    public function test_render_omits_recipe_uri_when_empty_string(): void
    {
        $service = new RenderButtonService();

        $html = $service->render(
            $this->settings,
            'Kochmodus starten',
            ''
        );

        $this->assertStringNotContainsString('data-kochmodus-recipe-uri', $html);
    }

    public function test_render_omits_invalid_recipe_uri_instead_of_throwing(): void
    {
        $service = new RenderButtonService();

        $html = $service->render(
            $this->settings,
            'Kochmodus starten',
            'rezept-slug'
        );

        $this->assertStringContainsString('<kochmodus-button', $html);
        $this->assertStringNotContainsString('data-kochmodus-recipe-uri', $html);
    }

    public function test_render_omits_recipe_uri_with_disallowed_scheme(): void
    {
        $service = new RenderButtonService();

        $html = $service->render(
            $this->settings,
            'Kochmodus starten',
            'javascript:alert(1)'
        );

        $this->assertStringContainsString('<kochmodus-button', $html);
        $this->assertStringNotContainsString('data-kochmodus-recipe-uri', $html);
    }

    public function test_render_uses_default_label_when_not_specified(): void
    {
        $service = new RenderButtonService();

        $html = $service->render($this->settings);

        $this->assertStringContainsString('label="Kochmodus starten"', $html);
    }

    public function test_render_uses_custom_label(): void
    {
        $service = new RenderButtonService();

        $html = $service->render(
            $this->settings,
            'Jetzt kochen!'
        );

        $this->assertStringContainsString('label="Jetzt kochen!"', $html);
    }

    public function test_render_includes_background_color_as_css_variable(): void
    {
        $service = new RenderButtonService();

        $html = $service->render(
            $this->settings,
            'Kochmodus starten',
            null,
            '#ff0000'
        );

        $this->assertStringContainsString('style="', $html);
        $this->assertStringContainsString('--kochmodus-button-background: #ff0000', $html);
    }

    public function test_render_includes_text_color_as_css_variable(): void
    {
        $service = new RenderButtonService();

        $html = $service->render(
            $this->settings,
            'Kochmodus starten',
            null,
            null,
            null,
            '#ffffff'
        );

        $this->assertStringContainsString('style="', $html);
        $this->assertStringContainsString('--kochmodus-button-color: #ffffff', $html);
    }

    public function test_render_includes_all_colors_as_css_variables(): void
    {
        $service = new RenderButtonService();

        $html = $service->render(
            $this->settings,
            'Kochmodus starten',
            null,
            '#ff0000',
            '#cc0000',
            '#ffffff'
        );

        $this->assertStringContainsString('--kochmodus-button-background: #ff0000', $html);
        $this->assertStringContainsString('--kochmodus-button-hover-background: #cc0000', $html);
        $this->assertStringContainsString('--kochmodus-button-color: #ffffff', $html);
    }

    public function test_render_omits_style_when_no_colors_set(): void
    {
        $service = new RenderButtonService();

        $html = $service->render($this->settings);

        $this->assertStringNotContainsString('style=', $html);
    }

    public function test_render_ignores_invalid_background_color(): void
    {
        $service = new RenderButtonService();

        $html = $service->render(
            $this->settings,
            'Kochmodus starten',
            null,
            'red; position: fixed; background-image: url(https://evil.example)'
        );

        $this->assertStringNotContainsString('style=', $html);
        $this->assertStringNotContainsString('position', $html);
        $this->assertStringNotContainsString('evil.example', $html);
    }

    public function test_render_drops_invalid_color_but_keeps_valid_ones(): void
    {
        $service = new RenderButtonService();

        $html = $service->render(
            $this->settings,
            'Kochmodus starten',
            null,
            '#ff0000',
            'injection; url(x)',
            '#ffffff'
        );

        $this->assertStringContainsString('--kochmodus-button-background: #ff0000', $html);
        $this->assertStringContainsString('--kochmodus-button-color: #ffffff', $html);
        $this->assertStringNotContainsString('--kochmodus-button-hover-background', $html);
        $this->assertStringNotContainsString('injection', $html);
    }

    public function test_render_omits_style_when_colors_are_empty_strings(): void
    {
        $service = new RenderButtonService();

        $html = $service->render(
            $this->settings,
            'Kochmodus starten',
            null,
            '',
            '',
            ''
        );

        $this->assertStringNotContainsString('style=', $html);
    }

    public function test_render_falls_back_to_settings_default_label(): void
    {
        $settings = new PluginSettings(
            new AccessToken('mytoken'),
            null,
            null,
            null,
            new ButtonLabel('Jetzt kochen!')
        );

        $service = new RenderButtonService();

        $html = $service->render($settings);

        $this->assertStringContainsString('label="Jetzt kochen!"', $html);
    }

    public function test_render_prefers_explicit_label_over_settings_default(): void
    {
        $settings = new PluginSettings(
            new AccessToken('mytoken'),
            null,
            null,
            null,
            new ButtonLabel('Jetzt kochen!')
        );

        $service = new RenderButtonService();

        $html = $service->render($settings, 'Los gehts');

        $this->assertStringContainsString('label="Los gehts"', $html);
    }

    public function test_render_uses_settings_default_label_when_explicit_label_empty(): void
    {
        $settings = new PluginSettings(
            new AccessToken('mytoken'),
            null,
            null,
            null,
            new ButtonLabel('Jetzt kochen!')
        );

        $service = new RenderButtonService();

        $html = $service->render($settings, '   ');

        $this->assertStringContainsString('label="Jetzt kochen!"', $html);
    }

    public function test_render_falls_back_to_settings_default_colors(): void
    {
        $settings = new PluginSettings(
            new AccessToken('mytoken'),
            new Color('#111111'),
            new Color('#222222'),
            new Color('#333333')
        );

        $service = new RenderButtonService();

        $html = $service->render($settings);

        $this->assertStringContainsString('--kochmodus-button-background: #111111', $html);
        $this->assertStringContainsString('--kochmodus-button-hover-background: #222222', $html);
        $this->assertStringContainsString('--kochmodus-button-color: #333333', $html);
    }

    public function test_render_prefers_explicit_colors_over_settings_defaults(): void
    {
        $settings = new PluginSettings(
            new AccessToken('mytoken'),
            new Color('#111111'),
            new Color('#222222'),
            new Color('#333333')
        );

        $service = new RenderButtonService();

        $html = $service->render(
            $settings,
            'Kochmodus starten',
            null,
            '#ff0000',
            '#cc0000',
            '#ffffff'
        );

        $this->assertStringContainsString('--kochmodus-button-background: #ff0000', $html);
        $this->assertStringContainsString('--kochmodus-button-hover-background: #cc0000', $html);
        $this->assertStringContainsString('--kochmodus-button-color: #ffffff', $html);
        $this->assertStringNotContainsString('#111111', $html);
        $this->assertStringNotContainsString('#222222', $html);
        $this->assertStringNotContainsString('#333333', $html);
    }

    public function test_render_mixes_explicit_colors_and_settings_defaults(): void
    {
        $settings = new PluginSettings(
            new AccessToken('mytoken'),
            new Color('#111111'),
            new Color('#222222'),
            new Color('#333333')
        );

        $service = new RenderButtonService();

        $html = $service->render(
            $settings,
            'Kochmodus starten',
            null,
            '#ff0000'
        );

        $this->assertStringContainsString('--kochmodus-button-background: #ff0000', $html);
        $this->assertStringContainsString('--kochmodus-button-hover-background: #222222', $html);
        $this->assertStringContainsString('--kochmodus-button-color: #333333', $html);
    }

    public function test_render_falls_back_to_settings_default_when_explicit_color_invalid(): void
    {
        $settings = new PluginSettings(
            new AccessToken('mytoken'),
            new Color('#111111')
        );

        $service = new RenderButtonService();

        $html = $service->render(
            $settings,
            'Kochmodus starten',
            null,
            'injection; url(x)'
        );

        $this->assertStringContainsString('--kochmodus-button-background: #111111', $html);
        $this->assertStringNotContainsString('injection', $html);
    }

    public function test_render_escapes_all_attributes(): void
    {
        /** @var list<string> $escaped */
        $escaped = [];
        $escaper = function (string $value) use (&$escaped): string {
            $escaped[] = $value;
            return 'ESC(' . $value . ')';
        };

        $service = new RenderButtonService();

        $html = $service->render(
            $this->settings,
            'Kochmodus starten',
            'https://example.com/rezept/',
            '#ff0000',
            '#cc0000',
            '#ffffff',
            $escaper
        );

        $this->assertCount(4, $escaped);
        $this->assertContains('Kochmodus starten', $escaped);
        $this->assertContains('https://example.com/rezept/', $escaped);
        $this->assertContains('--kochmodus-button-background: #ff0000; --kochmodus-button-hover-background: #cc0000; --kochmodus-button-color: #ffffff', $escaped);
        $this->assertContains('mytoken', $escaped);
        $this->assertStringContainsString('ESC(', $html);
    }
}
