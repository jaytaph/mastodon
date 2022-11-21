<?php

declare(strict_types=1);

namespace App\Security\OAuth\Server;

use League\Bundle\OAuth2ServerBundle\Event\TokenRequestResolveEvent;
use League\Bundle\OAuth2ServerBundle\OAuth2Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TokenRequestResolver implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            OAuth2Events::TOKEN_REQUEST_RESOLVE => 'onRequestResolve',
        ];
    }

    public function onRequestResolve(TokenRequestResolveEvent $event): void
    {
        $token = json_decode($event->getResponse()->getContent(), true);
    }
}
