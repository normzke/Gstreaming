#!/bin/bash

echo "=== CLEAN DEPLOYMENT TO BINGETV.CO.KE ==="
echo ""

REMOTE_HOST="bluehost"
REMOTE_DIR="/home1/fieldte5/bingetv.co.ke"
LOCAL_DIR="/Users/la/Downloads/GStreaming"

echo "Step 1: Syncing to correct folder: $REMOTE_DIR"
echo ""

# Sync each directory individually to correct locations
echo "Syncing public/..."
rsync -avz --chmod=D755,F644 $LOCAL_DIR/public/ $REMOTE_HOST:$REMOTE_DIR/public/

echo ""
echo "Syncing user/..."
rsync -avz --chmod=D755,F644 $LOCAL_DIR/user/ $REMOTE_HOST:$REMOTE_DIR/user/

echo ""
echo "Syncing admin/..."
rsync -avz --chmod=D755,F644 $LOCAL_DIR/admin/ $REMOTE_HOST:$REMOTE_DIR/admin/

echo ""
echo "Syncing config/..."
rsync -avz --chmod=D755,F644 $LOCAL_DIR/config/ $REMOTE_HOST:$REMOTE_DIR/config/

echo ""
echo "Syncing lib/..."
rsync -avz --chmod=D755,F644 $LOCAL_DIR/lib/ $REMOTE_HOST:$REMOTE_DIR/lib/

echo ""
echo "Syncing api/..."
rsync -avz --chmod=D755,F644 $LOCAL_DIR/api/ $REMOTE_HOST:$REMOTE_DIR/api/

echo ""
echo "Syncing root .htaccess (ONLY root, no subdirs)..."
rsync -avz --chmod=644 $LOCAL_DIR/.htaccess $REMOTE_HOST:$REMOTE_DIR/.htaccess

echo ""
echo "Step 2: Removing subdirectory .htaccess files (they cause conflicts)..."
ssh $REMOTE_HOST "cd $REMOTE_DIR && rm -f public/.htaccess user/.htaccess admin/.htaccess && echo '✅ Removed subdirectory .htaccess'"

echo ""
echo "Step 3: Setting proper permissions..."
ssh $REMOTE_HOST "cd $REMOTE_DIR && \
    chmod 755 public user admin api config lib && \
    chmod 644 .htaccess && \
    chmod -R 755 public/css public/js public/images public/gateway && \
    chmod -R 755 user/css user/js user/images && \
    chmod 644 config/*.php lib/*.php && \
    chmod 755 public/*.php user/*.php admin/*.php && \
    echo '✅ Permissions set correctly'"

echo ""
echo "Step 4: Cleaning up confusing/backup folders..."
ssh $REMOTE_HOST "ls -la /home1/fieldte5/ | grep bingetv"

echo ""
read -p "Do you want to remove backup folders (bingetv.co.ke1, bingetv_backup_*)? (y/n) " -n 1 -r
echo
if [[ \$REPLY =~ ^[Yy]$ ]]
then
    ssh $REMOTE_HOST "rm -rf /home1/fieldte5/bingetv.co.ke1 /home1/fieldte5/bingetv_backup_* && echo '✅ Removed backup folders'"
fi

echo ""
echo "Step 5: Testing site..."
sleep 2
curl -I https://bingetv.co.ke/ 2>&1 | head -5
curl -I https://bingetv.co.ke/register.php 2>&1 | head -3
curl -I https://bingetv.co.ke/user/ 2>&1 | head -3

echo ""
echo "=== DEPLOYMENT COMPLETE ==="
echo "Site: https://bingetv.co.ke"
echo "All files synced to: $REMOTE_DIR"
echo "Permissions set correctly"

