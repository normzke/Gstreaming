# BingeTV Quick Start - No IDEs Required!

## ✅ What's Ready Right Now

- **WebOS IPK**: ✅ Built and ready (`public/apps/webos/com.bingetv.app_1.0.0_all.ipk`)
- **Tizen TPK**: ✅ Built and ready (`public/apps/tizen/com.bingetv.app-1.0.0.tpk`)
- **Android APK**: ⚠️ Needs building (see below)

## 🚀 Build Android APK (5 Minutes)

```bash
# 1. Install Java (if not installed)
brew install openjdk@11

# 2. Download Android SDK Command Line Tools
# From: https://developer.android.com/studio#command-tools

# 3. Install SDK components
cd ~/android-sdk/cmdline-tools/bin
./sdkmanager "platform-tools" "platforms;android-34" "build-tools;34.0.0"

# 4. Set environment
export ANDROID_HOME=$HOME/android-sdk

# 5. Build APK
cd ~/Downloads/BingeTV/apps/android
./build-apk.sh
```

Done! APK will be at `public/apps/android/BingeTV-debug.apk`

## 📥 Download Page

Visit: `https://bingetv.co.ke/download.php`

- Automatically detects your TV platform
- Shows download buttons for available apps
- WebOS and Tizen ready now!

## 🎯 All Build Scripts Ready

- `BUILD_ALL_APPS.sh` - Build everything
- `apps/android/build-apk.sh` - Android only
- `apps/webos/build-ipk.sh` - WebOS only (already built)
- `apps/tizen/build-tpk.sh` - Tizen only (already built)

No IDEs needed! 🎉
