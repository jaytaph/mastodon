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
        // @TODO: Change this to actually go to a page with a button that will ask to confirm the app

        // If we are logged in, we are automatically authorized.
        $user = $event->getUser();

        if (null === $user) {
            return;
        }

        $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
    }
}
