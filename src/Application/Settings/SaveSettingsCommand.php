<?php

declare(strict_types = 1);

namespace Kochmodus\Application\Settings;

final class SaveSettingsCommand
{
    /** @var string */
    private $accessToken;

    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function accessToken(): string
    {
        return $this->accessToken;
    }
}
