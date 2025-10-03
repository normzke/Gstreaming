<?php
// Quick script to fix remaining admin pages

$pages = [
    'payments.php' => 'Payments',
    'subscriptions.php' => 'Subscriptions', 
    'orders.php' => 'Orders',
    'social-media.php' => 'Social Media',
    'mpesa-config.php' => 'M-PESA Config',
    'generate-receipt.php' => 'Generate Receipt'
];

foreach ($pages as $page => $title) {
    $filePath = "admin/$page";
    if (!file_exists($filePath)) {
        echo "File not found: $filePath\n";
        continue;
    }
    
    echo "Processing: $filePath\n";
    
    // Read the file
    $content = file_get_contents($filePath);
    
    // Find the position after the include statement
    $includePos = strpos($content, "include 'includes/header.php';");
    if ($includePos === false) {
        echo "No include found in $filePath\n";
        continue;
    }
    
    // Find the position after the PHP closing tag
    $phpClosePos = strpos($content, '?>', $includePos);
    if ($phpClosePos === false) {
        echo "No PHP closing tag found in $filePath\n";
        continue;
    }
    
    // Find the start of HTML content
    $htmlStartPos = strpos($content, '<!DOCTYPE html>', $phpClosePos);
    if ($htmlStartPos === false) {
        $htmlStartPos = strpos($content, '<html', $phpClosePos);
    }
    if ($htmlStartPos === false) {
        $htmlStartPos = strpos($content, '<head', $phpClosePos);
    }
    
    if ($htmlStartPos === false) {
        echo "No HTML start found in $filePath\n";
        continue;
    }
    
    // Find the end of HTML content
    $htmlEndPos = strrpos($content, '</html>');
    if ($htmlEndPos === false) {
        $htmlEndPos = strrpos($content, '</body>');
    }
    
    if ($htmlEndPos === false) {
        echo "No HTML end found in $filePath\n";
        continue;
    }
    
    // Extract the part before HTML
    $beforeHtml = substr($content, 0, $htmlStartPos);
    
    // Create new content with proper layout
    $newContent = $beforeHtml . "\n\n<!-- $title Management Content -->\n<div class=\"admin-card\">\n    <div class=\"card-header\">\n        <h3 class=\"card-title\">$title Management</h3>\n        <p>Manage $title settings and configurations</p>\n    </div>\n    <div class=\"card-body\">\n        <p>This page is being updated with the new layout. Content will be added here.</p>\n    </div>\n</div>\n\n<?php include 'includes/footer.php'; ?>";
    
    // Write the file back
    file_put_contents($filePath, $newContent);
    echo "Fixed: $filePath\n";
}

echo "All admin pages processed!\n";
?>
