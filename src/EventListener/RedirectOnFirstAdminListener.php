<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use App\Service\ConfigService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

/*
 * Checks if there are any users in the database. If not, it will redirect to the first-time config page to create the first admin.
 */

class RedirectOnFirstAdminListener implements EventSubscriberInterface
{
    protected RouterInterface $router;
    protected EntityManagerInterface $doctrine;
    protected ConfigService $configService;

    public function __construct(EntityManagerInterface $doctrine, ConfigService $configService, RouterInterface $router)
    {
        $this->router = $router;
        $this->doctrine = $doctrine;
        $this->configService = $configService;
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

        $firstTimeUrl = $this->router->generate('admin_first_time');
        $currentUrl = $event->getRequest()->getRequestUri();

        // Create a new config if none exists
        if (!$this->configService->hasConfig()) {
            $this->configService->createDefaultConfig();
        }

        // Continue the request when there is at least one user. This means we have created at least 1 admin.
        $total = $this->doctrine->getRepository(User::class)->count([]);
        if ($total > 0) {
            return;
        }

        // Continue when the current URL is the first-time config page (to prevent infinite redirect)
        if ($currentUrl == $firstTimeUrl) {
            return;
        }

        $event->setResponse(new RedirectResponse($firstTimeUrl));
    }
}
