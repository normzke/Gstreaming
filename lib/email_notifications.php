<?php
/**
 * Subscription & Payment Email Notifications
 * Add these functions to lib/email.php
 */

/**
 * Send payment confirmation email
 */
function sendPaymentConfirmationEmail($userEmail, $firstName, $amount, $transactionId, $packageName)
{
    $subject = 'Payment Received - BingeTV';

    $message = "
    <html>
    <head>
        <title>Payment Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #8B0000, #660000); color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .success-box { background: #D1FAE5; border-left: 4px solid #10B981; padding: 15px; margin: 20px 0; }
            .details { background: white; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
            .button { display: inline-block; background: #8B0000; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { background: #333; color: white; padding: 20px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Payment Confirmed!</h1>
            </div>
            <div class='content'>
                <div class='success-box'>
                    <p style='margin: 0; color: #065F46; font-weight: 600;'>
                        ✓ Your payment has been received successfully!
                    </p>
                </div>
                
                <h2>Hi {$firstName},</h2>
                <p>Thank you for your payment. Your subscription is now active!</p>
                
                <div class='details'>
                    <div class='detail-row'>
                        <span><strong>Package:</strong></span>
                        <span>{$packageName}</span>
                    </div>
                    <div class='detail-row'>
                        <span><strong>Amount Paid:</strong></span>
                        <span>KES " . number_format($amount, 2) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span><strong>Transaction ID:</strong></span>
                        <span>{$transactionId}</span>
                    </div>
                    <div class='detail-row'>
                        <span><strong>Date:</strong></span>
                        <span>" . date('M j, Y H:i') . "</span>
                    </div>
                </div>
                
                <p>You can now start streaming on all your devices!</p>
                <a href='" . SITE_URL . "/user/dashboard' class='button'>Go to Dashboard</a>
                
                <p>Best regards,<br>The BingeTV Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " BingeTV. All rights reserved.</p>
                <p>support@bingetv.co.ke | +254 768 704 834</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmailSMTP($userEmail, $subject, $message, true);
}

/**
 * Send subscription activation email
 */
function sendSubscriptionActivationEmail($userEmail, $firstName, $packageName, $endDate, $streamingUrl, $username, $password, $xtreamDetails = null)
{
    $subject = 'Your BingeTV Subscription is Active!';

    $xtreamHtml = '';
    if ($xtreamDetails) {
        $xtreamHtml = "
        <div class='credentials-box' style='background: #E0F2FE; border-left-color: #0284C7; margin-top: 15px;'>
            <p style='margin: 0 0 10px 0; color: #075985; font-weight: 600;'>
                📺 Xtream Codes / M3U Details
            </p>
            <div class='credential-item'><strong>Domain:</strong> {$xtreamDetails['server']}</div>
            <div class='credential-item'><strong>Username:</strong> {$xtreamDetails['username']}</div>
            <div class='credential-item'><strong>Password:</strong> {$xtreamDetails['password']}</div>
            <div class='credential-item' style='margin-top: 10px; word-break: break-all;'>
                <strong>M3U URL:</strong><br>
                <a href='{$xtreamDetails['m3u_url']}' style='color: #0284C7;'>{$xtreamDetails['m3u_url']}</a>
            </div>
        </div>";
    }

    $message = "
    <html>
    <head>
        <title>Subscription Activated</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #8B0000, #660000); color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .credentials-box { background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px; margin: 20px 0; }
            .credential-item { padding: 8px 0; font-family: monospace; }
            .button { display: inline-block; background: #8B0000; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { background: #333; color: white; padding: 20px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Welcome to BingeTV!</h1>
            </div>
            <div class='content'>
                <h2>Hi {$firstName},</h2>
                <p>Your {$packageName} subscription is now active! You can start streaming immediately.</p>
                
                <p><strong>Subscription Details:</strong></p>
                <ul>
                    <li>Package: {$packageName}</li>
                    <li>Valid Until: " . date('M j, Y', strtotime($endDate)) . "</li>
                </ul>
                
                <div class='credentials-box'>
                    <p style='margin: 0 0 10px 0; color: #92400E; font-weight: 600;'>
                        🔐 Your Login Credentials
                    </p>
                    <div class='credential-item'><strong>Username:</strong> {$username}</div>
                    <div class='credential-item'><strong>Password:</strong> {$password}</div>
                    <div class='credential-item'><strong>Web Portal:</strong> <a href='" . SITE_URL . "/user/login'>Login Here</a></div>
                </div>

                {$xtreamHtml}
                
                <p><strong>How to Start Streaming:</strong></p>
                <ol>
                    <li>Download our app or use any IPTV player (TiviMate, IPTV Smarters, etc.)</li>
                    <li>Enter the credentials above</li>
                    <li>Start enjoying thousands of channels!</li>
                </ol>
                
                <a href='" . SITE_URL . "/user/downloads' class='button'>Download Apps</a>
                
                <p>Need help? Contact our support team anytime.</p>
                <p>Best regards,<br>The BingeTV Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " BingeTV. All rights reserved.</p>
                <p>support@bingetv.co.ke | +254 768 704 834</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmailSMTP($userEmail, $subject, $message, true);
}

/**
 * Send subscription expiry warning email (7 days before)
 */
function sendSubscriptionExpiryWarningEmail($userEmail, $firstName, $packageName, $endDate, $daysRemaining)
{
    $subject = 'Your BingeTV Subscription Expires Soon!';

    $message = "
    <html>
    <head>
        <title>Subscription Expiry Warning</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #F59E0B, #D97706); color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .warning-box { background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px; margin: 20px 0; }
            .button { display: inline-block; background: #8B0000; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { background: #333; color: white; padding: 20px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>⚠️ Subscription Expiring Soon</h1>
            </div>
            <div class='content'>
                <div class='warning-box'>
                    <p style='margin: 0; color: #92400E; font-weight: 600;'>
                        Your subscription expires in {$daysRemaining} day" . ($daysRemaining != 1 ? 's' : '') . "!
                    </p>
                </div>
                
                <h2>Hi {$firstName},</h2>
                <p>This is a friendly reminder that your {$packageName} subscription will expire on <strong>" . date('M j, Y', strtotime($endDate)) . "</strong>.</p>
                
                <p>Don't miss out on your favorite shows and channels! Renew now to continue enjoying uninterrupted streaming.</p>
                
                <a href='" . SITE_URL . "/user/dashboard' class='button'>Renew Subscription</a>
                
                <p><strong>Why Renew Now?</strong></p>
                <ul>
                    <li>No interruption in service</li>
                    <li>Keep your streaming credentials</li>
                    <li>Continue watching where you left off</li>
                </ul>
                
                <p>Best regards,<br>The BingeTV Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " BingeTV. All rights reserved.</p>
                <p>support@bingetv.co.ke | +254 768 704 834</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmailSMTP($userEmail, $subject, $message, true);
}

/**
 * Send subscription expired email
 */
function sendSubscriptionExpiredEmail($userEmail, $firstName, $packageName)
{
    $subject = 'Your BingeTV Subscription Has Expired';

    $message = "
    <html>
    <head>
        <title>Subscription Expired</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #DC2626, #991B1B); color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .expired-box { background: #FEE2E2; border-left: 4px solid #DC2626; padding: 15px; margin: 20px 0; }
            .button { display: inline-block; background: #8B0000; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { background: #333; color: white; padding: 20px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Subscription Expired</h1>
            </div>
            <div class='content'>
                <div class='expired-box'>
                    <p style='margin: 0; color: #991B1B; font-weight: 600;'>
                        Your {$packageName} subscription has expired.
                    </p>
                </div>
                
                <h2>Hi {$firstName},</h2>
                <p>Your subscription has ended. To continue enjoying BingeTV's premium content, please renew your subscription.</p>
                
                <a href='" . SITE_URL . "/user/dashboard' class='button'>Renew Now</a>
                
                <p><strong>What You're Missing:</strong></p>
                <ul>
                    <li>Thousands of live TV channels</li>
                    <li>Sports, movies, and entertainment</li>
                    <li>Stream on multiple devices</li>
                    <li>HD quality streaming</li>
                </ul>
                
                <p>Renew today and get back to streaming!</p>
                <p>Best regards,<br>The BingeTV Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " BingeTV. All rights reserved.</p>
                <p>support@bingetv.co.ke | +254 768 704 834</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmailSMTP($userEmail, $subject, $message, true);
}

/**
 * Send subscription renewal confirmation email
 */
function sendSubscriptionRenewalEmail($userEmail, $firstName, $packageName, $newEndDate)
{
    $subject = 'Subscription Renewed - BingeTV';

    $message = "
    <html>
    <head>
        <title>Subscription Renewed</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #8B0000, #660000); color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .success-box { background: #D1FAE5; border-left: 4px solid #10B981; padding: 15px; margin: 20px 0; }
            .button { display: inline-block; background: #8B0000; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { background: #333; color: white; padding: 20px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Subscription Renewed!</h1>
            </div>
            <div class='content'>
                <div class='success-box'>
                    <p style='margin: 0; color: #065F46; font-weight: 600;'>
                        ✓ Your subscription has been renewed successfully!
                    </p>
                </div>
                
                <h2>Hi {$firstName},</h2>
                <p>Thank you for renewing your {$packageName} subscription!</p>
                
                <p><strong>Your new subscription details:</strong></p>
                <ul>
                    <li>Package: {$packageName}</li>
                    <li>Valid Until: " . date('M j, Y', strtotime($newEndDate)) . "</li>
                </ul>
                
                <p>Continue enjoying uninterrupted streaming on all your devices!</p>
                
                <a href='" . SITE_URL . "/user/dashboard' class='button'>Go to Dashboard</a>
                
                <p>Best regards,<br>The BingeTV Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " BingeTV. All rights reserved.</p>
                <p>support@bingetv.co.ke | +254 768 704 834</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmailSMTP($userEmail, $subject, $message, true);
}
?>