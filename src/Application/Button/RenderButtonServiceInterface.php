<?php

declare(strict_types = 1);

namespace Kochmodus\Application\Button;

use Kochmodus\Domain\Button\ButtonLabel;
use Kochmodus\Domain\Settings\PluginSettings;

interface RenderButtonServiceInterface
{
    /**
     * @param callable(string): string|null $escapeAttribute
     */
    public function render(
        PluginSettings $settings,
        string $label = ButtonLabel::DEFAULT,
        ?string $recipeUri = null,
        ?string $backgroundColor = null,
        ?string $hoverBackgroundColor = null,
        ?string $color = null,
        ?callable $escapeAttribute = null
    ): string;
}
