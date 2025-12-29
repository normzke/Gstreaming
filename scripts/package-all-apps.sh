#!/bin/bash
# BingeTV - Package All Apps Script
# This script packages all TV apps (Android, Tizen, WebOS) for distribution

echo "🚀 BingeTV - Packaging All Apps"
echo "================================"
echo ""

# Navigate to project root
cd "$(dirname "$0")/.."

# 1. Android APK
echo "📱 [1/3] Android TV APK..."
if [ -f "apps/android/app/build/outputs/apk/release/app-release.apk" ]; then
    cp apps/android/app/build/outputs/apk/release/app-release.apk apps/android/bingetv-android-tv.apk
    echo "✅ Android APK copied: apps/android/bingetv-android-tv.apk"
    ls -lh apps/android/bingetv-android-tv.apk
else
    echo "⚠️  Android APK not found. Please build the Android app first."
    echo "   Run: cd apps/android && ./gradlew assembleRelease"
fi
echo ""

# 2. Tizen TPK
echo "📺 [2/3] Samsung Tizen TPK..."
if [ -f "apps/tizen/package-tizen.sh" ]; then
    cd apps/tizen
    ./package-tizen.sh
    cd ../..
else
    echo "⚠️  Tizen packaging script not found"
fi
echo ""

# 3. WebOS IPK
echo "📺 [3/3] LG WebOS IPK..."
if [ -f "apps/webos/package-webos.sh" ]; then
    cd apps/webos
    ./package-webos.sh
    cd ../..
else
    echo "⚠️  WebOS packaging script not found"
fi
echo ""

# Summary
echo "================================"
echo "📦 Packaging Summary:"
echo "================================"
echo ""

if [ -f "apps/android/bingetv-android-tv.apk" ]; then
    echo "✅ Android: apps/android/bingetv-android-tv.apk"
else
    echo "❌ Android: Not packaged"
fi

if [ -f "apps/tizen/com.bingetv.app-1.0.0.tpk" ]; then
    echo "✅ Tizen: apps/tizen/com.bingetv.app-1.0.0.tpk"
else
    echo "❌ Tizen: Not packaged"
fi

if [ -f "apps/webos/com.bingetv.app_1.0.0_all.ipk" ]; then
    echo "✅ WebOS: apps/webos/com.bingetv.app_1.0.0_all.ipk"
else
    echo "❌ WebOS: Not packaged"
fi

echo ""
echo "🚀 Ready to deploy! Run: ./scripts/deploy-apps.sh"
