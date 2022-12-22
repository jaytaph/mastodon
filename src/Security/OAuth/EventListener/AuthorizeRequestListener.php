<?php

declare(strict_types=1);

namespace App\Security\OAuth\EventListener;

use League\Bundle\OAuth2ServerBundle\Event\AuthorizationRequestResolveEvent;
use League\Bundle\OAuth2ServerBundle\OAuth2Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Twig\Environment;

class AuthorizeRequestListener implements EventSubscriberInterface
{
    protected Environment $twig;
    protected RequestStack $requestStack;
    protected UserProviderInterface $userProvider;
    protected UserPasswordHasherInterface $hasher;
    protected TokenStorageInterface $tokenStorage;

    public function __construct(
        Environment $twig,
        RequestStack $requestStack,
        UserProviderInterface $userProvider,
        UserPasswordHasherInterface $hasher,
        TokenStorageInterface $tokenStorage
    ) {
        $this->twig = $twig;
        $this->requestStack = $requestStack;
        $this->userProvider = $userProvider;
        $this->hasher = $hasher;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE => 'onAuthorizeRequest',
        ];
    }

    public function onAuthorizeRequest(AuthorizationRequestResolveEvent $event): void
    {
        $errors = [];

        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->getMethod() == 'POST') {
            $errors = $this->processSubmit($request, $event);
            if (count($errors) == 0) {
                return;
            }
        }

        $response = $this->twig->render('oauth/authorize.html.twig', [
            'client' => $event->getClient(),
            'last_username' => $request?->request->get('_username') ?? '',
            'user' => $this->tokenStorage->getToken()?->getUser(),
            'error' => $errors,
        ]);

        $event->setResponse(new Response($response));
    }

    /**
     * @param Request $request
     * @param AuthorizationRequestResolveEvent $event
     * @return array<string, string[]|string>
     */
    protected function processSubmit(Request $request, AuthorizationRequestResolveEvent $event): array
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        // If we are logged in, we can simply check the accept/deny buttons
        if ($user) {
            if ($request->request->get('accept')) {
                $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
                $event->setUser($user);
            } else {
                $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_DENIED);
            }

            return [];
        }


        try {
            $errors = [];
            $userIdentifier = $request->request->get('_username') ?? '';
            $user = $this->userProvider->loadUserByIdentifier(strval($userIdentifier));

            /** @var PasswordAuthenticatedUserInterface $user */
            if ($this->hasher->isPasswordValid($user, strval($request->request->get('_password')))) {
                $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
                /** @var UserInterface $user */
                $event->setUser($user);

                return [];
            }
        } catch (UserNotFoundException $exception) {
            $errors['messageKey'] = 'Invalid credentials.';
            $errors['messageData'] = [];
        }

        return $errors;
    }
}
