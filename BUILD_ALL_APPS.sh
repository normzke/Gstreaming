#!/bin/bash

# BingeTV - Build All Apps Script
# Builds APK, IPK, and TPK without requiring IDEs

echo "=========================================="
echo "BingeTV - Build All Apps"
echo "=========================================="
echo ""
echo "This will build apps for all platforms:"
echo "  ✓ Android TV (APK)"
echo "  ✓ LG WebOS (IPK)"
echo "  ✓ Samsung Tizen (TPK)"
echo ""
read -p "Press Enter to continue or Ctrl+C to cancel..."

BASE_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$BASE_DIR"

# Build WebOS IPK
echo ""
echo "=========================================="
echo "Building WebOS IPK..."
echo "=========================================="
cd apps/webos
if [ -f "build-ipk.sh" ]; then
    bash build-ipk.sh
else
    echo "❌ build-ipk.sh not found"
fi
cd "$BASE_DIR"

# Build Tizen TPK
echo ""
echo "=========================================="
echo "Building Tizen TPK..."
echo "=========================================="
cd apps/tizen
if [ -f "build-tpk.sh" ]; then
    bash build-tpk.sh
else
    echo "❌ build-tpk.sh not found"
fi
cd "$BASE_DIR"

# Build Android APK (if prerequisites met)
echo ""
echo "=========================================="
echo "Building Android APK..."
echo "=========================================="
cd apps/android
if [ -f "build-apk.sh" ]; then
    echo "Note: Android APK build requires Java and Android SDK"
    echo "Checking prerequisites..."
    
    if command -v java &> /dev/null; then
        echo "✅ Java found"
        if [ -n "$ANDROID_HOME" ] || [ -f "local.properties" ]; then
            echo "✅ Android SDK configured"
            bash build-apk.sh
        else
            echo "⚠️  Android SDK not configured"
            echo "   Set ANDROID_HOME or create local.properties"
            echo "   See: apps/android/QUICK_BUILD.md"
        fi
    else
        echo "⚠️  Java not found"
        echo "   Install JDK 8+ to build Android APK"
        echo "   See: apps/android/QUICK_BUILD.md"
    fi
else
    echo "❌ build-apk.sh not found"
fi
cd "$BASE_DIR"

# Summary
echo ""
echo "=========================================="
echo "Build Summary"
echo "=========================================="
echo ""

# Check what was built
BUILT_APPS=()

if [ -f "public/apps/webos/com.bingetv.app_1.0.0_all.ipk" ]; then
    BUILT_APPS+=("✅ WebOS IPK")
    echo "✅ WebOS IPK: public/apps/webos/com.bingetv.app_1.0.0_all.ipk"
fi

if [ -f "public/apps/tizen/com.bingetv.app-1.0.0.tpk" ]; then
    BUILT_APPS+=("✅ Tizen TPK")
    echo "✅ Tizen TPK: public/apps/tizen/com.bingetv.app-1.0.0.tpk"
fi

if [ -f "public/apps/android/BingeTV-debug.apk" ]; then
    BUILT_APPS+=("✅ Android APK")
    echo "✅ Android APK: public/apps/android/BingeTV-debug.apk"
else
    echo "⚠️  Android APK: Not built (requires Java + Android SDK)"
fi

echo ""
echo "Built apps: ${#BUILT_APPS[@]}/3"
echo ""
echo "📥 Download page: https://bingetv.co.ke/download.php"
echo ""

