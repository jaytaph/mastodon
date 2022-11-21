<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class LogRequestResponse implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    // Logs the response
    public function onKernelResponse(ResponseEvent $event)
    {
//        if ($event->getRequestType() !== HttpKernelInterface::MAIN_REQUEST) {
//            return;
//        }
//
//        $request = $event->getRequest();
//        file_put_contents('req.log', json_encode($request->request->all())."\n\n", FILE_APPEND);
//
//        $response = $event->getResponse();
//        file_put_contents('req.log', $response->getContent()."\n\n", FILE_APPEND);
    }
}
