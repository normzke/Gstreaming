<?php
/**
 * M-PESA Integration Class
 * Complete M-PESA API integration for GStreaming
 */

class MpesaIntegration {
    private $consumerKey;
    private $consumerSecret;
    private $shortcode;
    private $passkey;
    private $callbackUrl;
    private $initiatorName;
    private $securityCredential;
    private $environment; // sandbox or production
    
    // API URLs
    const SANDBOX_BASE_URL = 'https://sandbox.safaricom.co.ke';
    const PRODUCTION_BASE_URL = 'https://api.safaricom.co.ke';
    
    public function __construct($config = []) {
        $this->consumerKey = $config['consumer_key'] ?? '';
        $this->consumerSecret = $config['consumer_secret'] ?? '';
        $this->shortcode = $config['shortcode'] ?? '';
        $this->passkey = $config['passkey'] ?? '';
        $this->callbackUrl = $config['callback_url'] ?? '';
        $this->initiatorName = $config['initiator_name'] ?? '';
        $this->securityCredential = $config['security_credential'] ?? '';
        $this->environment = $config['environment'] ?? 'sandbox';
    }
    
    /**
     * Get access token for M-PESA API
     */
    public function getAccessToken() {
        $url = $this->getBaseUrl() . '/oauth/v1/generate?grant_type=client_credentials';
        
        $headers = [
            'Authorization: Basic ' . base64_encode($this->consumerKey . ':' . $this->consumerSecret),
            'Content-Type: application/json'
        ];
        
        $response = $this->makeHttpRequest($url, 'GET', null, $headers);
        
        if ($response && isset($response['access_token'])) {
            return $response['access_token'];
        }
        
        throw new Exception('Failed to get M-PESA access token');
    }
    
    /**
     * Initiate STK Push for payment
     */
    public function initiateSTKPush($phoneNumber, $amount, $accountReference, $transactionDesc) {
        $accessToken = $this->getAccessToken();
        
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        
        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phoneNumber,
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => $this->callbackUrl,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc
        ];
        
        $url = $this->getBaseUrl() . '/mpesa/stkpush/v1/processrequest';
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $response = $this->makeHttpRequest($url, 'POST', json_encode($payload), $headers);
        
        return $response;
    }
    
    /**
     * Query STK Push status
     */
    public function querySTKPush($checkoutRequestId) {
        $accessToken = $this->getAccessToken();
        
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        
        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId
        ];
        
        $url = $this->getBaseUrl() . '/mpesa/stkpushquery/v1/query';
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $response = $this->makeHttpRequest($url, 'POST', json_encode($payload), $headers);
        
        return $response;
    }
    
    /**
     * Process B2C payment (for refunds)
     */
    public function initiateB2C($phoneNumber, $amount, $remarks, $occassion = '') {
        $accessToken = $this->getAccessToken();
        
        $payload = [
            'InitiatorName' => $this->initiatorName,
            'SecurityCredential' => $this->securityCredential,
            'CommandID' => 'BusinessPayment',
            'Amount' => $amount,
            'PartyA' => $this->shortcode,
            'PartyB' => $phoneNumber,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $this->callbackUrl,
            'ResultURL' => $this->callbackUrl,
            'Occasion' => $occassion
        ];
        
        $url = $this->getBaseUrl() . '/mpesa/b2c/v1/paymentrequest';
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $response = $this->makeHttpRequest($url, 'POST', json_encode($payload), $headers);
        
        return $response;
    }
    
    /**
     * Process C2B payment (customer to business)
     */
    public function initiateC2B($phoneNumber, $amount, $accountNumber, $billRefNumber) {
        $accessToken = $this->getAccessToken();
        
        $payload = [
            'ShortCode' => $this->shortcode,
            'CommandID' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'Msisdn' => $phoneNumber,
            'BillRefNumber' => $billRefNumber,
            'AccountNumber' => $accountNumber
        ];
        
        $url = $this->getBaseUrl() . '/mpesa/c2b/v1/simulate';
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $response = $this->makeHttpRequest($url, 'POST', json_encode($payload), $headers);
        
        return $response;
    }
    
    /**
     * Get base URL based on environment
     */
    private function getBaseUrl() {
        return $this->environment === 'production' ? self::PRODUCTION_BASE_URL : self::SANDBOX_BASE_URL;
    }
    
    /**
     * Make HTTP request
     */
    private function makeHttpRequest($url, $method = 'GET', $data = null, $headers = []) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception('HTTP Error: ' . $httpCode);
        }
        
        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON Decode Error: ' . json_last_error_msg());
        }
        
        return $decodedResponse;
    }
    
    /**
     * Validate M-PESA callback
     */
    public function validateCallback($callbackData) {
        // Validate the callback data from M-PESA
        if (!isset($callbackData['Body']['stkCallback'])) {
            return false;
        }
        
        $callback = $callbackData['Body']['stkCallback'];
        
        if (!isset($callback['ResultCode']) || $callback['ResultCode'] !== 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Extract payment details from callback
     */
    public function extractPaymentDetails($callbackData) {
        $callback = $callbackData['Body']['stkCallback'];
        
        if (!isset($callback['CallbackMetadata']['Item'])) {
            return null;
        }
        
        $items = $callback['CallbackMetadata']['Item'];
        $details = [];
        
        foreach ($items as $item) {
            $details[$item['Name']] = $item['Value'];
        }
        
        return $details;
    }
    
    /**
     * Test M-PESA connection
     */
    public function testConnection() {
        try {
            $accessToken = $this->getAccessToken();
            return [
                'success' => true,
                'message' => 'M-PESA connection successful',
                'environment' => $this->environment
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'M-PESA connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get configuration status
     */
    public function getConfigStatus() {
        $required = [
            'consumer_key' => $this->consumerKey,
            'consumer_secret' => $this->consumerSecret,
            'shortcode' => $this->shortcode,
            'passkey' => $this->passkey,
            'callback_url' => $this->callbackUrl
        ];
        
        $missing = [];
        foreach ($required as $key => $value) {
            if (empty($value)) {
                $missing[] = $key;
            }
        }
        
        return [
            'configured' => empty($missing),
            'missing' => $missing,
            'environment' => $this->environment
        ];
    }
}
?>
