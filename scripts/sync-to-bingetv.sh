#!/bin/bash

# Quick sync script for bingetv.co.ke
# Use this for quick updates without full deployment

LOCAL_DIR="/Users/la/Downloads/GStreaming"
REMOTE_HOST="bluehost"
REMOTE_DIR="/home1/fieldte5/bingetv.co.ke"

echo "🔄 Quick sync to bingetv.co.ke..."

# Sync only changed files
rsync -avz --delete \
    --exclude='.git' \
    --exclude='.DS_Store' \
    --exclude='*.log' \
    --exclude='node_modules' \
    --exclude='.env' \
    --exclude='deploy-*.sh' \
    --exclude='sync-*.sh' \
    --exclude='*.md' \
    --exclude='tests/' \
    --exclude='database/migrations/' \
    $LOCAL_DIR/ $REMOTE_HOST:$REMOTE_DIR/

# Set permissions
ssh $REMOTE_HOST "chmod -R 755 $REMOTE_DIR/"

echo "✅ Sync completed!"
echo "🌐 Site: https://bingetv.co.ke"
