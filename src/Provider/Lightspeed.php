<?php

namespace League\OAuth2\Client\Provider;

use GuzzleHttp\Client;
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
     * @var array
     */
    private $context = ['error' => false, 'apiCall' => ''];

    /**
     * @var mixed
     */
    private $oauthToken;

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
        $account = $this->getResourceOwner($token);

        return $account->getId();
    }

    /**
     * @param AccessToken $token
     * @param $saleId
     * @return mixed
     */
    public function getSale(AccessToken $token, $saleId)
    {
        $this->oauthToken = $token;
        $params = ['oauth_token' => $token->getToken()];
        $response = $this->makeAPICall('Account.Sale', 'GET', $saleId, $params, null);

        if (isset($response['Sale']) && $this->itemsCount($response) > 0) {
            return $response['Sale'];
        }

        return [];
    }

    /**
     * @param AccessToken $token
     * @param int $saleId
     * @param array $saleData
     * @return mixed
     */
    public function updateSale(AccessToken $token, $saleId, $saleData)
    {
        $this->oauthToken = $token;
        $params = ['oauth_token' => $token->getToken()];
        $response = $this->makeAPICall('Account.Sale', 'PUT', $saleId, $params, $saleData);

        if (isset($response['Sale']) && $this->itemsCount($response) > 0) {
            return $response['Sale'];
        }

        return [];
    }

    /**
     * @param AccessToken $token
     * @param $saleId
     * @return mixed
     */
    public function getSaleLine(AccessToken $token, $saleId)
    {
        $this->oauthToken = $token;
        $params = ['oauth_token' => $token->getToken(), 'limit' => 1];

        //return $this->prepareApiUrl('Account.Sale'.'/'.$saleId.'/SaleLine', '125620', null, $params);
        $response = $this->makeAPICall('Account.Sale' . '/' . $saleId . '/SaleLine', 'GET', null, $params, null);

        if (isset($response['SaleLine']) && $this->itemsCount($response) > 0) {
            return $response['SaleLine'];
        }

        return [];
    }

    /**
     * @param AccessToken $token
     * @param $saleId
     * @return mixed
     */
    public function updateSaleLine(AccessToken $token, $saleId, $saleLineId, $data)
    {
        $this->oauthToken = $token;
        $params = ['oauth_token' => $token->getToken()];

        //return $this->prepareApiUrl('Account.Sale'.'/'.$saleId.'/SaleLine', '125620', null, $params);
        $control = 'Account.Sale' . '/' . $saleId . '/SaleLine' . '/' . $saleLineId;
        $response = $this->makeAPICall($control, 'PUT', null, $params, $data);

        if (isset($response['SaleLine']) && $this->itemsCount($response) > 0) {
            return $response['SaleLine'];
        }

        return [];
    }

    /**
     * @param AccessToken $token
     * @param $data
     * @return mixed
     */
    public function createSaleLine(AccessToken $token, $data)
    {
        $this->oauthToken = $token;
        $params = ['oauth_token' => $token->getToken()];

        $response = $this->makeAPICall('Account.Sale/49/SaleLine', 'POST', null, $params, $data);

        if (isset($response['SaleLine']) && $this->itemsCount($response) > 0) {
            return $response['SaleLine'];
        }

        return [];
    }

    /**
     * @param AccessToken $token
     * @return mixed
     */
    public function getShops(AccessToken $token)
    {
        $this->oauthToken = $token;
        $params = ['oauth_token' => $token->getToken()];
        $response = $this->makeAPICall('Account.Shop', 'GET', null, $params, null);

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
     * @param int $customerId
     * @return mixed
     */
    public function getCustomer(AccessToken $token, $customerId)
    {
        $this->oauthToken = $token;
        $params = array(
            'oauth_token' => $token->getToken(),
            'archived' => 0,
            'limit' => '1',
            'load_relations' => 'all',
            'customerID' => $customerId,
        );

        $response = $this->makeAPICall('Account.Customer', 'GET', null, $params, null);

        //validate the response
        if (isset($response['Customer']) && $this->itemsCount($response) == 1) {
            return $response['Customer'];
        } elseif (isset($response['Customer']) && $this->itemsCount($response) > 1) {
            return $response['Customer'];
        }

        return [];
    }

    /**
     * @param AccessToken $token
     * @param int $employeeId
     * @return mixed
     */
    public function getEmployee(AccessToken $token, $employeeId)
    {
        $this->oauthToken = $token;
        $params = array(
            'oauth_token' => $token->getToken(),
            'archived' => 0,
            'limit' => '1',
            'load_relations' => 'all',
            'employeeID' => $employeeId,
        );

        $response = $this->makeAPICall('Account.Employee', 'GET', $employeeId, $params, null);

        //validate the response
        if (isset($response['Employee']) && $this->itemsCount($response) > 0) {
            return $response['Employee'];
        }

        return [];
    }

    /**
     * @param AccessToken $token
     * @param $discountId
     * @return mixed
     */
    public function getDiscount(AccessToken $token, $discountId = null)
    {
        $this->oauthToken = $token;
        $params = ['oauth_token' => $token->getToken()];
        $response = $this->makeAPICall('Account.Discount', 'GET', $discountId, $params, null);

        if (isset($response['Discount']) && $this->itemsCount($response) > 0) {
            return $response['Discount'];
        }

        return [];
    }

    /**
     * @param AccessToken $token
     * @param $data
     * @return mixed
     */
    public function createDiscount(AccessToken $token, $data)
    {
        $this->oauthToken = $token;
        $params = ['oauth_token' => $token->getToken()];

        $response = $this->makeAPICall('Account.Discount', 'POST', null, $params, $data);

        if (isset($response['Discount']) && $this->itemsCount($response) > 0) {
            return $response['Discount'];
        }

        return [];
    }

    /**
     * @param $controlUrl
     * @param $action
     * @param $uniqueId
     * @param $params
     * @param $data
     * @return mixed
     */
    public function makeAPICall($controlUrl, $action, $uniqueId, $params, $data)
    {
        $this->context['apiCall'] = $controlUrl;

        if (is_null($data) || $data == '') {
            $data = [];
        }

        $account = $this->getResourceOwner($this->oauthToken);

        $url = $this->prepareApiUrl($controlUrl, $account->getId(), $uniqueId, $params);

        $client = new \GuzzleHttp\Client();
        $response = $client->request($action, $url, ['json' => $data]);

        $body = (string) $response->getBody()->read(3024);
        $r = json_decode($body, true);

        $this->checkApiResponse($r);
        return $r;
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
        $controlUrl = $this->getBaseLightspeedApiUrl();
        $controlUrl .= str_replace('.', '/', str_replace('Account.', 'Account.' . $accountId . '.', $controlName));

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
        return $this->getBaseLightspeedApiUrl() . 'Account/.json?oauth_token=' . $token;
    }

    /**
     * @param array $response
     * @param AccessToken $token
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new LightspeedResourceOwner($response);
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
