<?php

declare(strict_types = 1);

namespace Kochmodus\Domain\Settings;

final class PluginSettings
{
    private AccessToken $accessToken;

    public function __construct(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function accessToken(): AccessToken
    {
        return $this->accessToken;
    }
}
