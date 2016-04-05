<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Token\AccessToken;

class MerchantOS extends Lightspeed
{
    const LS_FORMAT = '.json';

    /**
     * @var mixed
     */
    private $oauthToken;
    /**
     * @var mixed
     */
    private $accountId;

    /**
     * @var array
     */
    private $context = ['error' => false, 'apiCall' => ''];

    /**
     * Creates new MerchantOS
     *
     * @param AccessToken $token
     * @param mixed $accountId
     */
    public function __construct(AccessToken $token, $accountId)
    {
        $this->oauthToken = $token->getToken();
        $this->accountId = $accountId;
    }

    /**
     * @param $saleId
     * @return mixed
     */
    public function getSale($saleId)
    {
        $params = ['oauth_token' => $this->oauthToken, 'load_relations' => 'all', 'orderby' => 'saleLineID', 'orderby_desc' => 1];
        $response = $this->makeAPICall('Account.Sale', 'GET', $saleId, $params, null);

        if (isset($response['Sale']) && $this->itemsCount($response) > 0) {
            return $response['Sale'];
        }

        return [];
    }

    /**
     * @param int $saleId
     * @param array $saleData
     * @return mixed
     */
    public function updateSale($saleId, $saleData)
    {
        $params = ['oauth_token' => $this->oauthToken];
        $response = $this->makeAPICall('Account.Sale', 'PUT', $saleId, $params, $saleData);

        if (isset($response['Sale']) && $this->itemsCount($response) > 0) {
            return $response['Sale'];
        }

        return [];
    }

    /**
     * @param int $saleId
     * @param int $limit
     * @return mixed
     */
    public function getSaleLine($saleId, $extra = [])
    {
        $params = ['oauth_token' => $this->oauthToken];

        $params = array_merge($params, $extra);
        $response = $this->makeAPICall('Account.Sale' . '/' . $saleId . '/SaleLine', 'GET', null, $params, null);

        if (isset($response['SaleLine']) && $this->itemsCount($response) == 1) {
            return [$response['SaleLine']];
        } elseif (isset($response['SaleLine']) && $this->itemsCount($response) > 0) {
            return $response['SaleLine'];
        }

        return [];
    }

    /**
     * @param $saleId
     * @return mixed
     */
    public function updateSaleLine($saleId, $saleLineId, $data)
    {
        $params = ['oauth_token' => $this->oauthToken];

        $control = 'Account.Sale' . '/' . $saleId . '/SaleLine' . '/' . $saleLineId;
        $response = $this->makeAPICall($control, 'PUT', null, $params, $data);

        if (isset($response['SaleLine']) && $this->itemsCount($response) > 0) {
            return $response['SaleLine'];
        }

        return [];
    }

    /**
     * @param $saleId
     * @param $data
     * @return mixed
     */
    public function createSaleLine($saleId, $data)
    {
        $params = ['oauth_token' => $this->oauthToken];

        $response = $this->makeAPICall('Account.Sale' . '/' . $saleId . '/SaleLine', 'POST', null, $params, $data);

        if (isset($response['SaleLine']) && $this->itemsCount($response) > 0) {
            return $response['SaleLine'];
        }

        return [];
    }

    /**
     * @return mixed
     */
    public function getShops()
    {
        $params = ['oauth_token' => $this->oauthToken];
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
     * @param int $customerId
     * @return mixed
     */
    public function getCustomer($customerId)
    {
        $params = array(
            'oauth_token' => $this->oauthToken,
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
     * @param int $customerId
     * @param array $data
     * @return mixed
     */
    public function updateCustomer($customerId, $data)
    {
        $params = array(
            'oauth_token' => $this->oauthToken,
        );

        $response = $this->makeAPICall('Account.Customer', 'PUT', $customerId, $params, $data);

        //validate the response
        if (isset($response['Customer']) && $this->itemsCount($response) == 1) {
            return $response['Customer'];
        } elseif (isset($response['Customer']) && $this->itemsCount($response) > 1) {
            return $response['Customer'];
        }

        return [];
    }

    /**
     * @return mixed
     */
    public function getCustomField($customFieldId)
    {
        $params = array(
            'oauth_token' => $this->oauthToken,
            'archived' => 0,
            'limit' => '1',
            'load_relations' => 'all',
            'customFieldID' => $customFieldId,
        );

        $response = $this->makeAPICall('Account.Customer/CustomField', 'GET', null, $params, null);

        //validate the response
        if (isset($response['CustomField']) && $this->itemsCount($response) == 1) {
            return $response['CustomField'];
        } elseif (isset($response['CustomField']) && $this->itemsCount($response) > 1) {
            return $response['CustomField'];
        }

        return [];
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createCustomField($data)
    {
        $params = array(
            'oauth_token' => $this->oauthToken,
        );

        $response = $this->makeAPICall('Account.Customer/CustomField', 'POST', null, $params, $data);

        //validate the response
        if (isset($response['CustomField']) && $this->itemsCount($response) == 1) {
            return $response['CustomField'];
        } elseif (isset($response['CustomField']) && $this->itemsCount($response) > 1) {
            return $response['CustomField'];
        }

        return [];
    }

    /**
     * @param int $employeeId
     * @return mixed
     */
    public function getEmployee($employeeId)
    {
        $params = array(
            'oauth_token' => $this->oauthToken,
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
     * @param $discountId
     * @return mixed
     */
    public function getDiscount($discountId = null)
    {
        $params = ['oauth_token' => $this->oauthToken];
        $response = $this->makeAPICall('Account.Discount', 'GET', $discountId, $params, null);

        if (isset($response['Discount']) && $this->itemsCount($response) > 0) {
            return $response['Discount'];
        }

        return [];
    }

    /**
     * @param $data
     * @return mixed
     */
    public function createDiscount($data)
    {
        $params = ['oauth_token' => $this->oauthToken];

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

        $url = $this->prepareApiUrl($controlUrl, $this->accountId, $uniqueId, $params);

        $client = new \GuzzleHttp\Client();
        $response = $client->request($action, $url, ['json' => $data]);

        $body = (string) $response->getBody();
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
}
