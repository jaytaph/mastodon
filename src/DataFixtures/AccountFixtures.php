<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Config;
use App\Entity\Account;
use App\JsonArray;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AccountFixtures extends Fixture
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {

        $account = new Account();
        $account->setAcct(Config::ADMIN_USER . '@' . Config::SITE_DOMAIN);
        $account->setDisplayName(Config::ADMIN_USER);
        $account->setCreatedAt(new \DateTimeImmutable());
        $account->setLastStatusAt(new \DateTimeImmutable());
        $account->setSource(JsonArray::empty());
        $account->setEmojis(JsonArray::empty());
        $account->setFields(JsonArray::empty());

        $config = [
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
            'digest_alg' => 'sha512',
        ];
        $keyPair = openssl_pkey_new($config);
        if ($keyPair === false) {
            throw new \Exception('Could not generate key pair');
        }
        $pubKey = openssl_pkey_get_details($keyPair);
        if ($pubKey === false) {
            throw new \Exception('Could not generate key pair');
        }

        openssl_pkey_export($keyPair, $privKey);
        $account->setPublicKeyPem($pubKey['key']);
        $account->setPrivateKeyPem($privKey);

        $manager->persist($account);
        $manager->flush();
    }
}
