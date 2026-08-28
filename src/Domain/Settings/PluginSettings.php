<?php

declare(strict_types = 1);

namespace Flowd\KochmodusWordpressPlugin\Domain\Settings;

use Flowd\KochmodusWordpressPlugin\Domain\Button\ButtonLabel;
use Flowd\KochmodusWordpressPlugin\Domain\Button\Color;

final class PluginSettings
{
    private AccessToken $accessToken;

    private ?Color $defaultBackgroundColor;

    private ?Color $defaultHoverBackgroundColor;

    private ?Color $defaultColor;

    private ?ButtonLabel $defaultLabel;

    public function __construct(
        AccessToken $accessToken,
        ?Color $defaultBackgroundColor = null,
        ?Color $defaultHoverBackgroundColor = null,
        ?Color $defaultColor = null,
        ?ButtonLabel $defaultLabel = null
    ) {
        $this->accessToken = $accessToken;
        $this->defaultBackgroundColor = $defaultBackgroundColor;
        $this->defaultHoverBackgroundColor = $defaultHoverBackgroundColor;
        $this->defaultColor = $defaultColor;
        $this->defaultLabel = $defaultLabel;
    }

    public function accessToken(): AccessToken
    {
        return $this->accessToken;
    }

    public function defaultBackgroundColor(): ?Color
    {
        return $this->defaultBackgroundColor;
    }

    public function defaultHoverBackgroundColor(): ?Color
    {
        return $this->defaultHoverBackgroundColor;
    }

    public function defaultColor(): ?Color
    {
        return $this->defaultColor;
    }

    public function defaultLabel(): ?ButtonLabel
    {
        return $this->defaultLabel;
    }
}
