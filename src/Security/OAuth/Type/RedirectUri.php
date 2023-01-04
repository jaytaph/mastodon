<?php

declare(strict_types=1);

namespace App\Security\OAuth\Type;

use League\Bundle\OAuth2ServerBundle\DBAL\Type\ImplodedArray;
use App\Security\OAuth\RedirectUri as RedirectUriModel;

/**
 * @template-extends ImplodedArray<RedirectUriModel>
 */
class RedirectUri extends ImplodedArray
{
    /**
     * @var string
     */
    private const NAME = 'oauth2_redirect_uri';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param list<string> $values
     *
     * @return list<RedirectUriModel>
     */
    protected function convertDatabaseValues(array $values): array
    {
        dump($values);
        return array_map(static function (string $value): RedirectUriModel {
            return new RedirectUriModel($value);
        }, $values);
    }
}
