<?php

namespace App\Security\OAuth\Server;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use Psr\Http\Message\ResponseInterface;

class ScopedBearerTokenResponse extends BearerTokenResponse
{

    public function generateHttpResponse(ResponseInterface $response)
        {
            $expireDateTime = $this->accessToken->getExpiryDateTime()->getTimestamp();

            $scope = "";
            foreach ($this->accessToken->getScopes() as $v) {
                $scope .= " " . $v->getIdentifier();
            }
            $scope = trim($scope);


            $responseParams = [
                'token_type'   => 'Bearer',
                'scope' => $scope,
                'expires_in'   => $expireDateTime - \time(),
                'access_token' => (string) $this->accessToken,
            ];
            $responseParams = json_encode($responseParams);

            $response = $response
                ->withStatus(200)
                ->withHeader('pragma', 'no-cache')
                ->withHeader('cache-control', 'no-store')
                ->withHeader('content-type', 'application/json; charset=UTF-8');

            $response->getBody()->write($responseParams);

            return $response;
        }

    /**
     * Add custom fields to your Bearer Token response here, then override
     * AuthorizationServer::getResponseType() to pull in your version of
     * this class rather than the default.
     *
     * @param AccessTokenEntityInterface $accessToken
     *
     * @return array
     */
    protected function getExtraParams(AccessTokenEntityInterface $accessToken)
    {

        return [
            'scope' => $scope,
            'created_at' => time(),
        ];
    }
}
