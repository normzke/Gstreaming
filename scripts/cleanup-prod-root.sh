#!/bin/bash
# Production Root Cleanup Script for BingeTV
# Moves redundant .php files from root to a backup directory to avoid routing conflicts

BACKUP_DIR="old_root_backup_$(date +%Y%m%d_%H%M%S)"

echo "Creating backup directory: $BACKUP_DIR"
mkdir -p "$BACKUP_DIR"

# List of files to move (based on typical root contamination)
FILES_TO_MOVE=(
    "channels.php"
    "packages.php"
    "gallery.php"
    "support.php"
    "login.php"
    "register.php"
    "index.php.bak"
    "test.php"
    "info.php"
    "check.php"
    "debug.php"
)

for file in "${FILES_TO_MOVE[@]}"; do
    if [ -f "$file" ]; then
        echo "Moving $file to $BACKUP_DIR"
        mv "$file" "$BACKUP_DIR/"
    fi
done

echo "Cleanup complete. Apache/LiteSpeed will now correctly route requests to /public or other subdirectories via .htaccess."
