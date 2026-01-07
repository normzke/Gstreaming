<?php
/**
 * Paystack Success Verification Page
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../lib/functions.php';
require_once __DIR__ . '/../../lib/payment-processor.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$reference = $_GET['reference'] ?? '';

if (!$reference) {
    header('Location: ../subscriptions/subscribe.php');
    exit();
}

$processor = new PaymentProcessor();
$result = $processor->processPaystackCallback($reference);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Verification - BingeTV</title>

    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/components.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .verification-page {
            background: var(--background-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .verification-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .status-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        .status-success {
            color: #10b981;
        }

        .status-error {
            color: #ef4444;
        }

        .verification-header h1 {
            color: var(--text-color-light);
            margin-bottom: 10px;
            font-family: 'Orbitron', sans-serif;
        }

        .verification-header p {
            color: var(--text-color-secondary);
            margin-bottom: 30px;
        }

        .verification-details {
            background: var(--background-color-dark);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: var(--text-color-light);
            font-size: 0.9rem;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: block;
            width: 100%;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background: #f1f2f6;
            color: var(--text-color-light);
        }
    </style>
</head>

<body class="verification-page">
    <div class="verification-container">
        <?php if ($result['success']): ?>
            <div class="status-icon status-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="verification-header">
                <h1>Payment Successful!</h1>
                <p>Your subscription has been activated successfully.</p>
            </div>
            <div class="verification-details">
                <div class="detail-item">
                    <span>Reference:</span>
                    <span>
                        <?php echo htmlspecialchars($reference); ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span>Status:</span>
                    <span>Completed</span>
                </div>
            </div>
            <a href="../dashboard/" class="btn btn-primary">Go to Dashboard</a>
        <?php else: ?>
            <div class="status-icon status-error">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="verification-header">
                <h1>Payment Failed</h1>
                <p>
                    <?php echo htmlspecialchars($result['message'] ?? 'An error occurred during verification.'); ?>
                </p>
            </div>
            <div class="verification-details">
                <div class="detail-item">
                    <span>Reference:</span>
                    <span>
                        <?php echo htmlspecialchars($reference); ?>
                    </span>
                </div>
            </div>
            <a href="../subscriptions/subscribe.php" class="btn btn-primary">Try Again</a>
            <a href="../dashboard/" class="btn btn-secondary">Back to Dashboard</a>
        <?php endif; ?>
    </div>
</body>

</html>