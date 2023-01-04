<?php

declare(strict_types=1);

namespace App\Security\OAuth;

use \League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri as BaseRedirectUri;

class RedirectUri extends BaseRedirectUri
{
    protected string $redirectUri;

    public function __construct(string $redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    public function __toString(): string
    {
        return $this->redirectUri;
    }
}
