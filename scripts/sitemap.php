<?php
/**
 * Dynamic Sitemap Generator
 * Generates XML sitemap for search engines
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/seo.php';

header('Content-Type: application/xml; charset=utf-8');

$db = new Database();
$conn = $db->getConnection();

// Get sitemap data
$sitemap_data = SEO::getSitemapData();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
    
    <?php foreach ($sitemap_data as $item): ?>
    <url>
        <loc><?php echo htmlspecialchars($item['url']); ?></loc>
        <lastmod><?php echo $item['lastmod']; ?></lastmod>
        <changefreq><?php echo $item['changefreq']; ?></changefreq>
        <priority><?php echo $item['priority']; ?></priority>
        
        <?php if (strpos($item['url'], 'channel.php') !== false): ?>
        <image:image>
            <image:loc>https://bingetv.co.ke/assets/images/channel-banner.jpg</image:loc>
            <image:title>BingeTV Channel</image:title>
            <image:caption>Premium TV Channel on BingeTV Kenya</image:caption>
        </image:image>
        <?php endif; ?>
        
        <?php if (strpos($item['url'], 'gallery.php') !== false): ?>
        <video:video>
            <video:thumbnail_loc>https://bingetv.co.ke/assets/images/gallery-thumb.jpg</video:thumbnail_loc>
            <video:title>BingeTV Video Gallery</video:title>
            <video:description>High-quality sports and documentary videos</video:description>
            <video:content_loc>https://bingetv.co.ke/gallery.php</video:content_loc>
            <video:player_loc allow_embed="yes">https://bingetv.co.ke/gallery.php</video:player_loc>
            <video:duration>300</video:duration>
            <video:view_count>1000</video:view_count>
            <video:publication_date><?php echo date('Y-m-d'); ?>T00:00:00+00:00</video:publication_date>
            <video:category>Entertainment</video:category>
            <video:family_friendly>yes</video:family_friendly>
        </video:video>
        <?php endif; ?>
    </url>
    <?php endforeach; ?>
    
</urlset>
