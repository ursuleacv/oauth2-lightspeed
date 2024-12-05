<?php

namespace League\OAuth2\Client\Provider;

use InvalidArgumentException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Http\Message\ResponseInterface;

class Lightspeed extends AbstractProvider
{
    const LIGHTSPEED_API_URL = 'https://api.merchantos.com/API/';
    const LIGHTSPEED_REGISTRATION_ENDPOINT = 'https://cloud.merchantos.com/oauth/register.php';
    const LIGHTSPEED_AUTHORIZATION_ENDPOINT = 'https://cloud.merchantos.com/oauth/authorize.php';
    const LIGHTSPEED_TOKEN_ENDPOINT = 'https://cloud.merchantos.com/oauth/access_token.php';

    /**
     * @var mixed
     */
    protected $accountId;

    /**
     * @param array $options
     * @param array $collaborators
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
    }

    public function getBaseAuthorizationUrl()
    {
        return static::LIGHTSPEED_AUTHORIZATION_ENDPOINT;
    }

    /**
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return static::LIGHTSPEED_TOKEN_ENDPOINT;
    }

    public function getDefaultScopes(): array
    {
        return ['employee:all'];
    }

    /**
     * @param $grant
     * @param array $params
     * @return AccessToken
     */
    public function getAccessToken($grant = 'authorization_code', array $params = []): AccessTokenInterface
    {
        return parent::getAccessToken($grant, $params);
    }

    /**
     * Exchanges a short-lived access token with a long-lived access-token.
     *
     * @param string $accessToken
     *
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function getLongLivedAccessToken($accessToken): AccessTokenInterface
    {
        $params = [
            'ls_exchange_token' => (string) $accessToken,
        ];

        return $this->getAccessToken('ls_exchange_token', $params);
    }

    /**
     * @param AccessToken $token
     * @return mixed
     */
    public function getAccountId(AccessToken $token)
    {
        $account = $this->getResourceOwner($token);

        return $account->getId();
    }

    /**
     * @param AccessToken $token
     * @return mixed
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->getBaseLightspeedApiUrl() . 'Account/.json?oauth_token=' . $token;
    }

    /**
     * @param array $response
     * @param AccessToken $token
     * @return LightspeedResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new LightspeedResourceOwner($response);
    }

    /**
     * @param AccessToken $token
     * @param $accountId
     * @return MerchantOS
     */
    public function merchantosApi(AccessToken $token, $accountId)
    {
        return new MerchantOS($token, $accountId);
    }

    /**
     * Returns all options that are required.
     *
     * @return array
     */
    protected function getRequiredOptions()
    {
        return [
            'urlAuthorize',
            'urlAccessToken',
        ];
    }

    /**
     * @param ResponseInterface $response
     * @param $data
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $message = $data['error'] . ': ' . $data['error_description'];
            throw new IdentityProviderException($message, $response->getStatusCode(), $data);
        }
    }

    /**
     * Get the Lightspeed api URL.
     *
     * @return string
     */
    protected function getBaseLightspeedApiUrl()
    {
        return static::LIGHTSPEED_API_URL;
    }

    /**
     * Verifies that all required options have been passed.
     *
     * @param  array $options
     * @return void
     * @throws InvalidArgumentException
     */
    private function assertRequiredOptions(array $options)
    {
        $missing = array_diff_key(array_flip($this->getRequiredOptions()), $options);
        if (!empty($missing)) {
            throw new InvalidArgumentException(
                'Required options not defined: ' . implode(', ', array_keys($missing))
            );
        }
    }
}
