#!/bin/bash

# BingeTV Complete Sync Script
# Syncs all files to the remote server

REMOTE_USER="fieldte5"
REMOTE_HOST="bingetv.co.ke"
REMOTE_PATH="/home1/fieldte5/bingetv.co.ke/"
LOCAL_PATH="/Users/la/Downloads/GStreaming/"

echo "=== Starting BingeTV Complete Sync ==="

# Sync root .htaccess
echo "Syncing root .htaccess..."
rsync -avz --progress "${LOCAL_PATH}.htaccess" "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}.htaccess"

# Sync config files
echo "Syncing config files..."
rsync -avz --progress "${LOCAL_PATH}config/" "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}config/"

# Sync lib files
echo "Syncing lib files..."
rsync -avz --progress "${LOCAL_PATH}lib/" "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}lib/"

# Sync public directory
echo "Syncing public directory..."
rsync -avz --progress "${LOCAL_PATH}public/" "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}public/"

# Sync user directory
echo "Syncing user directory..."
rsync -avz --progress "${LOCAL_PATH}user/" "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}user/"

# Sync admin directory
echo "Syncing admin directory..."
rsync -avz --progress "${LOCAL_PATH}admin/" "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}admin/"

# Sync database migrations
echo "Syncing database migrations..."
rsync -avz --progress "${LOCAL_PATH}database/" "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}database/"

# Sync API directory
echo "Syncing API directory..."
rsync -avz --progress "${LOCAL_PATH}api/" "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}api/"

echo ""
echo "=== Setting Permissions ==="
ssh "${REMOTE_USER}@${REMOTE_HOST}" << 'ENDSSH'
cd /home1/fieldte5/bingetv.co.ke/

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make scripts executable
chmod 755 scripts/*.sh 2>/dev/null || true

# Set .htaccess permissions
chmod 644 .htaccess

echo "✅ Permissions set"
ENDSSH

echo ""
echo "=== Sync Complete ==="
echo "Next step: Run database migration 010"

