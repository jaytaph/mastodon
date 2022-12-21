<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Repository\UserRepository;
use App\Service\InstanceConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

/*
 * Checks if there is a config. If none is present, it will redirect to the config page
 */

class RedirectOnNoConfigAdminListener implements EventSubscriberInterface
{
    protected RouterInterface $router;
    protected InstanceConfigService $configService;

    public function __construct(InstanceConfigService $configService, RouterInterface $router)
    {
        $this->configService = $configService;
        $this->router = $router;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($this->configService->hasConfig()) {
            return;
        }

        // Continue when the current URL is the first-time config page (to prevent infinite redirect)
        $configUrl = $this->router->generate('admin_config');
        $currentUrl = $event->getRequest()->getRequestUri();

        if ($currentUrl == $configUrl) {
            return;
        }

        $event->setResponse(new RedirectResponse($configUrl));
    }
}
