<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;

class MerchantOS extends Lightspeed
{
    const LS_FORMAT = '.json';

    /**
     * @var mixed
     */
    private $oauthToken;

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

    private $logMessage;

    private $itemsCount;

    private $connectTimeout = 0;

    public $allowSleep = false;

    public $debugMode = false;

    /**
     * Creates new MerchantOS
     *
     * @param AccessToken $token
     * @param mixed $accountId
     */
    public function __construct(AccessToken $token, protected $accountId)
    {
        $this->oauthToken = $token->getToken();
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

    public function getLogMessage()
    {
        return $this->logMessage;
    }

    public function getItemsCount()
    {
        return $this->itemsCount;
    }

    /**
     * @param $seconds
     */
    public function setConnectTimeout($seconds)
    {
        $this->connectTimeout = $seconds;
    }

    public function getConnectTimeout()
    {
        return $this->connectTimeout;
    }

    /**
     * @param $vendorId
     * @param array $params
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getVendor($vendorId, $params = [])
    {
        $response = $this->makeAPICall('Account.Vendor', 'GET', $vendorId, $params, null);

        //validate the response
        if (isset($response['Vendor']) && $this->itemsCount($response) == 1) {
            return $response['Vendor'];
        }

        return [];
    }

    /**
     * @param array $params
     * @return array
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getVendors($params = [])
    {
        $response = $this->makeAPICall('Account.Vendor', 'GET', null, $params, null);

        if (isset($response['Vendor'])) {
            if (isset($response['Vendor'][0])) {
                return $response['Vendor'];
            } else {
                return [$response['Vendor']];
            }
        }

        return [];
    }

    /**
     * @param array $params
     * @return array
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getTags($params = [])
    {
        $response = $this->makeAPICall('Account.Tag', 'GET', null, $params, null);

        if (isset($response['Tag'])) {
            if (isset($response['Tag'][0])) {
                return $response['Tag'];
            } else {
                return [$response['Tag']];
            }
        }

        return [];
    }

    /**
     * @param array $params
     * @return array
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCustomerTypes($params = [])
    {
        $response = $this->makeAPICall('Account.CustomerType', 'GET', null, $params, null);

        if (isset($response['CustomerType'])) {
            if (isset($response['CustomerType'][0])) {
                return $response['CustomerType'];
            } else {
                return [$response['CustomerType']];
            }
        }

        return [];
    }

    /**
     * @param array $params
     * @return array
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCategories($params = [])
    {
        $response = $this->makeAPICall('Account.Category', 'GET', null, $params, null);

        if (isset($response['Category'])) {
            if (isset($response['Category'][0])) {
                return $response['Category'];
            } else {
                return [$response['Category']];
            }
        }
        return [];
    }

    /**
     * @param $itemId
     * @param array $params
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @param array $params
     * @return array
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getItems($params = [])
    {
        $response = $this->makeAPICall('Account.Item', 'GET', null, $params, null);

        if (isset($response['Item'])) {
            if (isset($response['Item'][0])) {
                return $response['Item'];
            } else {
                return [$response['Item']];
            }
        }

        return [];
    }

    /**
     * @param $data
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @param int $saleId
     * @param array $params
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSale($saleId, $params = [])
    {
        if (empty($params)) {
            $params = [
                'load_relations' => '["SaleLines","SaleLines.Item","Customer","Customer.Contact"]',
                'orderby' => 'saleLineID',
                'orderby_desc' => 1,
            ];
        }

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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSales($params = [])
    {
        $response = $this->makeAPICall('Account.Sale', 'GET', null, $params, null);

        if (isset($response['Sale'])) {
            if (isset($response['Sale'][0])) {
                return $response['Sale'];
            } else {
                return [$response['Sale']];
            }
        }

        return [];
    }

    /**
     * @param int $saleId
     * @param array $params
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSaleSaleLines($saleId, $params = [])
    {
        $response = $this->makeAPICall('Account.Sale' . '/' . $saleId . '/SaleLine', 'GET', null, $params, null);

        if (isset($response['SaleLine'])) {
            if (isset($response['SaleLine'][0])) {
                return $response['SaleLine'];
            } else {
                return [$response['SaleLine']];
            }
        }

        return [];
    }

    /**
     * @param array $params
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSaleLines($params = [])
    {
        $response = $this->makeAPICall('Account.SaleLine', 'GET', null, $params, null);

        if (isset($response['SaleLine'])) {
            if (isset($response['SaleLine'][0])) {
                return $response['SaleLine'];
            } else {
                return [$response['SaleLine']];
            }
        }
        return [];
    }

    /**
     * @param $saleLineId
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @param array $params
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getShops($params = [])
    {
        $response = $this->makeAPICall('Account.Shop', 'GET', null, $params, null);

        //validate the response
        if (isset($response['Shop'])) {
            if (isset($response['Shop'][0])) {
                return $response['Shop'];
            } else {
                return [$response['Shop']];
            }
        }

        return [];
    }

    /**
     * @param $shopId
     * @param array $params
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @param array $params
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCustomer($customerId, $params = [])
    {
        if (empty($params)) {
            $params = [
                'load_relations' => '["Contact","Tags","CustomerType"]',
                'archived' => 0,
            ];
        }

        $response = $this->makeAPICall('Account.Customer', 'GET', $customerId, $params, null);

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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCustomers($params)
    {
        $response = $this->makeAPICall('Account.Customer', 'GET', null, $params, null);

        //validate the response
        if (isset($response['Customer'])) {
            if (isset($response['Customer'][0])) {
                return $response['Customer'];
            } else {
                return [$response['Customer']];
            }
        }

        return [];
    }

    /**
     * @param array $data
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createCustomer($data)
    {
        $response = $this->makeAPICall('Account.Customer', 'POST', null, [], $data);

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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCustomFields($params)
    {
        $response = $this->makeAPICall('Account.Customer/CustomField', 'GET', null, $params, null);

        //validate the response
        if (isset($response['CustomField'])) {
            if (isset($response['CustomField'][0])) {
                return $response['CustomField'];
            } else {
                return [$response['CustomField']];
            }
        }

        return [];
    }

    /**
     * @param $customFieldId
     * @param array $params
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCustomField($customFieldId, $params = [])
    {
        if (empty($params)) {
            $params = [
                'customFieldID' => $customFieldId,
                'archived' => 0,
                'limit' => '1',
            ];
        }

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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @param integer $customFieldId
     * @param array $params
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAllCustomFieldChoices($customFieldId, $params = [])
    {
        $response = $this->makeAPICall('Account.Customer/CustomField/'
            . $customFieldId . '/CustomFieldChoice', 'GET', null, $params, null);

        //validate the response
        if (isset($response['CustomFieldChoice'])) {
            if (isset($response['CustomFieldChoice'][0])) {
                return $response['CustomFieldChoice'];
            } else {
                return [$response['CustomFieldChoice']];
            }
        }

        return [];
    }

    /**
     * @param integer $customFieldId
     * @param integer $customFieldChoiceID
     * @param array $params
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCustomFieldChoice($customFieldId, $customFieldChoiceID, $params = [])
    {
        $response = $this->makeAPICall('Account.Customer/CustomField/'
            . $customFieldId . '/CustomFieldChoice', 'GET', $customFieldChoiceID, $params, null);

        //validate the response
        if (isset($response['CustomFieldChoice']) && $this->itemsCount($response) == 1) {
            return $response['CustomFieldChoice'];
        } elseif (isset($response['CustomFieldChoice']) && $this->itemsCount($response) > 1) {
            return $response['CustomFieldChoice'];
        }

        return [];
    }

    /**
     * @param integer $customFieldId
     * @param array $data
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createCustomFieldChoice($customFieldId, $data)
    {
        $params = [];
        $response = $this->makeAPICall('Account.Customer/CustomField/'
            . $customFieldId . '/CustomFieldChoice', 'POST', null, $params, $data);

        //validate the response
        if (isset($response['CustomFieldChoice']) && $this->itemsCount($response) == 1) {
            return $response['CustomFieldChoice'];
        } elseif (isset($response['CustomFieldChoice']) && $this->itemsCount($response) > 1) {
            return $response['CustomFieldChoice'];
        }

        return [];
    }

    /**
     * @param array $data
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @param $customFieldId
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @param array $params
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getEmployee($employeeId, $params = [])
    {
        if (empty($params)) {
            $params = [
                'load_relations' => '["Contact","EmployeeRole"]',
                'employeeID' => $employeeId,
                'archived' => 0,
                'limit' => '1',
            ];
        }

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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getEmployees($params = [])
    {
        $response = $this->makeAPICall('Account.Employee', 'GET', null, $params, null);

        //validate the response
        if (isset($response['Employee'])) {
            if (isset($response['Employee'][0])) {
                return $response['Employee'];
            } else {
                return [$response['Employee']];
            }
        }

        return [];
    }

    /**
     * @param array $params
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDiscounts($params = [])
    {
        $response = $this->makeAPICall('Account.Discount', 'GET', null, $params, null);

        if (isset($response['Discount'])) {
            if (isset($response['Discount'][0])) {
                return $response['Discount'];
            } else {
                return [$response['Discount']];
            }
        }
        return [];
    }

    /**
     * @param $discountId
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getConfig()
    {
        $response = $this->makeAPICall('Account.Config', 'GET', null, [], []);

        return $response['Config'] ?? [];
    }

    /**
     * @return mixed
     * @throws IdentityProviderException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getOptions()
    {
        $response = $this->makeAPICall('Account.Option', 'GET', null, [], []);

        return $response;
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws IdentityProviderException
     */
    public function getLocale()
    {
        $response = $this->makeAPICall('Locale', 'GET', null, [], []);

        return $response['Locale'] ?? [];
    }

    /**
     * @param $controlUrl
     * @param $action
     * @param $uniqueId
     * @param $params
     * @param $data
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws IdentityProviderException
     */
    public function makeAPICall($controlUrl, $action, $uniqueId, $params, $data)
    {
        if (is_null($data) || $data == '') {
            $data = [];
        }

        $url = $this->prepareApiUrl($controlUrl, $this->accountId, $uniqueId, $params);

        $this->context['apiCall'] = $url;
        $this->context['action'] = strtoupper((string) $action);

        $headers = [
            'User-Agent' => $this->userAgent,
            'Accept' => 'application/vnd.merchantos-v2+json',
            'Authorization' => 'Bearer ' . $this->oauthToken,
        ];

        $this->sleepIfNecessary();

        $client = new \GuzzleHttp\Client();

        if ($this->connectTimeout > 0) {
            $response = $client->request($action, $url, [
                'headers' => $headers,
                'json' => $data,
                'connect_timeout' => $this->connectTimeout,
            ]);
        } else {
            $response = $client->request($action, $url, ['headers' => $headers, 'json' => $data]);
        }

        //set response headers
        $this->requestHeaders = $response->getHeaders();

        if ($this->debugMode) {
            $bucketLevel = $this->requestHeaders['X-LS-API-Bucket-Level'][0] ?? '';
            $logMessage = ' Account=' . $this->accountId;
            $logMessage .= ' X-LS-API-Bucket=' . $bucketLevel;
            $logMessage .= ' Req=' . $this->context['action'] . ' ' . $this->context['apiCall'];
            $this->logMessage = $logMessage;
        }

        $body = (string) $response->getBody();
        $r = json_decode($body, true);

        if (isset($r['@attributes'])) {
            $this->itemsCount = $r['@attributes']['count'];
        }

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
                $append = urlencode($key) . '=' . urlencode((string) $value);
                $qs .= $qs ? '&' . $append : $append;
            }
            return $qs;
        }
    }

    /**
     * @param $response
     * @throws IdentityProviderException
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
     * @return void
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

        [$currentLevel, $bucketSize] = explode('/', (string) $bucketLevelStr);

        //The drip rate is calculated by dividing the bucket size by 60 seconds.
        $dripRate = $bucketSize / 60;

        //remaining units until the limit is reached
        $available = $bucketSize - $currentLevel;

        //get the units needed for the next request
        $units = $this->getMethodUnits();

        if ($units >= $available) {
            //if not enough - sleep for a while
            $neededUnits = $units - $available;
            $sleepTime = ceil($neededUnits / $dripRate);

            if ($this->debugMode) {
                $logMessage = 'Too many requests Account=' . $this->accountId;
                $logMessage .= ' X-LS-API-Bucket=' . $bucketLevelStr;
                $logMessage .= ' Units Next Request=' . $units;
                $logMessage .= ' Sleeping=' . $sleepTime . 'sec';
                $logMessage .= ' Req=' . $this->context['action'] . ' ' . $this->context['apiCall'];

                $this->logMessage = $logMessage;
                // error_log($logMessage);
            }

            sleep($sleepTime);
        }
    }

    /**
     * @return int
     */
    private function getMethodUnits()
    {
        if ($this->context['action'] == 'GET') {
            return 1;
        } elseif (in_array($this->context['action'], ['POST', 'PUT', 'DELETE'])) {
            return 10;
        }
        return 1;
    }
}
