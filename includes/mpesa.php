<?php
/**
 * M-PESA Integration for GStreaming
 * Handles M-PESA Till and Paybill payments
 */

require_once __DIR__ . '/../config/config.php';

class MPesaAPI {
    private $consumerKey;
    private $consumerSecret;
    private $shortcode;
    private $passkey;
    private $environment;
    private $baseUrl;
    private $accessToken;
    
    public function __construct() {
        $this->consumerKey = MPESA_CONSUMER_KEY;
        $this->consumerSecret = MPESA_CONSUMER_SECRET;
        $this->shortcode = MPESA_SHORTCODE;
        $this->passkey = MPESA_PASSKEY;
        $this->environment = MPESA_ENVIRONMENT;
        
        // Set base URL based on environment
        if ($this->environment === 'sandbox') {
            $this->baseUrl = 'https://sandbox.safaricom.co.ke';
        } else {
            $this->baseUrl = 'https://api.safaricom.co.ke';
        }
        
        $this->accessToken = $this->getAccessToken();
    }
    
    /**
     * Get OAuth access token
     */
    private function getAccessToken() {
        $url = $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials';
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($this->consumerKey . ':' . $this->consumerSecret)
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        }
        
        return null;
    }
    
    /**
     * STK Push for Till Number
     */
    public function stkPushTill($phoneNumber, $amount, $accountReference, $transactionDesc) {
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        
        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phoneNumber,
            'PartyB' => MPESA_TILL_NUMBER,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => SITE_URL . '/api/mpesa/callback.php',
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc
        ];
        
        return $this->makeRequest('/mpesa/stkpush/v1/processrequest', $payload);
    }
    
    /**
     * STK Push for Paybill
     */
    public function stkPushPaybill($phoneNumber, $amount, $accountReference, $transactionDesc) {
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        
        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phoneNumber,
            'PartyB' => MPESA_PAYBILL_NUMBER,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => SITE_URL . '/api/mpesa/callback.php',
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc
        ];
        
        return $this->makeRequest('/mpesa/stkpush/v1/processrequest', $payload);
    }
    
    /**
     * Query STK Push status
     */
    public function queryStkPush($checkoutRequestId) {
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        
        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId
        ];
        
        return $this->makeRequest('/mpesa/stkpushquery/v1/query', $payload);
    }
    
    /**
     * Register URL for callbacks
     */
    public function registerURL($validationURL, $confirmationURL) {
        $payload = [
            'ShortCode' => $this->shortcode,
            'ResponseType' => 'Completed',
            'ConfirmationURL' => $confirmationURL,
            'ValidationURL' => $validationURL
        ];
        
        return $this->makeRequest('/mpesa/c2b/v1/registerurl', $payload);
    }
    
    /**
     * Simulate C2B transaction (for testing)
     */
    public function simulateC2B($phoneNumber, $amount, $billRefNumber) {
        $payload = [
            'ShortCode' => $this->shortcode,
            'CommandID' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'Msisdn' => $phoneNumber,
            'BillRefNumber' => $billRefNumber
        ];
        
        return $this->makeRequest('/mpesa/c2b/v1/simulate', $payload);
    }
    
    /**
     * Make HTTP request to M-PESA API
     */
    private function makeRequest($endpoint, $payload) {
        if (!$this->accessToken) {
            return [
                'success' => false,
                'error' => 'Failed to get access token'
            ];
        }
        
        $url = $this->baseUrl . $endpoint;
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'cURL Error: ' . $error
            ];
        }
        
        $data = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $data
            ];
        } else {
            return [
                'success' => false,
                'error' => $data['errorMessage'] ?? 'Unknown error occurred',
                'data' => $data
            ];
        }
    }
}

/**
 * Process M-PESA payment
 */
