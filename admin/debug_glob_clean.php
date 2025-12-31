<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h3>Glob Debugger</h3>";
echo "Current Dir: " . __DIR__ . "<br>";

$pattern = __DIR__ . '/../database/*.sql';
echo "Pattern: " . $pattern . "<br>";

if (function_exists('glob')) {
    echo "Glob function exists.<br>";
    $files = glob($pattern);
    if ($files === false) {
        echo "Glob failed (returned false).<br>";
    } else {
        echo "Glob success. Count: " . count($files) . "<br>";
        print_r($files);
    }
} else {
    echo "Glob function is DISABLED.<br>";
}
echo "<br>Done.";
