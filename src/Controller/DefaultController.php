<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return new Response("donkey");
    }

    #[Route('/users/{user}/inbox', name: 'app_users_inbox')]
    public function inbox(string $user, Request $request): Response
    {
        file_put_contents("../var/uploads/{$user}-inbox.txt", json_encode($request->getContent(), true)."\n", FILE_APPEND);

        return new Response("donkey");
    }

    #[Route('/users/{user}', name: 'app_users_jaytaph')]
    public function user(string $user): Response
    {
        $data = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://dhpt.nl/users/jaytaph',
            'type' => 'Person',
            'preferredUsername' => 'jaytaph',
            'name' => 'Joshua Thijssen',
            'summary' => 'I am a software developer and breaker of things that are working.',
            'inbox' => 'https://dhpt.nl/users/jaytaph/inbox',
            'outbox' => 'https://dhpt.nl/users/jaytaph/outbox',
            'publicKey' => [
                'id' => 'https://dhpt.nl/users/jaytaph#main-key',
                'owner' => 'https://dhpt.nl/users/jaytaph',
                'publicKeyPem' => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtLF7UQZ3J/KuZ/PD20ae\n1exuuO9hXSOdJrIzQVQRcsEpjAO+BLQIAHYAgmrajDvpkkpIFywZlUiyIqjk5fCx\npnJ8nIwrPqEz1gloF743BO9gw+fZOQazPv5Kw6QuvyzojxC40LdcL5tqkB8A81GL\nSmWHGEd9tst+f3FC3IlUADjcJb5HVTMO0NraxC4bTBn0hv0Uw+bt61xsFgdSAXQ3\n8TURwgLnWc7ijtsKSLjUIWg6WwnVqjqXycdwaBw7Yg1V00hBF7uXWyLlZoeltKwJ\nPc8WNTP4fM1/iBvPzP9dNwJTuEKomvZziV4d0I+dAwzMe4QyZP4sBtsm8HDQ0YOp\nmwIDAQAB\n-----END PUBLIC KEY-----\n"
            ],
#            'followers' => 'https://dhpt.nl/users/jaytaph/followers',
#            'following' => 'https://dhpt.nl/users/jaytaph/following',
        ];

        $response = new JsonResponse($data);
        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }

    #[Route('/.well-known/webfinger', name: 'app_webfinger')]
    public function webfinger(Request $request): Response
    {
        $resource = $request->query->get('resource');
        if (! str_starts_with($resource, 'acct:')) {
            throw new BadRequestHttpException('Invalid resource');
        }
        $resource = str_replace('acct:', '', $resource);
        if ($resource != "jaytaph@dhpt.nl") {
            throw new NotFoundHttpException('User not found');
        }

        $data = [
            'subject' => 'acct:jaytaph@dhpt.nl',
            "aliases" => [
              "https://dhpt.nl/@jaytaph",
              "https://dhpt.nl/users/jaytaph"
            ],
            'links' => [
                [
                    "rel" => "http://webfinger.net/rel/profile-page",
                    "type" =>"text/html",
                    "href" => "https://dhpt.nl/@jaytaph"
                ],
                [
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => 'https://dhpt.nl/users/jaytaph',
                ],
                [
                    "rel" => "http://ostatus.org/schema/1.0/subscribe",
                    "template" => "https://dhpt.nl/users/jaytaph/follow?uri={uri}"
                ]
            ],
        ];

        $response = new JsonResponse($data);
        $response->headers->set('Content-Type', 'application/jrd+json');

        return $response;
    }

}
