<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Service\AccountService;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    protected AccountService $accountService;
    protected ClientManagerInterface $clientManager;

    public function __construct(AccountService $accountService, ClientManagerInterface $clientManager)
    {
        $this->accountService = $accountService;
        $this->clientManager = $clientManager;
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): Response
    {
        // This should never be called
        return new Response("DonkeyHeads Mastodon Server - Things will break here");
    }
}
