# Quick APK Build Guide (No Android Studio Required)

## Prerequisites

### 1. Install Java JDK
Download and install JDK 8 or higher:
- **macOS**: `brew install openjdk@11` or download from https://adoptium.net/
- **Windows**: Download from https://adoptium.net/
- **Linux**: `sudo apt install openjdk-11-jdk` (Ubuntu/Debian)

### 2. Install Android SDK (Command Line Tools)

**Option A: Download Command Line Tools**
1. Download from: https://developer.android.com/studio#command-tools
2. Extract to a folder (e.g., `~/android-sdk`)
3. Run:
   ```bash
   cd ~/android-sdk/cmdline-tools/bin
   ./sdkmanager "platform-tools" "platforms;android-34" "build-tools;34.0.0"
   ```

**Option B: Use Homebrew (macOS)**
```bash
brew install --cask android-commandlinetools
```

### 3. Set Environment Variables
```bash
# Add to ~/.zshrc or ~/.bashrc
export ANDROID_HOME=$HOME/Library/Android/sdk  # or your SDK path
export PATH=$PATH:$ANDROID_HOME/platform-tools
export PATH=$PATH:$ANDROID_HOME/tools
export PATH=$PATH:$ANDROID_HOME/tools/bin
```

## Build APK

### Method 1: Using Build Script (Easiest)
```bash
cd ~/Downloads/BingeTV/apps/android
chmod +x build-apk.sh
./build-apk.sh
```

### Method 2: Using Gradle Wrapper Directly
```bash
cd ~/Downloads/BingeTV/apps/android

# Create local.properties if needed
echo "sdk.dir=$ANDROID_HOME" > local.properties

# Build debug APK
./gradlew assembleDebug

# APK will be at: app/build/outputs/apk/debug/app-debug.apk
```

### Method 3: Using Gradle (if installed)
```bash
cd ~/Downloads/BingeTV/apps/android
gradle assembleDebug
```

## Output

After successful build:
- **Debug APK**: `app/build/outputs/apk/debug/app-debug.apk`
- **Release APK** (requires signing): `app/build/outputs/apk/release/app-release.apk`

## Install on Device

```bash
# Connect device via USB or network
adb devices

# Install APK
adb install app/build/outputs/apk/debug/app-debug.apk
```

## Troubleshooting

**"SDK not found" error:**
- Create `local.properties` file with: `sdk.dir=/path/to/android/sdk`
- Or set `ANDROID_HOME` environment variable

**"Java not found" error:**
- Install JDK 8 or higher
- Set `JAVA_HOME` environment variable

**Build fails:**
- Check Android SDK is properly installed
- Verify `local.properties` has correct SDK path
- Ensure all dependencies are downloaded

