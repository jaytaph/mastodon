<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/*
 * Checks if the logged-in user has an account, and if not, redirects to the account registration page.
 */

class RedirectWhenNoAccountListener implements EventSubscriberInterface
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        // Check if a user has logged in
        // check if the user is a non-admin
        // check if the user has an account
        // if no account, redirect to account registration page
    }
}
