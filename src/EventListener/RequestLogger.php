<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestLogger implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => [
                ['onKernelRequest', 512],
            ]
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $data = [
            'method' => $event->getRequest()->getMethod(),
            'uri' => $event->getRequest()->getRequestUri(),
            'headers' => $event->getRequest()->headers->all(),
            'query' => $event->getRequest()->query->all(),
            'post' => $event->getRequest()->request->all(),
        ];

        file_put_contents('requestlogger.txt', json_encode($data) . "\n", FILE_APPEND);
    }
}
