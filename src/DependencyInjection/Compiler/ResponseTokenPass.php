<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Security\OAuth\Server\ScopedBearerTokenResponse;
use League\OAuth2\Server\AuthorizationServer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ResponseTokenPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Override the default token response class so we have a response with scopes in it.
        $authorizationServer = $container->findDefinition(AuthorizationServer::class);
        $authorizationServer->addArgument(new Reference(ScopedBearerTokenResponse::class));
    }
}