function processMPesaPayment($userId, $packageId, $phoneNumber, $amount) {
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        $conn->beginTransaction();
        
        // Get package details
        $packageQuery = "SELECT * FROM packages WHERE id = :id AND is_active = true";
        $packageStmt = $conn->prepare($packageQuery);
        $packageStmt->bindParam(':id', $packageId);
        $packageStmt->execute();
        $package = $packageStmt->fetch();
        
        if (!$package) {
            throw new Exception('Package not found');
        }
        
        // Get user details
        $userQuery = "SELECT * FROM users WHERE id = :id";
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bindParam(':id', $userId);
        $userStmt->execute();
        $user = $userStmt->fetch();
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        // Create payment record
        $paymentQuery = "INSERT INTO payments (user_id, amount, currency, payment_method, phone_number, status) 
                        VALUES (:user_id, :amount, :currency, :payment_method, :phone_number, 'pending')";
        
        $paymentStmt = $conn->prepare($paymentQuery);
        $paymentStmt->bindParam(':user_id', $userId);
        $paymentStmt->bindParam(':amount', $amount);
        $paymentStmt->bindParam(':currency', 'KES');
        $paymentStmt->bindParam(':payment_method', 'mpesa');
        $paymentStmt->bindParam(':phone_number', $phoneNumber);
        $paymentStmt->execute();
        
        $paymentId = $conn->lastInsertId();
        
        // Generate account reference
        $accountReference = 'GSTREAM' . $paymentId;
        
        // Initialize M-PESA API
        $mpesa = new MPesaAPI();
        
        // Use Till Number for payments under 1000 KES, Paybill for higher amounts
        if ($amount < 1000) {
            $result = $mpesa->stkPushTill($phoneNumber, $amount, $accountReference, 'GStreaming Subscription');
        } else {
            $result = $mpesa->stkPushPaybill($phoneNumber, $amount, $accountReference, 'GStreaming Subscription');
        }
        
        if ($result['success']) {
            // Update payment with checkout request ID
            $updateQuery = "UPDATE payments SET mpesa_transaction_id = :transaction_id WHERE id = :id";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bindParam(':transaction_id', $result['data']['CheckoutRequestID']);
            $updateStmt->bindParam(':id', $paymentId);
            $updateStmt->execute();
            
            $conn->commit();
            
            return [
                'success' => true,
                'payment_id' => $paymentId,
                'checkout_request_id' => $result['data']['CheckoutRequestID'],
                'message' => 'Payment initiated. Please complete the payment on your phone.'
            ];
        } else {
            $conn->rollback();
            return [
                'success' => false,
                'error' => $result['error']
            ];
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Verify M-PESA payment
 */
function verifyMPesaPayment($checkoutRequestId) {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get payment record
    $paymentQuery = "SELECT * FROM payments WHERE mpesa_transaction_id = :transaction_id";
    $paymentStmt = $conn->prepare($paymentQuery);
    $paymentStmt->bindParam(':transaction_id', $checkoutRequestId);
    $paymentStmt->execute();
    $payment = $paymentStmt->fetch();
    
    if (!$payment) {
        return [
            'success' => false,
            'error' => 'Payment not found'
        ];
    }
    
    if ($payment['status'] !== 'pending') {
        return [
            'success' => true,
            'status' => $payment['status'],
            'message' => 'Payment already processed'
        ];
    }
    
    try {
        // Query M-PESA for payment status
        $mpesa = new MPesaAPI();
        $result = $mpesa->queryStkPush($checkoutRequestId);
        
        if ($result['success']) {
            $responseCode = $result['data']['ResponseCode'] ?? null;
            
            if ($responseCode === '0') {
                // Payment successful
                $conn->beginTransaction();
                
                // Update payment status
                $updatePaymentQuery = "UPDATE payments SET status = 'completed', mpesa_receipt_number = :receipt_number, transaction_date = NOW() WHERE id = :id";
                $updatePaymentStmt = $conn->prepare($updatePaymentQuery);
                $updatePaymentStmt->bindParam(':receipt_number', $result['data']['MpesaReceiptNumber']);
                $updatePaymentStmt->bindParam(':id', $payment['id']);
                $updatePaymentStmt->execute();
                
                // Create subscription (assuming package_id is stored in payment details)
                $subscriptionQuery = "INSERT INTO user_subscriptions (user_id, package_id, status, start_date, end_date) 
                                     VALUES (:user_id, :package_id, 'active', NOW(), NOW() + INTERVAL '30 days')";
                $subscriptionStmt = $conn->prepare($subscriptionQuery);
                $subscriptionStmt->bindParam(':user_id', $payment['user_id']);
                $subscriptionStmt->bindParam(':package_id', $payment['subscription_id']); // This should be set during payment creation
                $subscriptionStmt->execute();
                
                $conn->commit();
                
                return [
                    'success' => true,
                    'status' => 'completed',
                    'receipt_number' => $result['data']['MpesaReceiptNumber']
                ];
            } else {
                // Payment failed
                $updateQuery = "UPDATE payments SET status = 'failed' WHERE id = :id";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bindParam(':id', $payment['id']);
                $updateStmt->execute();
                
                return [
                    'success' => false,
                    'status' => 'failed',
                    'error' => 'Payment was not completed'
                ];
            }
        } else {
            return [
                'success' => false,
                'error' => $result['error']
            ];
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
?>
