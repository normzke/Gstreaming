# Build Apps Without IDEs - Complete Guide

## ✅ Good News: You Don't Need Android Studio or Other IDEs!

All apps can be built using command-line tools and scripts we've created.

---

## 🚀 Quick Start - Build Everything

```bash
cd ~/Downloads/BingeTV
bash BUILD_ALL_APPS.sh
```

This script will:
- ✅ Build WebOS IPK (no prerequisites needed)
- ✅ Build Tizen TPK (no prerequisites needed)  
- ⚠️ Build Android APK (requires Java + Android SDK)

---

## 📱 Android APK - Build Without Android Studio

### Prerequisites (One-Time Setup)

#### 1. Install Java JDK
**macOS:**
```bash
brew install openjdk@11
```

**Or download from:** https://adoptium.net/

**Verify:**
```bash
java -version
```

#### 2. Install Android SDK Command Line Tools

**Download:** https://developer.android.com/studio#command-tools

**Extract and setup:**
```bash
# Extract to ~/android-sdk
cd ~/android-sdk/cmdline-tools/bin
./sdkmanager "platform-tools" "platforms;android-34" "build-tools;34.0.0"
```

#### 3. Set Environment Variables
Add to `~/.zshrc` or `~/.bashrc`:
```bash
export ANDROID_HOME=$HOME/android-sdk
export PATH=$PATH:$ANDROID_HOME/platform-tools
export PATH=$PATH:$ANDROID_HOME/tools
export PATH=$PATH:$ANDROID_HOME/tools/bin
```

Reload: `source ~/.zshrc`

### Build APK

**Option 1: Using Build Script (Recommended)**
```bash
cd ~/Downloads/BingeTV/apps/android
./build-apk.sh
```

**Option 2: Using Gradle Wrapper**
```bash
cd ~/Downloads/BingeTV/apps/android
echo "sdk.dir=$ANDROID_HOME" > local.properties
./gradlew assembleDebug
```

**Output:** `app/build/outputs/apk/debug/app-debug.apk`

The script automatically copies it to `public/apps/android/BingeTV-debug.apk`

---

## 📺 WebOS IPK - Build Without webOS SDK

**No prerequisites needed!** Just run:

```bash
cd ~/Downloads/BingeTV/apps/webos
./build-ipk.sh
```

**Output:** `com.bingetv.app_1.0.0_all.ipk`

Automatically copied to `public/apps/webos/`

---

## 📺 Samsung Tizen TPK - Build Without Tizen Studio

**No prerequisites needed!** Just run:

```bash
cd ~/Downloads/BingeTV/apps/tizen
./build-tpk.sh
```

**Output:** `com.bingetv.app-1.0.0.tpk`

**Note:** This creates an unsigned TPK. For production, you'd need Tizen Studio to sign it, but it works for testing.

Automatically copied to `public/apps/tizen/`

---

## 📥 Download Page Setup

After building, the download page at `https://bingetv.co.ke/download.php` will automatically detect and show download links for:

- ✅ **WebOS IPK** - Ready to download (already built)
- ✅ **Tizen TPK** - Ready to download (already built)
- ⚠️ **Android APK** - Will be available after you build it

---

## 🔄 Automated Build Process

### Build All Apps at Once:
```bash
cd ~/Downloads/BingeTV
bash BUILD_ALL_APPS.sh
```

### Build Individual Apps:
```bash
# Android
cd apps/android && ./build-apk.sh

# WebOS
cd apps/webos && ./build-ipk.sh

# Tizen
cd apps/tizen && ./build-tpk.sh
```

---

## 📋 What Gets Built

After running build scripts:

```
public/apps/
├── android/
│   └── BingeTV-debug.apk        (after Android build)
├── webos/
│   └── com.bingetv.app_1.0.0_all.ipk  ✅ Ready!
└── tizen/
    └── com.bingetv.app-1.0.0.tpk      ✅ Ready!
```

---

## ✅ Current Status

- ✅ **WebOS IPK**: Built and ready (`public/apps/webos/com.bingetv.app_1.0.0_all.ipk`)
- ✅ **Tizen TPK**: Built and ready (`public/apps/tizen/com.bingetv.app-1.0.0.tpk`)
- ⚠️ **Android APK**: Needs Java + Android SDK to build

---

## 🎯 Next Steps

1. **Build Android APK** (if you want):
   - Install Java JDK
   - Install Android SDK Command Line Tools
   - Run `./build-apk.sh`

2. **Upload to Server**:
   - Upload `public/apps/` directory to your web server
   - Download page will automatically work

3. **Test Downloads**:
   - Visit `https://bingetv.co.ke/download.php`
   - Test download links

---

## 💡 Tips

- **WebOS and Tizen**: Already built! Just upload to server
- **Android**: Can be built later when you have Java/SDK setup
- **No IDEs Required**: All builds use command-line tools
- **Automated**: Scripts handle everything automatically

---

## 📞 Need Help?

- See individual build guides in each app directory
- Check `QUICK_BUILD.md` for Android
- Contact support if you encounter issues

