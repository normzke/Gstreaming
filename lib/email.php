<?php
/**
 * Email functionality using SMTP
 * Uses PHPMailer for proper SMTP authentication and delivery
 */

// Check if PHPMailer is available via Composer
$phpmailer_available = file_exists(__DIR__ . '/../vendor/autoload.php');

if ($phpmailer_available) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

define('PHPMAILER_AVAILABLE', $phpmailer_available);

/**
 * Send email using SMTP
 */
function sendEmailSMTP($to, $subject, $message, $isHTML = true) {
    if (!PHPMAILER_AVAILABLE) {
        return sendEmailFallback($to, $subject, $message);
    }
    
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 0; // Disable debug output in production
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML($isHTML);
        $mail->Subject = $subject;
        $mail->Body = $message;
        if (!$isHTML) {
            $mail->AltBody = strip_tags($message);
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send welcome email to new users
 */
function sendWelcomeEmail($userEmail, $firstName) {
    $subject = 'Welcome to BingeTV - Your Streaming Journey Begins!';

    $message = "
    <html>
    <head>
        <title>Welcome to BingeTV</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #8B0000, #660000); color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
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
                <p>Thank you for joining BingeTV. Your account has been created successfully!</p>
                <p>Get ready to enjoy thousands of channels on your favorite devices!</p>
                <a href='" . SITE_URL . "/user/dashboard/' class='button'>Go to Dashboard</a>
                <p>If you have any questions, feel free to contact our support team.</p>
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
 * Send email verification email
 */
function sendEmailVerification($userEmail, $verificationToken, $firstName) {
    $subject = 'Verify Your BingeTV Email Address';

    $verificationLink = SITE_URL . "/verify-email.php?token=" . $verificationToken;

    $message = "
    <html>
    <head>
        <title>Verify Your Email</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #8B0000, #660000); color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .button { display: inline-block; background: #8B0000; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { background: #333; color: white; padding: 20px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Email Verification Required</h1>
            </div>
            <div class='content'>
                <h2>Hi {$firstName},</h2>
                <p>Thank you for registering with BingeTV! To complete your registration and start enjoying our streaming services, please verify your email address.</p>
                <a href='{$verificationLink}' class='button'>Verify Email Address</a>
                <p>If the button doesn't work, you can also copy and paste this link into your browser:</p>
                <p><a href='{$verificationLink}'>{$verificationLink}</a></p>
                <p>This verification link will expire in 24 hours for security reasons.</p>
                <p>If you didn't create an account with BingeTV, please ignore this email.</p>
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
 * Send password reset email
 */
function sendPasswordResetEmail($userEmail, $resetToken, $firstName) {
    $subject = 'Reset Your BingeTV Password';

    $resetLink = SITE_URL . "/reset-password.php?token=" . $resetToken;

    $message = "
    <html>
    <head>
        <title>Password Reset</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #8B0000, #660000); color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .button { display: inline-block; background: #8B0000; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { background: #333; color: white; padding: 20px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Password Reset Request</h1>
            </div>
            <div class='content'>
                <h2>Hi {$firstName},</h2>
                <p>We received a request to reset your BingeTV password. Click the button below to create a new password:</p>
                <a href='{$resetLink}' class='button'>Reset Password</a>
                <p>If the button doesn't work, you can also copy and paste this link into your browser:</p>
                <p><a href='{$resetLink}'>{$resetLink}</a></p>
                <p>This password reset link will expire in 1 hour for security reasons.</p>
                <p>If you didn't request a password reset, please ignore this email. Your password will remain unchanged.</p>
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
 * Generate email verification token
 */
function generateEmailVerificationToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Verify email token and activate user account
 */
function verifyEmailToken($token) {
    global $conn;
    if (!$conn) {
        $db = new Database();
        $conn = $db->getConnection();
    }

    try {
        // Find user with this verification token
        $query = "SELECT id, first_name FROM users WHERE email_verification_token = ? AND email_verification_expires > NOW() AND email_verified = false";
        $stmt = $conn->prepare($query);
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Activate user account
            $updateQuery = "UPDATE users SET email_verified = true, email_verification_token = NULL, email_verification_expires = NULL WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->execute([$user['id']]);

            return [
                'success' => true,
                'user_id' => $user['id'],
                'first_name' => $user['first_name']
            ];
        }

        return ['success' => false, 'error' => 'Invalid or expired verification token'];

    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Verification failed'];
    }
}

/**
 * Fallback email function using basic mail() for simple text emails
 * Use only when SMTP is not available
 */
function sendEmailFallback($to, $subject, $message) {
    $headers = 'From: ' . SMTP_FROM_EMAIL . "\r\n" .
               'Reply-To: ' . SMTP_FROM_EMAIL . "\r\n" .
               'X-Mailer: PHP/' . phpversion() . "\r\n" .
               'Content-type: text/html; charset=UTF-8';

    return mail($to, $subject, $message, $headers);
}
?>
