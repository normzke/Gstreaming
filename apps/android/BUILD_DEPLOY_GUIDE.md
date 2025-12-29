# BingeTV v2.0 - Build & Deployment Guide

## 🚀 Quick Start

### Prerequisites
- Android Studio (latest version)
- Android SDK 34
- Java 17
- Android TV device or emulator

---

## 📦 Building the APK

### Method 1: Android Studio (Recommended)

1. **Open Project**
   ```bash
   cd /Users/la/Downloads/Bingetv/apps/android
   # Open this folder in Android Studio
   ```

2. **Sync Gradle**
   - Android Studio will automatically detect `build.gradle`
   - Click "Sync Now" when prompted
   - Wait for dependencies to download (~2-5 minutes)

3. **Build APK**
   - Menu: `Build → Build Bundle(s) / APK(s) → Build APK(s)`
   - Or use shortcut: `Ctrl+Shift+A` → type "Build APK"
   - Wait for build to complete

4. **Locate APK**
   ```
   app/build/outputs/apk/debug/app-debug.apk
   ```

### Method 2: Command Line

```bash
cd /Users/la/Downloads/Bingetv/apps/android

# Clean build
./gradlew clean

# Build debug APK
./gradlew assembleDebug

# APK location
ls -lh app/build/outputs/apk/debug/app-debug.apk
```

---

## 📱 Installing on Android TV

### Via ADB (Recommended)

1. **Enable ADB on Android TV**
   - Settings → Device Preferences → About
   - Click "Build" 7 times to enable Developer Mode
   - Settings → Device Preferences → Developer Options
   - Enable "USB Debugging"

2. **Connect via ADB**
   ```bash
   # Find TV IP address (Settings → Network)
   adb connect 192.168.1.XXX:5555
   
   # Verify connection
   adb devices
   ```

3. **Install APK**
   ```bash
   adb install -r app/build/outputs/apk/debug/app-debug.apk
   ```

### Via USB Drive

1. Copy APK to USB drive
2. Insert USB into Android TV
3. Use file manager app to navigate to USB
4. Click APK to install
5. Allow installation from unknown sources if prompted

### Via Download (from website)

1. **Copy APK to server**
   ```bash
   cp app/build/outputs/apk/debug/app-debug.apk ../../public/apps/android/bingetv-android-tv.apk
   ```

2. **Deploy to production**
   ```bash
   rsync -avz ../../public/apps/android/bingetv-android-tv.apk \
     bluehost:/home1/fieldte5/bingetv.co.ke/public/apps/android/
   ```

3. **Download on Android TV**
   - Open browser on TV
   - Go to `https://bingetv.co.ke/apps`
   - Download APK
   - Install

---

## 🧪 Testing Checklist

### First Launch
- [ ] App launches without crashes
- [ ] Splash screen displays
- [ ] Navigates to login screen

### Login Flow
- [ ] M3U URL input works
- [ ] Xtream Codes input works
- [ ] Invalid credentials show error
- [ ] Valid credentials proceed to main
- [ ] Remember me saves credentials

### Main Screen
- [ ] Channels load and display
- [ ] Category sidebar shows categories
- [ ] Category selection filters channels
- [ ] Channel logos load correctly
- [ ] Grid is responsive to D-pad

### Channel Playback
- [ ] Click channel starts playback
- [ ] Video plays smoothly
- [ ] Back button returns to grid
- [ ] Channel info displays correctly

### Search
- [ ] Search button opens dialog
- [ ] Real-time search works
- [ ] Results display correctly
- [ ] Click result plays channel

### Favorites
- [ ] Long-press shows context menu
- [ ] Add to favorites works
- [ ] Remove from favorites works
- [ ] Favorites category shows favorited channels
- [ ] Favorite icon displays on cards

### Settings
- [ ] Settings button opens settings
- [ ] Grid columns adjustment works
- [ ] Logout clears credentials
- [ ] Clear cache works
- [ ] About shows correct info

---

## 🐛 Troubleshooting

### Build Errors

**Error: SDK not found**
```bash
# Set ANDROID_HOME
export ANDROID_HOME=~/Library/Android/sdk
export PATH=$PATH:$ANDROID_HOME/tools:$ANDROID_HOME/platform-tools
```

**Error: Java version mismatch**
```bash
# Use Java 17
export JAVA_HOME=/Library/Java/JavaVirtualMachines/jdk-17.jdk/Contents/Home
```

**Error: Gradle sync failed**
```bash
# Clear Gradle cache
rm -rf ~/.gradle/caches/
./gradlew clean
```

### Runtime Errors

**Error: Cleartext HTTP not allowed**
- Already fixed in `network_security_config.xml`
- Ensure AndroidManifest includes the config

**Error: No channels found**
- Check M3U URL is valid
- Check internet connection
- Check credentials for Xtream Codes

**Error: Images not loading**
- Check internet connection
- Check logo URLs are valid
- Clear app cache in settings

---

## 🔧 Development Tips

### Enable Logging
```kotlin
// In ApiClient.kt, logging is already enabled
val logging = HttpLoggingInterceptor().apply {
    level = HttpLoggingInterceptor.Level.BODY
}
```

### View Logs
```bash
# Filter BingeTV logs
adb logcat | grep BingeTV

# View all logs
adb logcat
```

### Debug on TV
```bash
# Connect debugger
adb connect 192.168.1.XXX:5555

# Then run debug in Android Studio
```

---

## 📊 Performance Optimization

### Image Caching
- Glide automatically caches images
- Clear cache via Settings if needed

### Database Performance
- Room uses background threads
- LiveData updates UI automatically

### Network Performance
- 30-second timeout configured
- Retry logic in place
- Efficient parsing

---

## 🚢 Production Release

### 1. Update Version
```gradle
// In app/build.gradle
versionCode 2
versionName "2.0.0"
```

### 2. Generate Signed APK
1. Build → Generate Signed Bundle / APK
2. Select APK
3. Create new keystore or use existing
4. Fill in keystore details
5. Select "release" build variant
6. Build

### 3. Test Release APK
```bash
adb install -r app/build/outputs/apk/release/app-release.apk
```

### 4. Deploy to Server
```bash
cp app/build/outputs/apk/release/app-release.apk \
  ../../public/apps/android/bingetv-android-tv.apk
  
rsync -avz ../../public/apps/android/ \
  bluehost:/home1/fieldte5/bingetv.co.ke/public/apps/android/
```

---

## 📝 Version History

### v2.0.0 (Current)
- Complete UI redesign
- M3U + Xtream Codes support
- Search functionality
- Favorites system
- Settings management
- Context menu
- Enhanced performance

### v1.0.0 (Previous)
- Basic Leanback UI
- M3U support only
- Simple channel list

---

## 🆘 Support

### Common Issues

**Q: App crashes on launch**
A: Check Android version (min SDK 21), clear app data

**Q: No channels loading**
A: Verify M3U URL, check internet, check credentials

**Q: Images not showing**
A: Check internet connection, clear cache

**Q: Can't find app after install**
A: Check Apps list, or reinstall

### Contact
- Email: support@bingetv.co.ke
- Website: https://bingetv.co.ke

---

## ✅ Final Checklist

Before deploying to users:
- [ ] Build APK successfully
- [ ] Test all features
- [ ] Test on actual Android TV
- [ ] Verify M3U loading
- [ ] Verify Xtream loading
- [ ] Test search
- [ ] Test favorites
- [ ] Test settings
- [ ] Check performance
- [ ] Deploy to server
- [ ] Update website download link

---

**You're ready to build and deploy BingeTV v2.0!** 🎉
