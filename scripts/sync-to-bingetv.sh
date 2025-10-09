#!/bin/bash

# Quick sync script for bingetv.co.ke
# Use this for quick updates without full deployment

LOCAL_DIR="/Users/la/Downloads/GStreaming"
REMOTE_HOST="bluehost"
REMOTE_DIR="/home1/fieldte5/bingetv.co.ke"

echo "🔄 Quick sync to bingetv.co.ke..."

# Sync only changed files (NO DELETE - only add/update)
rsync -avz \
    --exclude='.git' \
    --exclude='.DS_Store' \
    --exclude='*.log' \
    --exclude='node_modules' \
    --exclude='.env' \
    --exclude='deploy-*.sh' \
    --exclude='sync-*.sh' \
    --exclude='tests/' \
    $LOCAL_DIR/ $REMOTE_HOST:$REMOTE_DIR/

# Set permissions
ssh $REMOTE_HOST "chmod -R 755 $REMOTE_DIR/ && chmod 644 $REMOTE_DIR/*.php && chmod 644 $REMOTE_DIR/.htaccess"

echo "✅ Sync completed!"
echo ""
echo "📋 Next Steps:"
echo "1. Run migration: https://bingetv.co.ke/run_migration_010.php"
echo "2. Test subscription links from dashboard"
echo "3. Delete run_migration_010.php after migration"
echo ""
echo "🌐 Site: https://bingetv.co.ke"
