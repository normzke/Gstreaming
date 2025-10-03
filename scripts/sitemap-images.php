<?php
/**
 * Image Sitemap Generator
 * Generates XML sitemap for images
 */

require_once 'config/config.php';
require_once 'config/database.php';

header('Content-Type: application/xml; charset=utf-8');

$db = new Database();
$conn = $db->getConnection();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    
    <!-- Homepage Images -->
    <url>
        <loc>https://bingetv.co.ke/</loc>
        <image:image>
            <image:loc>https://bingetv.co.ke/assets/images/hero-bg.jpg</image:loc>
            <image:title>BingeTV - Premium TV Streaming Kenya</image:title>
            <image:caption>Never miss Premier League & Premium Sports</image:caption>
        </image:image>
        <image:image>
            <image:loc>https://bingetv.co.ke/assets/images/logo.png</image:loc>
            <image:title>BingeTV Logo</image:title>
            <image:caption>BingeTV - Kenya's Premier Streaming Service</image:caption>
        </image:image>
    </url>
    
    <!-- Channels Page Images -->
    <url>
        <loc>https://bingetv.co.ke/channels.php</loc>
        <?php
        try {
            $channelsQuery = "SELECT id, name, logo_url FROM channels WHERE is_active = true ORDER BY sort_order LIMIT 20";
            $channelsStmt = $conn->prepare($channelsQuery);
            $channelsStmt->execute();
            $channels = $channelsStmt->fetchAll();
            
            foreach ($channels as $channel):
        ?>
        <image:image>
            <image:loc><?php echo htmlspecialchars($channel['logo_url']); ?></image:loc>
            <image:title><?php echo htmlspecialchars($channel['name']); ?> - BingeTV</image:title>
            <image:caption><?php echo htmlspecialchars($channel['name']); ?> TV Channel on BingeTV Kenya</image:caption>
        </image:image>
        <?php endforeach; ?>
        <?php } catch (Exception $e) { /* Continue without channel images */ } ?>
    </url>
    
    <!-- Gallery Page Images -->
    <url>
        <loc>https://bingetv.co.ke/gallery.php</loc>
        <image:image>
            <image:loc>https://bingetv.co.ke/assets/images/gallery-preview.jpg</image:loc>
            <image:title>BingeTV Video Gallery</image:title>
            <image:caption>High-quality sports and documentary videos</image:caption>
        </image:image>
        <image:image>
            <image:loc>https://bingetv.co.ke/assets/images/premier-league-preview.jpg</image:loc>
            <image:title>Premier League Highlights</image:title>
            <image:caption>Watch Premier League highlights and live matches</image:caption>
        </image:image>
        <image:image>
            <image:loc>https://bingetv.co.ke/assets/images/natgeo-preview.jpg</image:loc>
            <image:title>National Geographic Content</image:title>
            <image:caption>Explore the world with National Geographic documentaries</image:caption>
        </image:image>
    </url>
    
    <!-- Subscribe Page Images -->
    <url>
        <loc>https://bingetv.co.ke/subscribe.php</loc>
        <image:image>
            <image:loc>https://bingetv.co.ke/assets/images/subscription-plans.jpg</image:loc>
            <image:title>BingeTV Subscription Plans</image:title>
            <image:caption>Choose from our flexible subscription plans</image:caption>
        </image:image>
        <image:image>
            <image:loc>https://bingetv.co.ke/assets/images/mpesa-payment.jpg</image:loc>
            <image:title>M-PESA Payment</image:title>
            <image:caption>Pay securely with M-PESA mobile money</image:caption>
        </image:image>
    </url>
    
</urlset>
