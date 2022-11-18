<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OAuthController extends AbstractController
{

    #[Route('/xxx/oauth/authorize', name: 'oauth_authorize', methods: ['GET'])]
    public function authorize(Request $request): Response
    {
        // Just approve the thing....
        $url = $request->get('redirect_uri');
        $url .= '?code=' . bin2hex(random_bytes(32));

        return new RedirectResponse($url);
    }

    #[Route('/xxx/oauth/token', name: 'oauth_token', methods: ['POST'])]
    public function token(Request $request): Response
    {
        $data = [
            'access_token' => 'DHPTACC_' . strtoupper(bin2hex(random_bytes(32))),
            'token_type' => 'bearer',
            'expires_in' => 3600,
            'refresh_token' => 'DHPTREF_' . strtoupper(bin2hex(random_bytes(32))),
        ];

        return new JsonResponse($data);
    }
}
