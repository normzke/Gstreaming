<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../lib/seo.php';
require_once __DIR__ . '/../lib/email.php';

// Get SEO data
$seo_meta = SEO::getMetaTags('verify-email');
$og_tags = SEO::getOpenGraphTags('verify-email');
$canonical_url = SEO::getCanonicalUrl('verify-email');

$token = $_GET['token'] ?? '';
$message = '';
$messageType = '';

if ($token) {
    $verificationResult = verifyEmailToken($token);

    if ($verificationResult['success']) {
        $message = "Email verified successfully! Welcome to BingeTV, {$verificationResult['first_name']}!";
        $messageType = 'success';

        // Redirect to login after 3 seconds
        header("refresh:3;url=login.php");
    } else {
        $message = $verificationResult['error'];
        $messageType = 'error';
    }
} else {
    $message = 'Invalid verification link.';
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="https://bingetv.co.ke/">

    <!-- SEO Meta Tags -->
    <title><?php echo htmlspecialchars($seo_meta['title']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seo_meta['description']); ?>">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/components.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .verification-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #8B0000 0%, #660000 100%);
            padding: 20px;
        }

        .verification-container {
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }

        .verification-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
        }

        .verification-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }

        .verification-icon.success {
            background: #d1fae5;
            color: #065f46;
        }

        .verification-icon.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .verification-title {
            color: #8B0000;
            font-family: 'Orbitron', sans-serif;
            font-weight: 900;
            margin-bottom: 16px;
        }

        .verification-message {
            color: #666;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .btn-primary {
            background: linear-gradient(135deg, #8B0000, #660000);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
        }

        .countdown {
            color: #8B0000;
            font-weight: 600;
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <main class="verification-page">
        <div class="verification-container">
            <div class="verification-card">
                <div class="verification-icon <?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                </div>

                <h1 class="verification-title">
                    <?php echo $messageType === 'success' ? 'Email Verified!' : 'Verification Failed'; ?>
                </h1>

                <div class="verification-message">
                    <?php echo htmlspecialchars($message); ?>
                </div>

                <?php if ($messageType === 'success'): ?>
                    <div class="countdown">
                        Redirecting to login page in 3 seconds...
                    </div>
                    <a href="login" class="btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Continue to Login
                    </a>
                <?php else: ?>
                    <a href="login" class="btn-primary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Countdown timer for redirect
        <?php if ($messageType === 'success'): ?>
        let countdown = 3;
        const countdownElement = document.querySelector('.countdown');

        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = `Redirecting to login page in ${countdown} seconds...`;

            if (countdown <= 0) {
                clearInterval(timer);
            }
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>
