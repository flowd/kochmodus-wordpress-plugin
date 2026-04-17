<?php

declare(strict_types = 1);

namespace Kochmodus\Application\Button;

use Kochmodus\Domain\Button\ButtonLabel;
use Kochmodus\Domain\Button\RecipeUri;
use Kochmodus\Domain\Settings\PluginSettings;

final class RenderButtonService implements RenderButtonServiceInterface
{
    /**
     * @param callable(string): string|null $escapeAttribute HTML attribute escaper (e.g. esc_attr)
     */
    public function render(
        PluginSettings $settings,
        string $label = ButtonLabel::DEFAULT,
        ?string $recipeUri = null,
        ?string $backgroundColor = null,
        ?string $hoverBackgroundColor = null,
        ?string $color = null,
        ?callable $escapeAttribute = null
    ): string {
        $esc = $escapeAttribute ?? function (string $v): string {
            return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        };

        $buttonLabel = new ButtonLabel($label);

        $recipeUriAttr = '';
        if ($recipeUri !== null && $recipeUri !== '') {
            $validatedUri = new RecipeUri($recipeUri);
            $recipeUriAttr = sprintf(' data-kochmodus-recipe-uri="%s"', $esc($validatedUri->value()));
        }

        $styleAttr = $this->buildStyleAttribute($backgroundColor, $hoverBackgroundColor, $color, $esc);

        return sprintf(
            '<kochmodus-button label="%s"%s%s data-kochmodus-access-token="%s"></kochmodus-button>',
            $esc($buttonLabel->value()),
            $recipeUriAttr,
            $styleAttr,
            $esc($settings->accessToken()->value())
        );
    }

    /**
     * @param callable(string): string $esc
     */
    private function buildStyleAttribute(?string $backgroundColor, ?string $hoverBackgroundColor, ?string $color, callable $esc): string
    {
        $styles = [];

        if ($backgroundColor !== null && $backgroundColor !== '') {
            $styles[] = '--kochmodus-button-background: ' . $backgroundColor;
        }

        if ($hoverBackgroundColor !== null && $hoverBackgroundColor !== '') {
            $styles[] = '--kochmodus-button-hover-background: ' . $hoverBackgroundColor;
        }

        if ($color !== null && $color !== '') {
            $styles[] = '--kochmodus-button-color: ' . $color;
        }

        if ($styles === []) {
            return '';
        }

        return sprintf(' style="%s"', $esc(implode('; ', $styles)));
    }
}
