<?php

namespace PayLane;

use PayLane\Exception\ApiHttpCallException;
use PayLane\Exception\ApiServerConnectionException;
use PayLane\Exception\ExtensionMissingException;

/**
 * Client library for Paylane REST Server.
 * More info at http://devzone.paylane.com
 */
class RestClient
{

    const DEFAULT_API_URL = 'https://direct.paylane.com/rest/';

    /**
     * @var
     */
    private $apiUrl;

    /**
     * @var null|string
     */
    private $username = null;

    /**
     * @var null|string
     */
    private $password = null;

    /**
     * @var array
     */
    private $httpErrors = array(
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Timeout',
    );

    /**
     * @var bool
     */
    private $isSuccess = false;

    /**
     * @var array
     */
    private $allowedRequestMethods = array('GET', 'PUT', 'POST', 'DELETE');

    /**
     * @var boolean
     */
    private $sslVerify = true;

    /**
     * @param $username
     * @param $password
     * @param string $apiUrl
     * @throws ExtensionMissingException
     */
    public function __construct($username, $password, $apiUrl = self::DEFAULT_API_URL)
    {
        $this->username = $username;
        $this->password = $password;
        $this->apiUrl = $apiUrl;

        if (false === extension_loaded('curl')) {
            throw new ExtensionMissingException('The curl extension must be loaded for using this class!');
        }

        if (false === extension_loaded('json')) {
            throw new ExtensionMissingException('The json extension must be loaded for using this class!');
        }
    }

    /**
     * @param $sslVerify
     */
    public function setSslVerify($sslVerify)
    {
        $this->sslVerify = $sslVerify;
    }
    
    /**
     * Request state getter
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->isSuccess;
    }

    /**
     * Performs card sale
     *
     * @param array $params Sale Params
     * @return array
     */
    public function cardSale($params)
    {
        return $this->call(
            'cards/sale',
            'POST',
             $params
        );
    }

    /**
     * Performs card sale by token
     *
     * @param array $params Sale Params
     * @return array
     */
    public function cardSaleByToken($params)
    {
        return $this->call(
            'cards/saleByToken',
            'POST',
             $params
        );
    }

    /**
     * Card authorization
     *
     * @param array $params Authorization params
     * @return array
     */
    public function cardAuthorization($params)
    {
        return $this->call(
            'cards/authorization',
            'POST',
            $params
        );
    }

    /**
     * Card authorization by token
     *
     * @param array $params Authorization params
     * @return array
     */
    public function cardAuthorizationByToken($params)
    {
        return $this->call(
            'cards/authorizationByToken',
            'POST',
            $params
        );
    }
    
    /**
     * PayPal authorization
     *
     * @param $params
     * @return array
     */
    public function paypalAuthorization($params)
    {
        return $this->call(
            'paypal/authorization',
            'POST',
            $params
        );
    }

    /**
     * Performs capture from authorized card
     *
     * @param array $params Capture authorization params
     * @return array
     */
    public function captureAuthorization($params)
    {
        return $this->call(
            'authorizations/capture',
            'POST',
            $params
        );
    }

    /**
     * Performs closing of card authorization, basing on authorization card ID
     *
     * @param array $params Close authorization params
     * @return array
     */
    public function closeAuthorization($params)
    {
        return $this->call(
            'authorizations/close',
            'POST',
            $params
        );
    }

    /**
     * Performs refund
     *
     * @param array $params Refund params
     * @return array
     */
    public function refund($params)
    {
        return $this->call(
            'refunds',
            'POST',
            $params
        );
    }

    /**
     * Get sale info
     *
     * @param array $params Get sale info params
     * @return array
     */
    public function getSaleInfo($params)
    {
        return $this->call(
            'sales/info',
            'GET',
            $params
        );
    }
    
    /**
     * Get sale authorization info
     *
     * @param array $params Get sale authorization info params
     * @return array
     */
    public function getAuthorizationInfo($params)
    {
        return $this->call(
            'authorizations/info',
            'GET',
            $params
        );
    }

    /**
     * Performs sale status check
     *
     * @param array $params Check sale status
     * @return array
     */
    public function checkSaleStatus($params)
    {
        return $this->call(
            'sales/status',
            'GET',
            $params
        );
    }

    /**
     * Direct debit sale
     *
     * @param array $params Direct debit params
     * @return array
     */
    public function directDebitSale($params)
    {
        return $this->call(
            'directdebits/sale',
            'POST',
            $params
        );
    }

    /**
     * Sofort sale
     *
     * @param array $params Sofort params
     * @return array
     */
    public function sofortSale($params)
    {
        return $this->call(
            'sofort/sale',
            'POST',
            $params
        );
    }

