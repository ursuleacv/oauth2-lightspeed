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
    protected $accountId;

    /**
     * @var string
     */
    protected $userAgent = 'MerchantOS';

    /**
     * @var array
     */
    private $context = ['error' => false, 'apiCall' => '', 'action' => ''];

    /**
     * @var mixed
     */
    private $requestHeaders;

    public $allowSleep = false;

    public $debugMode = false;

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
     * @param $agent
     */
    public function setUserAgent($agent)
    {
        $this->userAgent = $agent;
    }

    public function getRequestHeaders()
    {
        return $this->requestHeaders;
    }

    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param $params
     * @return array
     */
    public function getTags($params = [])
    {
        $response = $this->makeAPICall('Account.Tag', 'GET', null, $params, null);

        if (isset($response['Tag']) && $this->itemsCount($response) == 1) {
            return [$response['Tag']];
        } elseif (isset($response['Tag']) && $this->itemsCount($response) > 1) {
            return $response['Tag'];
        }

        return [];
    }

    /**
     * @param $params
     * @return array
     */
    public function getCategories($params = [])
    {
        $response = $this->makeAPICall('Account.Category', 'GET', null, $params, null);

        if (isset($response['Category']) && $this->itemsCount($response) == 1) {
            return [$response['Category']];
        } elseif (isset($response['Category']) && $this->itemsCount($response) > 1) {
            return $response['Category'];
        }

        return [];
    }

    /**
     * @param $itemId
     * @param $params
     * @return mixed
     */
    public function getItem($itemId, $params = [])
    {
        $response = $this->makeAPICall('Account.Item', 'GET', $itemId, $params, null);

        if (isset($response['Item']) && $this->itemsCount($response) > 0) {
            return $response['Item'];
        }

        return [];
    }

    /**
     * @param $params
     * @return array
     */
    public function getItems($params = [])
    {
        $response = $this->makeAPICall('Account.Item', 'GET', null, $params, null);

        if (isset($response['Item']) && $this->itemsCount($response) == 1) {
            return [$response['Item']];
        } elseif (isset($response['Item']) && $this->itemsCount($response) > 1) {
            return $response['Item'];
        }

        return [];
    }

    /**
     * @param $itemId
     * @param $data
     * @return mixed
     */
    public function createItem($data)
    {
        $params = [];
        $response = $this->makeAPICall('Account.Item', 'POST', null, $params, $data);

        if (isset($response['Item']) && $this->itemsCount($response) > 0) {
            return $response['Item'];
        }

        return [];
    }

    /**
     * @param $itemId
     * @return mixed
     */
    public function deleteItem($itemId)
    {
        $params = [];
        $response = $this->makeAPICall('Account.Item', 'DELETE', $itemId, $params, null);

        if (isset($response['Item']) && $this->itemsCount($response) > 0) {
            return $response['Item'];
        }

        return [];
    }

    /**
     * @param $itemId
     * @param $data
     * @return mixed
     */
    public function updateItem($itemId, $data)
    {
        $params = [];
        $response = $this->makeAPICall('Account.Item', 'PUT', $itemId, $params, $data);

        if (isset($response['Item']) && $this->itemsCount($response) > 0) {
            return $response['Item'];
        }

        return [];
    }

    /**
     * @param $saleId
     * @return mixed
     */
    public function getSale($saleId)
    {
        $params = ['load_relations' => 'all', 'orderby' => 'saleLineID', 'orderby_desc' => 1];
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
        $params = [];
        $response = $this->makeAPICall('Account.Sale', 'PUT', $saleId, $params, $saleData);

        if (isset($response['Sale']) && $this->itemsCount($response) > 0) {
            return $response['Sale'];
        }

        return [];
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function getSales($params = [])
    {
        $response = $this->makeAPICall('Account.Sale', 'GET', null, $params, null);

        if (isset($response['Sale']) && $this->itemsCount($response) == 1) {
            return [$response['Sale']];
        } elseif (isset($response['Sale']) && $this->itemsCount($response) > 1) {
            return $response['Sale'];
        }

        return [];
    }

    /**
     * @param int $saleId
     * @param int $limit
     * @return mixed
     */
    public function getSaleSaleLines($saleId, $params = [])
    {
        $response = $this->makeAPICall('Account.Sale' . '/' . $saleId . '/SaleLine', 'GET', null, $params, null);

        if (isset($response['SaleLine']) && $this->itemsCount($response) == 1) {
            return [$response['SaleLine']];
        } elseif (isset($response['SaleLine']) && $this->itemsCount($response) > 1) {
            return $response['SaleLine'];
        }

        return [];
    }

    /**
     * @param int $saleId
     * @param int $limit
     * @return mixed
     */
    public function getSaleLine($saleLineId)
    {
        $params = [];
        $response = $this->makeAPICall('Account.SaleLine', 'GET', $saleLineId, $params, null);

        if (isset($response['SaleLine']) && $this->itemsCount($response) > 0) {
            return $response['SaleLine'];
        }

        return [];
    }

    /**
     * @param $saleId
     * @param $saleLineId
     * @param $data
     * @return mixed
     */
    public function updateSaleLine($saleId, $saleLineId, $data)
    {
        $params = [];

        $control = 'Account.Sale' . '/' . $saleId . '/SaleLine' . '/' . $saleLineId;
        $response = $this->makeAPICall($control, 'PUT', null, $params, $data);

        if (isset($response['SaleLine']) && $this->itemsCount($response) > 0) {
            return $response['SaleLine'];
        }

        return [];
    }

    /**
     * @param $saleId
     * @param $saleLineId
     * @return mixed
     */
    public function deleteSaleLine($saleId, $saleLineId)
    {
        $params = [];

        $control = 'Account.Sale' . '/' . $saleId . '/SaleLine' . '/' . $saleLineId;
        $response = $this->makeAPICall($control, 'DELETE', null, $params, null);

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
        $params = [];

        $response = $this->makeAPICall('Account.Sale' . '/' . $saleId . '/SaleLine', 'POST', null, $params, $data);

        if (isset($response['SaleLine']) && $this->itemsCount($response) > 0) {
            return $response['SaleLine'];
        }

        return [];
    }

    /**
     * @param $params
     * @return mixed
     */
    public function getShops($params = [])
    {
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
     * @param $shopId
     * @param $params
     * @return mixed
     */
    public function getShop($shopId, $params = [])
    {
        $response = $this->makeAPICall('Account.Shop', 'GET', $shopId, $params, null);

        //validate the response
        if (isset($response['Shop']) && $this->itemsCount($response) == 1) {
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
     * @param array $params
     * @return mixed
     */
    public function getCustomers($params)
    {
        $response = $this->makeAPICall('Account.Customer', 'GET', null, $params, null);

        //validate the response
        if (isset($response['Customer']) && $this->itemsCount($response) == 1) {
            return [$response['Customer']];
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
        $params = [];
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
     * @param array $params
     * @return mixed
     */
    public function getCustomFields($params)
    {
        $response = $this->makeAPICall('Account.Customer/CustomField', 'GET', null, $params, null);

        //validate the response
        if (isset($response['CustomField']) && $this->itemsCount($response) == 1) {
            return [$response['CustomField']];
        } elseif (isset($response['CustomField']) && $this->itemsCount($response) > 1) {
            return $response['CustomField'];
        }

        return [];
    }

    /**
     * @return mixed
     */
    public function getCustomField($customFieldId)
    {
        $params = array(
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
        $params = [];
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
     * @param array $data
     * @return mixed
     */
    public function createTaxClass($data)
    {
        $params = [];
        $response = $this->makeAPICall('Account.TaxClass', 'POST', null, $params, $data);

        //validate the response
        if (isset($response['TaxClass']) && $this->itemsCount($response) == 1) {
            return $response['TaxClass'];
        } elseif (isset($response['TaxClass']) && $this->itemsCount($response) > 1) {
            return $response['TaxClass'];
        }

        return [];
    }

    /**
     * @param int $taxClassId
     * @param array $params
     * @return mixed
     */
    public function getTaxClass($taxClassId, $params)
    {
        $response = $this->makeAPICall('Account.TaxClass', 'GET', $taxClassId, $params, $data = []);

        //validate the response
        if (isset($response['TaxClass']) && $this->itemsCount($response) == 1) {
            return $response['TaxClass'];
        } elseif (isset($response['TaxClass']) && $this->itemsCount($response) > 1) {
            return $response['TaxClass'];
        }

        return [];
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function deleteCustomField($customFieldId)
    {
        $params = [];
        $response = $this->makeAPICall('Account.Customer/CustomField', 'DELETE', $customFieldId, $params, null);

        //validate the response
        if (isset($response['CustomField']) && $this->itemsCount($response) == 1) {
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
     * @param array $params
     * @return mixed
     */
    public function getEmployees($params = [])
    {
        $response = $this->makeAPICall('Account.Employee', 'GET', null, $params, null);

        //validate the response
        if (isset($response['Employee']) && $this->itemsCount($response) == 1) {
            return [$response['Employee']];
        } elseif (isset($response['Employee']) && $this->itemsCount($response) > 0) {
            return $response['Employee'];
        }

        return [];
    }

    /**
     * @param $params
     * @return mixed
     */
    public function getDiscounts($params = [])
    {
        $response = $this->makeAPICall('Account.Discount', 'GET', null, $params, null);

        if (isset($response['Discount']) && $this->itemsCount($response) == 1) {
            return [$response['Discount']];
        } elseif (isset($response['Discount']) && $this->itemsCount($response) > 0) {
            return $response['Discount'];
        }

        return [];
    }

    /**
     * @param $discountId
     * @return mixed
     */
    public function getDiscount($discountId = null)
    {
        $params = [];
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
        $params = [];

        $response = $this->makeAPICall('Account.Discount', 'POST', null, $params, $data);

        if (isset($response['Discount']) && $this->itemsCount($response) > 0) {
            return $response['Discount'];
        }

        return [];
    }

    /**
     * @param $discountId
     * @param $data
     * @return mixed
     */
    public function updateDiscount($discountId, $data)
    {
        $params = [];
        $response = $this->makeAPICall('Account.Discount', 'PUT', $discountId, $params, $data);

        if (isset($response['Discount']) && $this->itemsCount($response) > 0) {
            return $response['Discount'];
        }

        return [];
    }

    /**
     * @param $discountId
     * @return mixed
     */
    public function deleteDiscount($discountId = null)
    {
        $params = [];
        $response = $this->makeAPICall('Account.Discount', 'DELETE', $discountId, $params, null);

        if (isset($response['Discount']) && $this->itemsCount($response) > 0) {
            return $response['Discount'];
        }

        return [];
    }

    /**
     * @param int $saleId
     * @param array $data
     * @return mixed
     */
    public function createSaleRefund($saleId, $data = [])
    {
        $response = $this->makeAPICall('Account.Sale' . '/' . $saleId . '/refund', 'POST', null, [], $data);

        if (isset($response['Sale']) && $this->itemsCount($response) > 0) {
            return $response['Sale'];
        }

        return [];
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function getCreditAccount($params = [])
    {
        $response = $this->makeAPICall('Account.CreditAccount', 'GET', null, $params, null);

        if (isset($response['CreditAccount']) && $this->itemsCount($response) > 0) {
            return $response['CreditAccount'];
        }

        return [];
    }

    /**
     * @param int $creditAccountId
     * @param array $data
     * @return mixed
     */
    public function createCreditAccount($creditAccountId, $data = [])
    {
        $response = $this->makeAPICall('Account.CreditAccount', 'PUT', $creditAccountId, [], $data);

        if (isset($response['CreditAccount']) && $this->itemsCount($response) > 0) {
            return $response['CreditAccount'];
        }

        return [];
    }

    /**
     * @return mixed
     */
    public function accounts()
    {
        $response = $this->makeAPICall('Account', 'GET', null, [], []);

        if (isset($response['Account']) && $this->itemsCount($response) > 0) {
            return $response['Account'];
        }

        return [];
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        $response = $this->makeAPICall('Account.Config', 'GET', null, [], []);

        if (isset($response['Config'])) {
            return $response['Config'];
        }

        return [];
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        $response = $this->makeAPICall('Account.Option', 'GET', null, [], []);

        return $response;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        $response = $this->makeAPICall('Locale', 'GET', null, [], []);

        if (isset($response['Locale'])) {
            return $response['Locale'];
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
        if (is_null($data) || $data == '') {
            $data = [];
        }

        $url = $this->prepareApiUrl($controlUrl, $this->accountId, $uniqueId, $params);

        $this->context['apiCall'] = $url;
        $this->context['action'] = strtoupper($action);

        $headers = [
            'User-Agent' => $this->userAgent,
            'Accept' => 'application/vnd.merchantos-v2+json',
            'Authorization' => 'Bearer ' . $this->oauthToken,
        ];

        $this->sleepIfNecessary();

        $client = new \GuzzleHttp\Client();
        $response = $client->request($action, $url, ['headers' => $headers, 'json' => $data]);

        //set response headers
        $this->requestHeaders = $response->getHeaders();

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
    protected function buildQueryString(array $data)
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
     * @param $headers
     * @return null
     */
    protected function sleepIfNecessary()
    {
        if (!$this->allowSleep) {
            return;
        }

        if (!$this->requestHeaders) {
            return;
        }

        $headers = $this->requestHeaders;

        if (!isset($headers['X-LS-API-Bucket-Level'][0])) {
            return;
        }

        $bucketLevelStr = $headers['X-LS-API-Bucket-Level'][0];

        list($currentLevel, $bucketSize) = explode('/', $bucketLevelStr);

        //The drip rate is calculated by dividing the bucket size by 60 seconds.
        $dripRate = $bucketSize / 60;

        //remaining units until the limit is reached
        $available = $bucketSize - $currentLevel;

        //get the units neded for the next request
        $units = $this->getMethodUnits();

        if ($units >= $available) {
            //if not enough - sleep for a while
            $neededUnits = $units - $available;
            $sleepTime = ceil($neededUnits / $dripRate);

            if ($debugMode) {
                $logMessage = 'Too many requests Account=' . $this->accountId;
                $logMessage .= ' X-LS-API-Bucket=' . $bucketLevelStr;
                $logMessage .= ' Units Next Request=' . $units;
                $logMessage .= ' Sleeping=' . $sleepTime . 'sec';
                $logMessage .= ' Req=' . $this->context['action'] .' ' . $this->context['apiCall'];
                error_log($logMessage);
            }

            sleep($sleepTime);
        }
    }

    private function getMethodUnits()
    {
        if ($this->context['action'] == 'GET') {
            return 1;
        } elseif (in_array($this->context['action'], ['POST', 'PUT', 'DELETE'])) {
            return 10;
        }
    }
}
