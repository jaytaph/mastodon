<?php

declare(strict_types=1);

namespace App\Security\OAuth;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class DevelopmentAuthenticator implements AuthenticatorInterface
{
    protected UserProviderInterface $userProvider;
    protected bool $oauthOverride;
    protected string $oauthOverrideUser;

    public function __construct(UserProviderInterface $userProvider, bool $oauthOverride, string $oauthOverrideUser = "")
    {
        $this->userProvider = $userProvider;
        $this->oauthOverride = $oauthOverride;
        $this->oauthOverrideUser = $oauthOverrideUser;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function supports(Request $request): ?bool
    {
        return $this->oauthOverride && $this->oauthOverrideUser != '';
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new Response('Authentication Required', 401);
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function doAuthenticate(Request $request): Passport
    {
        $userLoader = function (string $userIdentifier): UserInterface {
            return $this->userProvider->loadUserByIdentifier($userIdentifier);
        };

        $userBadge = new UserBadge($this->oauthOverrideUser, $userLoader);
        return new SelfValidatingPassport($userBadge, []);
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw $exception;
    }

    public function authenticate(Request $request): Passport
    {
        return $this->doAuthenticate($request);
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        return new UsernamePasswordToken($passport->getUser(), $firewallName, [
            'ROLE_USER',
            'ROLE_OAUTH2_WRITE',
            'ROLE_OAUTH2_READ',
            'ROLE_OAUTH2_FOLLOW',
            'ROLE_OAUTH2_PUSH',
        ]);
    }
}
