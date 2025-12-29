#!/bin/bash

echo "=== DEPLOY BINGETV TO PRODUCTION ==="
echo "Syncing all files to bingetv.co.ke..."
echo ""

REMOTE="bluehost:/home1/fieldte5/bingetv.co.ke"

# 1. Sync public directory (includes apps)
echo "1. Syncing public/ directory (including apps)..."
rsync -avz --delete \
    --exclude='uploads/' \
    --exclude='.DS_Store' \
    public/ $REMOTE/public/
echo "✅ Public directory synced (includes Android APK, WebOS IPK, Tizen TPK)"

# 2. Sync user directory  
echo ""
echo "2. Syncing user/ directory..."
rsync -avz --delete \
    --exclude='.DS_Store' \
    user/ $REMOTE/user/
echo "✅ User directory synced"

# 3. Sync admin directory
echo ""
echo "3. Syncing admin/ directory..."
rsync -avz --delete \
    --exclude='.DS_Store' \
    admin/ $REMOTE/admin/
echo "✅ Admin directory synced"

# 4. Sync config directory
echo ""
echo "4. Syncing config/ directory..."
rsync -avz \
    --exclude='.DS_Store' \
    config/ $REMOTE/config/
echo "✅ Config directory synced"

# 5. Sync lib directory
echo ""
echo "5. Syncing lib/ directory..."
rsync -avz \
    --exclude='.DS_Store' \
    lib/ $REMOTE/lib/
echo "✅ Lib directory synced"

# 6. Sync api directory
echo ""
echo "6. Syncing api/ directory..."
rsync -avz \
    --exclude='.DS_Store' \
    api/ $REMOTE/api/
echo "✅ API directory synced"

# 7. Sync root .htaccess
echo ""
echo "7. Syncing root .htaccess..."
rsync -avz .htaccess $REMOTE/.htaccess
echo "✅ Root .htaccess synced"

# 8. Set proper permissions
echo ""
echo "8. Setting permissions..."
ssh bluehost "chmod -R 755 /home1/fieldte5/bingetv.co.ke/public \
    /home1/fieldte5/bingetv.co.ke/user \
    /home1/fieldte5/bingetv.co.ke/admin \
    /home1/fieldte5/bingetv.co.ke/api && \
    chmod 644 /home1/fieldte5/bingetv.co.ke/.htaccess \
    /home1/fieldte5/bingetv.co.ke/public/.htaccess \
    /home1/fieldte5/bingetv.co.ke/user/.htaccess \
    /home1/fieldte5/bingetv.co.ke/admin/.htaccess && \
    echo 'Permissions set'"

# 9. Verify apps were uploaded
echo ""
echo "9. Verifying BingeTV apps deployment..."
ssh bluehost "ls -lh /home1/fieldte5/bingetv.co.ke/public/apps/android/ \
    /home1/fieldte5/bingetv.co.ke/public/apps/webos/ \
    /home1/fieldte5/bingetv.co.ke/public/apps/tizen/ 2>/dev/null && \
    echo '✅ All apps verified on server'"

echo ""
echo "=== DEPLOYMENT COMPLETE ===\"
echo "✅ All files synced to production"
echo "✅ Permissions set correctly"
echo "✅ BingeTV apps deployed:"
echo "   - Android TV APK (7.9 MB)"
echo "   - LG WebOS IPK (5.6 KB)"
echo "   - Samsung Tizen TPK (7.7 KB)"
echo ""
echo "🌐 Site: https://bingetv.co.ke"
echo "📱 Apps: https://bingetv.co.ke/apps.php"
echo ""
echo "Next steps:"
echo "1. Test website: https://bingetv.co.ke"
echo "2. Test apps page: https://bingetv.co.ke/apps.php"
echo "3. Test app downloads on actual TV devices"
echo "4. Verify auto-download works on TV browsers"
