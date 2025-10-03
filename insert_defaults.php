<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h2>Inserting Default Data</h2>";

// Insert default social media config
$checkSocial = "SELECT COUNT(*) FROM social_media_config WHERE id = 1";
$checkStmt = $conn->prepare($checkSocial);
$checkStmt->execute();
$socialExists = $checkStmt->fetchColumn();

if ($socialExists == 0) {
    $insertSocialMedia = "
    INSERT INTO social_media_config (id, facebook_url, twitter_url, instagram_url, youtube_url, linkedin_url, tiktok_url, whatsapp_number, telegram_url, display_in_footer, display_in_header, is_active)
    VALUES (1, '', '', '', '', '', '', '', '', true, false, true)";
    
    try {
        $conn->exec($insertSocialMedia);
        echo "<p style='color: green;'>✓ Default social media config inserted</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Error inserting social media config: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ Social media config already exists</p>";
}

// Insert default mpesa config
$checkMpesa = "SELECT COUNT(*) FROM mpesa_config WHERE id = 1";
$checkStmt = $conn->prepare($checkMpesa);
$checkStmt->execute();
$mpesaExists = $checkStmt->fetchColumn();

if ($mpesaExists == 0) {
    $insertMpesa = "
    INSERT INTO mpesa_config (id, business_short_code, passkey, consumer_key, consumer_secret, till_number, paybill_number, callback_url, timeout_url, environment, is_active)
    VALUES (1, '', '', '', '', '', '', 'https://bingetv.co.ke/api/mpesa/callback.php', 'https://bingetv.co.ke/api/mpesa/timeout.php', 'sandbox', false)";
    
    try {
        $conn->exec($insertMpesa);
        echo "<p style='color: green;'>✓ Default mpesa config inserted</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Error inserting mpesa config: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ M-PESA config already exists</p>";
}

echo "<h3 style='color: green;'>Default data insertion completed!</h3>";
echo "<p><a href='admin/mpesa-config.php'>Go to M-PESA Config</a> | <a href='admin/social-media.php'>Go to Social Media</a></p>";
?>
