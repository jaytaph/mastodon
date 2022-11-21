<?php

declare(strict_types=1);

namespace App;

class Config
{
    // The current administrator of the site.
    public const ADMIN_USER = 'jaytaph';

    public const SITE_DOMAIN = 'dhpt.nl';

    // The site URL
    public const SITE_URL = 'https://' . self::SITE_DOMAIN;

    // When doing API requests, this is the logged in user (@TODO: needs security)
    public const LOGGEDIN_USER_URL = self::SITE_URL . '/users/' . self::ADMIN_USER;
}
