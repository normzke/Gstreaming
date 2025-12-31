#!/bin/bash

# Configuration
REMOTE_USER=fieldte5
REMOTE_HOST=bluehost
REMOTE_DIR=/home1/fieldte5/bingetv.co.ke
LOCAL_DIR=/Users/la/Downloads/Bingetv

# Set up SSH options
SSH_OPTS="-o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null"

echo "=== Starting Clean Deployment ==="

# 1. Backup important files on remote server
echo "Backing up important files..."
ssh $SSH_OPTS $REMOTE_USER@$REMOTE_HOST "
  mkdir -p $REMOTE_DIR/backup
  cp -r $REMOTE_DIR/public $REMOTE_DIR/backup/ 2>/dev/null || true
  cp $REMOTE_DIR/.htaccess $REMOTE_DIR/backup/ 2>/dev/null || true
  cp $REMOTE_DIR/config/*.php $REMOTE_DIR/backup/ 2>/dev/null || true
"

# 2. Clean remote directory (except backup)
echo "Cleaning remote directory..."
ssh $SSH_OPTS $REMOTE_USER@$REMOTE_HOST "
  find $REMOTE_DIR -mindepth 1 -not -path '$REMOTE_DIR/backup*' -exec rm -rf {} + 2>/dev/null || true
  mkdir -p $REMOTE_DIR/{public,config,includes,lib,admin,user,api,logs}
"

# 3. Copy files to remote server
echo "Copying files to remote server..."
rsync -avz --delete \
  --exclude='.git' \
  --exclude='.DS_Store' \
  --exclude='.idea' \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='*.log' \
  $LOCAL_DIR/ $REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/

# 4. Set proper permissions
echo "Setting permissions..."
ssh $SSH_OPTS $REMOTE_USER@$REMOTE_HOST "
  chmod -R 755 $REMOTE_DIR
  chmod -R 644 $REMOTE_DIR/*.php
  chmod -R 644 $REMOTE_DIR/public/*.php
  chmod -R 755 $REMOTE_DIR/public/{css,js,images,assets,uploads}
  chmod 777 $REMOTE_DIR/logs 2>/dev/null || true
"

echo "=== Deployment Complete ==="
echo "Please check the website: https://bingetv.co.ke"