    /**
     * iDeal sale
     *
     * @param $params iDeal transaction params
     * @return array
     */
    public function idealSale($params)
    {
        return $this->call(
            'ideal/sale',
            'post',
            (array) $params
        );
    }

    /**
     * iDeal banks list
     *
     * @return array
     */
	public function idealBankCodes()
    {
        return $this->call(
            'ideal/bankcodes',
            'GET',
            array()
        );
    }

    /**
     * Bank transfer sale
     *
     * @param array $params Bank transfer sale params
     * @return array
     */
    public function bankTransferSale($params)
    {
        return $this->call(
            'banktransfers/sale',
            'post',
            $params
        );
    }
    
    /**
     * PayPal sale
     *
     * @param array $params Paypal sale params
     * @return array
     */
    public function paypalSale($params)
    {
        return $this->call(
            'paypal/sale',
            'post',
            $params
        );
    }

    /**
     * Cancels Paypal recurring profile
     *
     * @param array $params Paypal params
     * @return array
     */
    public function paypalStopRecurring($params)
    {
        return $this->call('paypal/stopRecurring',
            'post',
            $params
        );
    }

    /**
     *  Performs resale by sale ID
     *
     * @param array $params Resale by sale params
     * @return array
     */
    public function resaleBySale($params)
    {
        return $this->call(
            'resales/sale',
            'post',
            $params
        );
    }

    /**
     * Performs resale by authorization ID
     *
     * @param array $params Resale by authorization params
     * @return array
     */
    public function resaleByAuthorization($params)
    {
        return $this->call(
            'resales/authorization',
            'post',
            $params
        );
    }

    /**
     * Checks if a card is enrolled in the 3D-Secure program.
     *
     * @param array $params Is card 3d secure params
     * @return array
     */
    public function checkCard3DSecure($params)
    {
        return $this->call(
            '3DSecure/checkCard',
            'GET',
            $params
        );
    }

    /**
     * Checks if a card is enrolled in the 3D-Secure program, based on the card's token.
     *
     * @param array $params Is card 3d secure params
     * @return array
     */
    public function checkCard3DSecureByToken($params)
    {
        return $this->call(
            '3DSecure/checkCardByToken',
            'GET',
            $params
        );
    }

    /**
     * Performs sale by ID 3d secure authorization
     *
     * @param array $params Sale by 3d secure authorization params
     * @return array
     */
    public function saleBy3DSecureAuthorization($params)
    {
        return $this->call(
            '3DSecure/authSale',
            'post',
            $params
        );
    }
    
    /**
     * Perform check card
     *
     * @param array $params Check card params
     * @return array
     */
    public function checkCard($params)
    {
        return $this->call(
            'cards/check',
            'GET',
            $params
        );
    }
    
    /**
     * Perform check card by token
     *
     * @param array $params Check card params
     * @return array
     */
    public function checkCardByToken($params)
    {
        return $this->call(
            'cards/checkByToken',
            'GET',
            $params
        );
    }

    /**
     * @param $url
     * @param $requestMethod
     * @param array $params
     * @return mixed
     * @throws ApiHttpCallException
     * @throws ApiServerConnectionException
     */
    protected function call($url, $requestMethod, array $params)
    {
        $this->isSuccess = false;

        $this->checkRequestMethod($requestMethod);
        
        $response = $this->pushData($url, $requestMethod, json_encode($params));
        $response = json_decode($response, true);

        if (isset($response['success']) && $response['success']) {
            $this->isSuccess = true;
        }

        return $response;
    }

    /**
     * @param $requestMethod
     * @return bool
     */
    protected function checkRequestMethod($requestMethod)
    {
        return in_array(strtoupper($requestMethod), $this->allowedRequestMethods, true);
    }

    /**
     * @param $url
     * @param $requestMethod
     * @param $requestParams
     * @return mixed
     * @throws ApiHttpCallException
     * @throws ApiServerConnectionException
     */
    protected function pushData($url, $requestMethod, $requestParams)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestParams);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestMethod);
        curl_setopt($ch, CURLOPT_HTTPAUTH, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->sslVerify);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (isset($this->httpErrors[$httpCode])) {
            throw new ApiHttpCallException("API responded with an error: [{$httpCode}] $this->httpErrors[$httpCode]");
        }

        if (0 < curl_errno($ch)) {
            throw new ApiServerConnectionException("API Server at: {$this->apiUrl} seems to be away");
        }

        curl_close($ch);
        
        return $response;
    }
}