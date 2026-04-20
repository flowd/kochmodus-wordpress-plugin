<?php

declare(strict_types = 1);

namespace Kochmodus\Tests\Unit\Application\Button;

use Kochmodus\Application\Button\RenderButtonService;
use Kochmodus\Domain\Settings\AccessToken;
use Kochmodus\Domain\Settings\PluginSettings;
use PHPUnit\Framework\TestCase;

final class RenderButtonServiceTest extends TestCase
{
    private \Kochmodus\Domain\Settings\PluginSettings $settings;

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
