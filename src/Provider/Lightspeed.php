<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Exception\LightspeedProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Lightspeed extends AbstractProvider
{

    const LIGHTSPEED_API_URL = 'https://api.merchantos.com/API/';
    const LIGHTSPEED_REGISTRATION_ENDPOINT = 'https://cloud.merchantos.com/oauth/register.php';
    const LIGHTSPEED_AUTHORIZATION_ENDPOINT = 'https://cloud.merchantos.com/oauth/authorize.php';
    const LIGHTSPEED_TOKEN_ENDPOINT = 'https://cloud.merchantos.com/oauth/access_token.php';
    const LS_FORMAT = '.json';

    /**
     * @var mixed
     */
    protected $accountId;

    /**
     * @var array
     */
    private $context = ['error' => false, 'apiCall' => ''];

    /**
     * @param array $options
     * @param array $collaborators
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        $this->accountId = $options['accountId'];
    }

    public function getBaseAuthorizationUrl()
    {
        return static::LIGHTSPEED_AUTHORIZATION_ENDPOINT;
    }

    /**
     * @param array $params
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return static::LIGHTSPEED_TOKEN_ENDPOINT;
    }

    public function getDefaultScopes()
    {
        return ['employee:all'];
    }

    /**
     * @param $grant
     * @param array $params
     */
    public function getAccessToken($grant = 'authorization_code', array $params = [])
    {
        if (isset($params['refresh_token'])) {
            throw new LightspeedProviderException('Lightspeed does not support token refreshing.');
        }

        return parent::getAccessToken($grant, $params);
    }

    /**
     * Exchanges a short-lived access token with a long-lived access-token.
     *
     * @param string $accessToken
     *
     * @return \League\OAuth2\Client\Token\AccessToken
     *
     * @throws LightspeedProviderException
     */
    public function getLongLivedAccessToken($accessToken)
    {
        $params = [
            'ls_exchange_token' => (string) $accessToken,
        ];

        return $this->getAccessToken('ls_exchange_token', $params);
    }

    /**
     * @param AccessToken $token
     */
    public function getAccountId(AccessToken $token)
    {
        $params = ['oauth_token' => $token->getToken()];
        $url = $this->prepareApiUrl('Account', $this->accountId, null, $params);
        $request = $this->getAuthenticatedRequest(self::METHOD_GET, $url, $token);

        $response = $this->getResponse($request);

        if (isset($response['Account']) && $response['Account']['accountID']) {
            return (int) $response['Account']['accountID'];
        }

        if (isset($response['httpCode']) && $response['httpCode'] != '200') {
            throw new IdentityProviderException($response['message'], $response['httpCode'], $response);
        }

    }

    /**
     * @param AccessToken $token
     * @param $saleId
     * @return mixed
     */
    public function getSale(AccessToken $token, $saleId)
    {
        $apiResource = 'Account.Sale';
        $this->context['apiCall'] = $apiResource;

        $params = ['oauth_token' => $token->getToken()];
        $url = $this->prepareApiUrl($apiResource, $this->accountId, $saleId, $params);
        $request = $this->getAuthenticatedRequest(self::METHOD_GET, $url, $token);
        $response = $this->getResponse($request);

        $this->checkApiResponse($response);

        if (isset($response['Sale']) && $this->itemsCount($response) > 0) {
            return $response['Sale'];
        }

        return [];
    }

    /**
     * @param AccessToken $token
     * @return mixed
     */
    public function getShops(AccessToken $token)
    {
        $apiResource = 'Account.Shop';
        $this->context['apiCall'] = $apiResource;

        $params = ['oauth_token' => $token->getToken()];

        //get url
        $url = $this->prepareApiUrl($apiResource, $this->accountId, null, $params);
        //make API call
        $request = $this->getAuthenticatedRequest(self::METHOD_GET, $url, $token);
        //get response
        $response = $this->getResponse($request);

        $this->checkApiResponse($response);

        //validate the response
        if (isset($response['Shop']) && $this->itemsCount($response) == 1) {
            return [$response['Shop']];
        } elseif (isset($response['Shop']) && $this->itemsCount($response) > 1) {
            return $response['Shop'];
        }

        return [];
    }

    /**
     * @param AccessToken $token
     * @return mixed
     */
    public function getCustomer(AccessToken $token, $customerId)
    {
        $apiResource = 'Account.Customer';
        $this->context['apiCall'] = $apiResource;

        $params = array(
            'oauth_token' => $token->getToken(),
            'archived' => 0,
            'limit' => '50',
            'load_relations' => 'all',
            'customerID' => $customerId,
        );

        //get url
        $url = $this->prepareApiUrl($apiResource, $this->accountId, null, $params);
        //make API call
        $request = $this->getAuthenticatedRequest(self::METHOD_GET, $url, $token);
        //get response
        $response = $this->getResponse($request);

        $this->checkApiResponse($response);

        //validate the response
        if (isset($response['Customer']) && $this->itemsCount($response) == 1) {
            return $response['Customer'];
        } elseif (isset($response['Customer']) && $this->itemsCount($response) > 1) {
            return $response['Customer'];
        }

        return [];
    }

    /**
     * @param $controlName
     * @param $accountId
     * @param $uniqueId
     * @param $queryStr
     * @return string
     */
    private function prepareApiUrl($controlName, $accountId, $uniqueId = null, $queryStr = null)
    {
        $controlUrl = $this->getBaseLightspeedApiUrl() . str_replace('.', '/', str_replace('Account.', 'Account.' . $accountId . '.', $controlName));

        if ($uniqueId) {
            $controlUrl .= '/' . $uniqueId;
        }
        if ($queryStr && is_array($queryStr)) {
            $_queryStr = $this->buildQueryString($queryStr);

            $controlUrl .= self::LS_FORMAT . '?' . $_queryStr;
        } else {
            $controlUrl .= self::LS_FORMAT;
        }

        return $controlUrl;
    }

    /**
     * @param array $data
     * @return string
     */
    private function buildQueryString($data)
    {
        if (function_exists('http_build_query')) {
            return http_build_query($data);
        } else {
            $qs = '';
            foreach ($data as $key => $value) {
                $append = urlencode($key) . '=' . urlencode($value);
                $qs .= $qs ? '&' . $append : $append;
            }
            return $qs;
        }
    }

    /**
     * @param $response
     */
    private function checkApiResponse($response)
    {
        if (empty($this->accountId)) {
            $message = 'The "accountId" not set. In order to query Shop endpoint an accountId is required.';
            throw new \Exception($message);
        }

        // must be an error
        if (isset($response['httpCode']) && $response['httpCode'] != '200') {
            $message = $response['httpMessage'] . ': ' . $response['message'] . ' (' . $response['errorClass'] . ')';
            throw new IdentityProviderException($message, $response['httpCode'], $response);
        }
    }

    /**
     * @param $response
     * @return int
     */
    private function itemsCount($response)
    {
        $attributes = '@attributes';

        if (isset($response[$attributes])) {
            return $response[$attributes]['count'];
        }

        return 0;
    }

    /**
     * @param AccessToken $token
     * @return mixed
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getBaseLightspeedApiUrl() . '/Account/' . $this->accountId . '/Item?oauth_token=' . $token;
    }

    /**
     * @param array $response
     * @param AccessToken $token
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new LightspeedUser($response);
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
    private function getBaseLightspeedApiUrl()
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
