<?php

declare(strict_types=1);

namespace App\Security\OAuth\Server;

use League\Bundle\OAuth2ServerBundle\Event\AuthorizationRequestResolveEvent;
use League\Bundle\OAuth2ServerBundle\OAuth2Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestResolver implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE => 'onRequestResolve',
        ];
    }

    public function onRequestResolve(AuthorizationRequestResolveEvent $event): void
    {
        $user = $event->getUser();

        if (null === $user) {
            return;
        }

        $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
    }
}
