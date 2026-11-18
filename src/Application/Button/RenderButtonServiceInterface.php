<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Application\Button;

use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;

interface RenderButtonServiceInterface
{
    /**
     * @param callable(string): string|null $escapeAttribute
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
    ): string;
}
