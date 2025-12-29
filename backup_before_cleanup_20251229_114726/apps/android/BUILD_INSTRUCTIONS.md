# Build Instructions for TiviMate Streamer APK

## Quick Start

### Option 1: Using Android Studio (Recommended)

1. **Install Android Studio**
   - Download from: https://developer.android.com/studio
   - Install with Android SDK and Android TV support

2. **Open the Project**
   - Launch Android Studio
   - Select "Open an existing project"
   - Navigate to `/Users/la/TiviMateStreamer` (or your project location)
   - Click "OK"

3. **Configure SDK**
   - If prompted, let Android Studio download required SDK components
   - Or manually: Tools > SDK Manager > Install Android SDK Platform 34

4. **Set up local.properties**
   - Copy `local.properties.example` to `local.properties`
   - Edit `local.properties` and set your SDK path:
     ```
     sdk.dir=/Users/YOUR_USERNAME/Library/Android/sdk
     ```
   - On Windows: `sdk.dir=C\:\\Users\\YOUR_USERNAME\\AppData\\Local\\Android\\Sdk`
   - On Linux: `sdk.dir=/home/YOUR_USERNAME/Android/Sdk`

5. **Sync Gradle**
   - Click "Sync Now" when prompted
   - Or: File > Sync Project with Gradle Files

6. **Build APK**
   - Build > Build Bundle(s) / APK(s) > Build APK(s)
   - Wait for build to complete
   - Click "locate" in the notification to find your APK
   - APK location: `app/build/outputs/apk/debug/app-debug.apk`

7. **Build Release APK** (for distribution)
   - Build > Generate Signed Bundle / APK
   - Select APK
   - Create a new keystore (or use existing)
   - Select release build variant
   - APK location: `app/build/outputs/apk/release/app-release.apk`

### Option 2: Using Command Line

1. **Prerequisites**
   ```bash
   # Install Java JDK 8 or higher
   # Install Android SDK
   # Add to PATH: ANDROID_HOME and platform-tools
   ```

2. **Set up local.properties**
   ```bash
   cd /Users/la/TiviMateStreamer
   cp local.properties.example local.properties
   # Edit local.properties with your SDK path
   ```

3. **Build Debug APK**
   ```bash
   ./gradlew assembleDebug
   # APK: app/build/outputs/apk/debug/app-debug.apk
   ```

4. **Build Release APK**
   ```bash
   ./gradlew assembleRelease
   # APK: app/build/outputs/apk/release/app-release.apk
   ```

## Installing on Android TV

### Method 1: ADB (Android Debug Bridge)

1. **Enable Developer Options on TV**
   - Settings > About > Click "Build" 7 times
   - Settings > Developer options > Enable "USB debugging"

2. **Connect TV to Computer**
   - Via USB cable, or
   - Via network (enable "Network debugging" in Developer options)

3. **Install via ADB**
   ```bash
   # Connect via USB
   adb devices  # Verify connection
   adb install app-release.apk
   
   # Or connect via network
   adb connect TV_IP_ADDRESS:5555
   adb install app-release.apk
   ```

### Method 2: USB Drive

1. **Copy APK to USB drive**
   ```bash
   cp app-release.apk /Volumes/USB_DRIVE/
   ```

2. **On TV**
   - Insert USB drive
   - Use file manager app to navigate to APK
   - Click to install
   - Allow "Unknown sources" if prompted

### Method 3: Network File Transfer

1. **Share APK via network**
   - Use file sharing service (Dropbox, Google Drive, etc.)
   - Or set up local file server

2. **Download on TV**
   - Use browser or file manager on TV
   - Download APK
   - Install when download completes

## Troubleshooting

### Build Errors

**Error: SDK not found**
- Solution: Check `local.properties` has correct `sdk.dir` path

**Error: Gradle sync failed**
- Solution: 
  - File > Invalidate Caches / Restart
  - Check internet connection (Gradle downloads dependencies)
  - Update Android Studio

**Error: Minimum SDK version**
- Solution: Install Android SDK Platform 21+ via SDK Manager

### Installation Errors

**Error: App not installed**
- Solution: 
  - Uninstall previous version first
  - Check TV has enough storage
  - Enable "Unknown sources" in Developer options

**Error: ADB device not found**
- Solution:
  - Check USB debugging is enabled
  - Try different USB cable/port
  - For network: Check TV and computer are on same network

## System Requirements

- **Development:**
  - Android Studio Arctic Fox or later
  - JDK 8 or higher
  - Android SDK Platform 21-34
  - 4GB+ RAM recommended

- **Target Device:**
  - Android TV (5.0+ / API 21+)
  - 2GB+ RAM
  - Network connection for streaming

## Notes

- Debug APKs are larger and include debug symbols
- Release APKs are optimized and smaller
- For distribution, use signed release APKs
- First launch may take longer (app initialization)

