<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Application\Button;

use Flowd\KochmodusWordpressPlugin\Domain\Settings\PluginSettings;

interface RenderButtonServiceInterface
{
    /**
     * @param callable(string): string|null $escapeAttribute
     */
    public function render(
        PluginSettings $settings,
        ?string $label = null,
        ?string $recipeUri = null,
        ?string $backgroundColor = null,
        ?string $hoverBackgroundColor = null,
        ?string $color = null,
        ?callable $escapeAttribute = null
    ): string;
}
