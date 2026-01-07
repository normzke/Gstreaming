<?php
/**
 * Paystack Integration Class
 * Handles Paystack API interactions for payments
 */

class PaystackIntegration
{
    private $secretKey;
    private $publicKey;

    public function __construct()
    {
        $this->secretKey = PAYSTACK_SECRET_KEY;
        $this->publicKey = PAYSTACK_PUBLIC_KEY;
    }

    /**
     * Initialize a transaction
     * 
     * @param array $data [email, amount, reference, callback_url, metadata]
     * @return array [success, authorization_url, access_code, reference, message]
     */
    public function initializeTransaction($data)
    {
        $url = PAYSTACK_INITIALIZE_URL;

        // Amount must be in kobo/cents (multiply by 100)
        $data['amount'] = (int) ($data['amount'] * 100);

        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $err
            ];
        }

        $result = json_decode($response, true);

        if ($httpCode === 200 && $result['status']) {
            return [
                'success' => true,
                'authorization_url' => $result['data']['authorization_url'],
                'access_code' => $result['data']['access_code'],
                'reference' => $result['data']['reference'],
                'message' => $result['message']
            ];
        }

        return [
            'success' => false,
            'message' => $result['message'] ?? 'Failed to initialize transaction'
        ];
    }

    /**
     * Verify a transaction
     * 
     * @param string $reference
     * @return array [success, data, message]
     */
    public function verifyTransaction($reference)
    {
        $url = PAYSTACK_VERIFY_URL . rawurlencode($reference);

        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $err
            ];
        }

        $result = json_decode($response, true);

        if ($httpCode === 200 && $result['status']) {
            return [
                'success' => true,
                'data' => $result['data'],
                'message' => $result['message']
            ];
        }

        return [
            'success' => false,
            'message' => $result['message'] ?? 'Failed to verify transaction'
        ];
    }

    /**
     * Validate Webhook Signature
     */
    public function validateWebhook($payload, $signature)
    {
        return $signature === hash_hmac('sha512', $payload, $this->secretKey);
    }
}
