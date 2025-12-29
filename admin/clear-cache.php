<?php
// Clear PHP OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared successfully!<br>";
} else {
    echo "ℹ️ OPcache not available<br>";
}

// Clear any other caches
if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "✅ APC cache cleared!<br>";
}

echo "<br>✅ Cache clearing complete!<br>";
echo "<br><a href='index.php'>← Back to Dashboard</a>";
?>