<?php
/**
 * M-PESA Integration Class
 * Handles M-PESA STK Push and payment processing
 */

class MpesaIntegration {
    private $consumerKey;
    private $consumerSecret;
    private $shortCode;
    private $passkey;
    private $environment;
    private $baseUrl;
    
    public function __construct() {
        $this->consumerKey = MPESA_CONSUMER_KEY;
        $this->consumerSecret = MPESA_CONSUMER_SECRET;
        $this->shortCode = MPESA_SHORTCODE;
        $this->passkey = MPESA_PASSKEY;
        $this->environment = MPESA_ENVIRONMENT;
        
        $this->baseUrl = $this->environment === 'sandbox' 
            ? 'https://sandbox.safaricom.co.ke' 
            : 'https://api.safaricom.co.ke';
    }
    
    /**
     * Get access token
     */
    private function getAccessToken() {
        $url = $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials';
        
        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
        
        $headers = [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        }
        
        return null;
    }
    
    /**
     * Initiate STK Push
     */
    public function initiateSTKPush($phone, $amount, $accountReference, $transactionDesc) {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Failed to get access token'
            ];
        }
        
        // Format phone number
        $phone = $this->formatPhoneNumber($phone);
        
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortCode . $this->passkey . $timestamp);
        
        $url = $this->baseUrl . '/mpesa/stkpush/v1/processrequest';
        
        $data = [
            'BusinessShortCode' => $this->shortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => $this->shortCode,
            'PhoneNumber' => $phone,
            'CallBackURL' => SITE_URL . '/api/mpesa/callback.php',
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc
        ];
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        
        if ($httpCode === 200 && isset($responseData['ResponseCode']) && $responseData['ResponseCode'] === '0') {
            return [
                'success' => true,
                'checkout_request_id' => $responseData['CheckoutRequestID'],
                'message' => 'STK Push initiated successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => $responseData['ResponseDescription'] ?? 'Failed to initiate STK Push'
        ];
    }
    
    /**
     * Check STK Push status
     */
    public function checkSTKPushStatus($checkoutRequestId) {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Failed to get access token'
            ];
        }
        
        $url = $this->baseUrl . '/mpesa/stkpushquery/v1/query';
        
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortCode . $this->passkey . $timestamp);
        
        $data = [
            'BusinessShortCode' => $this->shortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId
        ];
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        
        if ($httpCode === 200 && isset($responseData['ResponseCode'])) {
            $status = 'pending';
            $receiptNumber = null;
            
            if ($responseData['ResponseCode'] === '0') {
                if (isset($responseData['ResultCode']) && $responseData['ResultCode'] === '0') {
                    $status = 'completed';
                    $receiptNumber = $responseData['MpesaReceiptNumber'] ?? null;
                } else {
                    $status = 'failed';
                }
            }
            
            return [
                'success' => true,
                'status' => $status,
                'receipt_number' => $receiptNumber,
                'message' => $responseData['ResponseDescription'] ?? 'Status checked successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => $responseData['ResponseDescription'] ?? 'Failed to check status'
        ];
    }
    
    /**
     * Format phone number to 254 format
     */
    private function formatPhoneNumber($phone) {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add 254 if it starts with 0
        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        }
        
        // Add 254 if it doesn't start with 254
        if (substr($phone, 0, 3) !== '254') {
            $phone = '254' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Process M-PESA callback
     */
    public function processCallback($callbackData) {
        try {
            $body = json_decode($callbackData, true);
            
            if (!$body || !isset($body['Body']['stkCallback'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid callback data'
                ];
            }
            
            $stkCallback = $body['Body']['stkCallback'];
            $checkoutRequestId = $stkCallback['CheckoutRequestID'];
            $resultCode = $stkCallback['ResultCode'];
            $resultDesc = $stkCallback['ResultDesc'];
            
            if ($resultCode === 0) {
                // Payment successful
                $callbackMetadata = $stkCallback['CallbackMetadata']['Item'] ?? [];
                $receiptNumber = null;
                $amount = null;
                $phoneNumber = null;
                
                foreach ($callbackMetadata as $item) {
                    switch ($item['Name']) {
                        case 'MpesaReceiptNumber':
                            $receiptNumber = $item['Value'];
                            break;
                        case 'Amount':
                            $amount = $item['Value'];
                            break;
                        case 'PhoneNumber':
                            $phoneNumber = $item['Value'];
                            break;
                    }
                }
                
                return [
                    'success' => true,
                    'checkout_request_id' => $checkoutRequestId,
                    'receipt_number' => $receiptNumber,
                    'amount' => $amount,
                    'phone_number' => $phoneNumber,
                    'status' => 'completed'
                ];
            } else {
                // Payment failed
                return [
                    'success' => false,
                    'checkout_request_id' => $checkoutRequestId,
                    'message' => $resultDesc,
                    'status' => 'failed'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error processing callback: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate M-PESA callback signature
     */
    public function validateCallback($callbackData, $signature) {
        $expectedSignature = base64_encode(hash_hmac('sha256', $callbackData, MPESA_PASSKEY, true));
        return hash_equals($expectedSignature, $signature);
    }
}
?>


