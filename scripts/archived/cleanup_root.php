<?php
/**
 * Production Root Cleanup Bridge
 * Moves redundant .php files from root to a backup directory.
 */

$backupDir = 'old_root_backup_' . date('Ymd_His');
if (!mkdir($backupDir, 0755)) {
    die("Failed to create backup directory: $backupDir");
}

$filesToMove = [
    'channels.php',
    'packages.php',
    'gallery.php',
    'support.php',
    'login.php',
    'register.php',
    'index.php.bak',
    'test.php',
    'info.php',
    'check.php',
    'debug.php'
];

$moved = [];
$failed = [];

foreach ($filesToMove as $file) {
    if (file_exists($file) && is_file($file)) {
        if (rename($file, "$backupDir/$file")) {
            $moved[] = $file;
        } else {
            $failed[] = $file;
        }
    }
}

echo "Cleanup Result:\n";
echo "Backup Directory: $backupDir\n";
echo "Moved: " . implode(', ', $moved) . "\n";
echo "Failed: " . implode(', ', $failed) . "\n";
echo "\nDONE. Please delete this script.";
