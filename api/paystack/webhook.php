<?php
/**
 * Paystack Webhook Handler
 * Processes asynchronous payment notifications from Paystack
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/payment-processor.php';
require_once __DIR__ . '/../../lib/paystack_integration.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit();
}

// Retrieve the request's body
$input = file_get_contents('php://input');
$paystackSignature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

if (!$paystackSignature) {
    error_log("Paystack Webhook: Missing signature");
    exit();
}

$paystack = new PaystackIntegration();

// Validate the signature
if (!$paystack->validateWebhook($input, $paystackSignature)) {
    error_log("Paystack Webhook: Invalid signature");
    http_response_code(400);
    exit();
}

// Respond with 200 OK as soon as signature is verified
http_response_code(200);

$event = json_decode($input, true);

if (!$event) {
    exit();
}

// Process different event types
switch ($event['event']) {
    case 'charge.success':
        $reference = $event['data']['reference'];
        error_log("Paystack Webhook: Processing charge.success for reference " . $reference);

        $processor = new PaymentProcessor();
        $processor->processPaystackCallback($reference);
        break;

    case 'transfer.success':
        // Handle successful transfers if applicable
        break;

    case 'transfer.failed':
        // Handle failed transfers if applicable
        break;

    default:
        error_log("Paystack Webhook: Unhandled event type " . $event['event']);
        break;
}

exit();
