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
        if (! $event->isMainRequest()) {
            return;
        }

        $req = $event->getRequest();
        $data = [
            'method' => $req->getMethod(),
            'uri' => $req->getRequestUri(),
            'headers' => $req->headers->all(),
            'query' => $req->query->all(),
            'post' => $req->request->all(),
            'json' => $req->getContent(),
        ];

        file_put_contents('requestlogger.txt', json_encode($data) . "\n", FILE_APPEND);
    }
}
