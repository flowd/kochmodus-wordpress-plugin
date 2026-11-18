<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Application\Button;

use Flowd\KochmodusWordpressPlugin\Domain\Button\Alignment;
use Flowd\KochmodusWordpressPlugin\Domain\Button\ButtonLabel;
use Flowd\KochmodusWordpressPlugin\Domain\Button\Color;
use Flowd\KochmodusWordpressPlugin\Domain\Button\RecipeUri;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;
use InvalidArgumentException;

final class RenderButtonService implements RenderButtonServiceInterface
{
    /**
     * The button sits in a block-level wrapper: the custom element itself is
     * inline, so theme layouts that center or constrain block children (auto
     * margins, max-width) would not position it. The wrapper takes part in
     * those layouts like a paragraph, and an inline text-align on it places
     * the button independently of the theme's stylesheet.
     *
     * @param callable(string): string|null $escapeAttribute HTML attribute escaper (e.g. esc_attr)
     * @param string|null $alignment left, center or right; anything else keeps the alignment the theme gives the wrapper
     */
    public function render(
        PluginSettings $settings,
        ?string $label = null,
        ?string $recipeUri = null,
        ?string $backgroundColor = null,
        ?string $hoverBackgroundColor = null,
        ?string $color = null,
        ?callable $escapeAttribute = null,
        ?string $alignment = null
    ): string {
        $esc = $escapeAttribute ?? function (string $v): string {
            return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        };

        $buttonLabel = $this->resolveLabel($settings, $label);

        $recipeUriAttr = '';
        $validatedUri = $this->validateRecipeUri($recipeUri);
        if ($validatedUri !== null) {
            $recipeUriAttr = sprintf(' data-kochmodus-recipe-uri="%s"', $esc($validatedUri));
        }

        $styleAttr = $this->buildStyleAttribute($settings, $backgroundColor, $hoverBackgroundColor, $color, $esc);

        $wrapperStyleAttr = '';
        $validatedAlignment = $this->validateAlignment($alignment);
        if ($validatedAlignment !== null) {
            $wrapperStyleAttr = sprintf(' style="%s"', $esc('text-align: ' . $validatedAlignment));
        }

        return sprintf(
            '<div class="kochmodus-button-wrapper"%s><kochmodus-button label="%s"%s%s data-kochmodus-access-token="%s"></kochmodus-button></div>',
            $wrapperStyleAttr,
            $esc($buttonLabel->value()),
            $recipeUriAttr,
            $styleAttr,
            $esc($settings->accessToken()->value())
        );
    }

    private function validateAlignment(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return (new Alignment($value))->value();
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    private function resolveLabel(PluginSettings $settings, ?string $label): ButtonLabel
    {
        if ($label !== null && trim($label) !== '') {
            return new ButtonLabel($label);
        }

        if ($settings->defaultLabel() instanceof ButtonLabel) {
            return $settings->defaultLabel();
        }

        return new ButtonLabel();
    }

    /**
     * @param callable(string): string $esc
     */
    private function buildStyleAttribute(PluginSettings $settings, ?string $backgroundColor, ?string $hoverBackgroundColor, ?string $color, callable $esc): string
    {
        $styles = [];

        $validatedBackground = $this->validateColor($backgroundColor)
            ?? ($settings->defaultBackgroundColor() instanceof Color ? $settings->defaultBackgroundColor()->value() : null);
        if ($validatedBackground !== null) {
            $styles[] = '--kochmodus-button-background: ' . $validatedBackground;
        }

        $validatedHoverBackground = $this->validateColor($hoverBackgroundColor)
            ?? ($settings->defaultHoverBackgroundColor() instanceof Color ? $settings->defaultHoverBackgroundColor()->value() : null);
        if ($validatedHoverBackground !== null) {
            $styles[] = '--kochmodus-button-hover-background: ' . $validatedHoverBackground;
        }

        $validatedColor = $this->validateColor($color)
            ?? ($settings->defaultColor() instanceof Color ? $settings->defaultColor()->value() : null);
        if ($validatedColor !== null) {
            $styles[] = '--kochmodus-button-color: ' . $validatedColor;
        }

        if ($styles === []) {
            return '';
        }

        return sprintf(' style="%s"', $esc(implode('; ', $styles)));
    }

    private function validateColor(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return (new Color($value))->value();
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    private function validateRecipeUri(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return (new RecipeUri($value))->value();
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }
}
