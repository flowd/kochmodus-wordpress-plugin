<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Application\Settings;

final class SaveSettingsCommand
{
    private string $accessToken;

    private ?string $backgroundColor;

    private ?string $hoverBackgroundColor;

    private ?string $color;

    private ?string $label;

    public function __construct(
        string $accessToken,
        ?string $backgroundColor = null,
        ?string $hoverBackgroundColor = null,
        ?string $color = null,
        ?string $label = null
    ) {
        $this->accessToken = $accessToken;
        $this->backgroundColor = $backgroundColor;
        $this->hoverBackgroundColor = $hoverBackgroundColor;
        $this->color = $color;
        $this->label = $label;
    }

    public function accessToken(): string
    {
        return $this->accessToken;
    }

    public function backgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function hoverBackgroundColor(): ?string
    {
        return $this->hoverBackgroundColor;
    }

    public function color(): ?string
    {
        return $this->color;
    }

    public function label(): ?string
    {
        return $this->label;
    }
}
