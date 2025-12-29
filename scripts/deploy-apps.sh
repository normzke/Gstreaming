#!/bin/bash
# BingeTV - Deploy Apps to Production
# This script syncs all packaged apps to the production server

echo "🚀 BingeTV - Deploying Apps to Production"
echo "=========================================="
echo ""

# Configuration
REMOTE_HOST="bluehost"
REMOTE_PATH="/home1/fieldte5/bingetv.co.ke/apps/"
LOCAL_PATH="apps/"

# Check if apps are packaged
echo "📋 Checking packaged apps..."
echo ""

ANDROID_EXISTS=false
TIZEN_EXISTS=false
WEBOS_EXISTS=false

if [ -f "apps/android/bingetv-android-tv.apk" ]; then
    echo "✅ Android APK found ($(ls -lh apps/android/bingetv-android-tv.apk | awk '{print $5}'))"
    ANDROID_EXISTS=true
else
    echo "⚠️  Android APK not found"
fi

if [ -f "apps/tizen/com.bingetv.app-1.0.0.tpk" ]; then
    echo "✅ Tizen TPK found ($(ls -lh apps/tizen/com.bingetv.app-1.0.0.tpk | awk '{print $5}'))"
    TIZEN_EXISTS=true
else
    echo "⚠️  Tizen TPK not found"
fi

if [ -f "apps/webos/com.bingetv.app_1.0.0_all.ipk" ]; then
    echo "✅ WebOS IPK found ($(ls -lh apps/webos/com.bingetv.app_1.0.0_all.ipk | awk '{print $5}'))"
    WEBOS_EXISTS=true
else
    echo "⚠️  WebOS IPK not found"
fi

echo ""

# Confirm deployment
if [ "$ANDROID_EXISTS" = false ] && [ "$TIZEN_EXISTS" = false ] && [ "$WEBOS_EXISTS" = false ]; then
    echo "❌ No apps found to deploy!"
    echo "   Run: ./scripts/package-all-apps.sh first"
    exit 1
fi

read -p "🤔 Deploy to production? (y/n) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Deployment cancelled"
    exit 0
fi

echo ""
echo "📤 Deploying to $REMOTE_HOST:$REMOTE_PATH..."
echo ""

# Sync apps directory
rsync -avz --progress \
    --exclude 'node_modules' \
    --exclude '.git' \
    --exclude '.gradle' \
    --exclude 'build/intermediates' \
    --exclude '.idea' \
    --exclude '*.md' \
    $LOCAL_PATH $REMOTE_HOST:$REMOTE_PATH

if [ $? -eq 0 ]; then
    echo ""
    echo "=========================================="
    echo "✅ Deployment successful!"
    echo "=========================================="
    echo ""
    echo "📱 Download URLs:"
    
    if [ "$ANDROID_EXISTS" = true ]; then
        echo "   Android: https://bingetv.co.ke/apps/android/bingetv-android-tv.apk"
    fi
    
    if [ "$TIZEN_EXISTS" = true ]; then
        echo "   Tizen:   https://bingetv.co.ke/apps/tizen/com.bingetv.app-1.0.0.tpk"
    fi
    
    if [ "$WEBOS_EXISTS" = true ]; then
        echo "   WebOS:   https://bingetv.co.ke/apps/webos/com.bingetv.app_1.0.0_all.ipk"
    fi
    
    echo ""
    echo "🎉 Apps are now live on bingetv.co.ke!"
else
    echo ""
    echo "❌ Deployment failed!"
    exit 1
fi
