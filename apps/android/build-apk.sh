#!/bin/bash

# BingeTV Android APK Build Script
# Builds APK without Android Studio - uses Gradle command line

echo "=========================================="
echo "BingeTV Android APK Builder"
echo "=========================================="
echo ""

# Check if we're in the right directory
if [ ! -f "build.gradle" ]; then
    echo "❌ Error: build.gradle not found"
    echo "Please run this script from the apps/android directory"
    exit 1
fi

# Check for Java
if ! command -v java &> /dev/null; then
    echo "❌ Java not found. Please install JDK 8 or higher"
    echo "Download from: https://adoptium.net/"
    exit 1
fi

echo "Java version:"
java -version
echo ""

# Check for Android SDK
if [ -z "$ANDROID_HOME" ] && [ -z "$ANDROID_SDK_ROOT" ]; then
    echo "⚠️  ANDROID_HOME not set"
    echo ""
    echo "Please set ANDROID_HOME to your Android SDK location:"
    echo "  export ANDROID_HOME=/path/to/android/sdk"
    echo ""
    echo "Or create local.properties file with:"
    echo "  sdk.dir=/path/to/android/sdk"
    echo ""
    
    # Try to find SDK in common locations
    COMMON_PATHS=(
        "$HOME/Library/Android/sdk"
        "$HOME/Android/Sdk"
        "/opt/android-sdk"
    )
    
    for path in "${COMMON_PATHS[@]}"; do
        if [ -d "$path" ]; then
            echo "Found SDK at: $path"
            read -p "Use this SDK? (y/n) " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                export ANDROID_HOME="$path"
                echo "sdk.dir=$path" > local.properties
                break
            fi
        fi
    done
    
    if [ -z "$ANDROID_HOME" ]; then
        echo "❌ Android SDK not found. Please install Android SDK:"
        echo "  1. Download Android Command Line Tools:"
        echo "     https://developer.android.com/studio#command-tools"
        echo "  2. Extract and run:"
        echo "     sdkmanager 'platform-tools' 'platforms;android-34' 'build-tools;34.0.0'"
        exit 1
    fi
fi

# Check for Gradle
if ! command -v ./gradlew &> /dev/null && [ ! -f "gradlew" ]; then
    echo "📥 Gradle wrapper not found. Downloading..."
    # We'll need to download Gradle wrapper
    echo "Please download Gradle wrapper or install Gradle"
    exit 1
fi

# Make gradlew executable
if [ -f "gradlew" ]; then
    chmod +x gradlew
fi

echo "✅ Prerequisites check passed"
echo ""
echo "Building APK..."
echo ""

# Build debug APK (no signing required)
if [ -f "gradlew" ]; then
    ./gradlew assembleDebug
    BUILD_RESULT=$?
else
    gradle assembleDebug
    BUILD_RESULT=$?
fi

if [ $BUILD_RESULT -eq 0 ]; then
    echo ""
    echo "=========================================="
    echo "✅ APK Build Successful!"
    echo "=========================================="
    echo ""
    
    APK_PATH="app/build/outputs/apk/debug/app-debug.apk"
    if [ -f "$APK_PATH" ]; then
        APK_SIZE=$(du -h "$APK_PATH" | cut -f1)
        echo "📦 APK Location: $APK_PATH"
        echo "📏 APK Size: $APK_SIZE"
        echo ""
        echo "To build release APK (requires signing key):"
        echo "  ./gradlew assembleRelease"
        echo ""
        echo "To install on connected device:"
        echo "  adb install $APK_PATH"
        echo ""
        
        # Copy to public directory for download
        if [ -d "../../public/apps/android" ]; then
            mkdir -p ../../public/apps/android
            cp "$APK_PATH" ../../public/apps/android/BingeTV-debug.apk
            echo "✅ APK copied to public/apps/android/BingeTV-debug.apk"
        fi
    fi
else
    echo ""
    echo "❌ Build failed. Check errors above."
    exit 1
fi

