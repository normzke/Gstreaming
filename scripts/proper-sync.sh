#!/bin/bash

echo "=== PROPER SYNC TO BINGETV.CO.KE ==="
echo "Syncing files to correct directories..."
echo ""

REMOTE="bluehost:/home1/fieldte5/bingetv.co.ke"

# 1. Sync public directory
echo "1. Syncing public/ directory..."
rsync -avz --delete \
    --exclude='uploads/' \
    public/ $REMOTE/public/
echo "✅ Public directory synced"

# 2. Sync user directory  
echo ""
echo "2. Syncing user/ directory..."
rsync -avz --delete \
    user/ $REMOTE/user/
echo "✅ User directory synced"

# 3. Sync admin directory
echo ""
echo "3. Syncing admin/ directory..."
rsync -avz --delete \
    admin/ $REMOTE/admin/
echo "✅ Admin directory synced"

# 4. Sync config directory
echo ""
echo "4. Syncing config/ directory..."
rsync -avz config/ $REMOTE/config/
echo "✅ Config directory synced"

# 5. Sync lib directory
echo ""
echo "5. Syncing lib/ directory..."
rsync -avz lib/ $REMOTE/lib/
echo "✅ Lib directory synced"

# 6. Sync api directory
echo ""
echo "6. Syncing api/ directory..."
rsync -avz api/ $REMOTE/api/
echo "✅ API directory synced"

# 7. Sync root .htaccess
echo ""
echo "7. Syncing root .htaccess..."
rsync -avz .htaccess $REMOTE/.htaccess
echo "✅ Root .htaccess synced"

# 8. Clean up misplaced files at root
echo ""
echo "8. Cleaning up misplaced files..."
ssh bluehost "cd /home1/fieldte5/bingetv.co.ke && \
    rm -f channels.php gallery.php help.php privacy.php terms.php refund.php \
    register.php login.php support.php config.php email.php main.css header.php \
    subscribe.php subscribe_advanced.php process.php index.php 2>/dev/null; \
    echo 'Cleaned up root directory'"

# 9. Set proper permissions
echo ""
echo "9. Setting permissions..."
ssh bluehost "chmod -R 755 /home1/fieldte5/bingetv.co.ke/public \
    /home1/fieldte5/bingetv.co.ke/user \
    /home1/fieldte5/bingetv.co.ke/admin \
    /home1/fieldte5/bingetv.co.ke/api && \
    chmod 644 /home1/fieldte5/bingetv.co.ke/.htaccess \
    /home1/fieldte5/bingetv.co.ke/public/.htaccess \
    /home1/fieldte5/bingetv.co.ke/user/.htaccess \
    /home1/fieldte5/bingetv.co.ke/admin/.htaccess && \
    echo 'Permissions set'"

echo ""
echo "=== SYNC COMPLETE ==="
echo "✅ All files in correct locations"
echo "✅ Misplaced files cleaned up"
echo "✅ Permissions set correctly"
echo ""
echo "🌐 Site: https://bingetv.co.ke"

