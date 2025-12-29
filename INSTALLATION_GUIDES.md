# BingeTV Installation Guides - All Platforms

Complete installation guides for Android TV, LG WebOS, and Samsung Tizen platforms.

---

## 📱 Android TV Installation Guide

### Prerequisites
- Android TV device (Android 5.0+ / API 21+)
- USB drive or ADB access
- Internet connection

### Method 1: ADB Installation (Recommended)

#### Step 1: Enable Developer Options
1. Go to **Settings** > **About**
2. Scroll to **Build** and click it **7 times**
3. Go back to **Settings** > **Developer options**
4. Enable **USB debugging** or **Network debugging**

#### Step 2: Connect to Computer
**Via USB:**
- Connect Android TV to computer via USB cable
- On TV, allow USB debugging when prompted

**Via Network:**
- Note your TV's IP address (Settings > Network)
- Enable Network debugging in Developer options
- On computer: `adb connect TV_IP_ADDRESS:5555`

#### Step 3: Install APK
```bash
# Download APK to your computer first
# Then install:
adb install BingeTV-release.apk

# Or if connected via network:
adb connect TV_IP_ADDRESS:5555
adb install BingeTV-release.apk
```

#### Step 4: Launch App
- Go to **Apps** on your Android TV
- Find **BingeTV** and launch it

### Method 2: USB Installation

#### Step 1: Prepare USB Drive
1. Format USB drive as **FAT32**
2. Copy `BingeTV-release.apk` to USB drive

#### Step 2: Enable Unknown Sources
1. Go to **Settings** > **Security & restrictions**
2. Enable **Unknown sources** or **Install unknown apps**
3. Select your file manager app and enable it

#### Step 3: Install from USB
1. Insert USB drive into Android TV
2. Open **File Manager** app
3. Navigate to USB drive
4. Find `BingeTV-release.apk`
5. Click to install
6. Follow on-screen prompts

### Method 3: Download from Website
1. Open browser on Android TV
2. Go to `https://bingetv.co.ke/download.php`
3. Click **Download for Android TV**
4. Open downloaded file
5. Install when prompted

### Troubleshooting

**"App not installed" error:**
- Ensure "Unknown sources" is enabled
- Check if TV has enough storage space
- Try uninstalling any previous version first
- Verify APK is not corrupted

**ADB connection issues:**
- Check USB cable is data-capable (not charge-only)
- Try different USB port
- For network: Ensure TV and computer are on same network
- Restart both devices

**App crashes:**
- Clear app data: Settings > Apps > BingeTV > Clear data
- Reinstall the app
- Check Android TV system updates

---

## 📺 LG Smart TV (WebOS) Installation Guide

### Prerequisites
- LG Smart TV with webOS 4.0 or higher
- USB drive or Developer Mode access
- Internet connection

### Method 1: Developer Mode Installation (Recommended)

#### Step 1: Enable Developer Mode
1. Go to **Settings** > **General** > **About This TV**
2. Click **Developer Mode** option **7 times**
3. Enter a PIN when prompted (remember this PIN)
4. Developer Mode will be enabled

#### Step 2: Get TV IP Address
1. Go to **Settings** > **Network** > **Wi-Fi** (or Ethernet)
2. Note the **IP Address** displayed

#### Step 3: Install via WebOS SDK (Computer Required)
1. **Download webOS TV SDK** from: https://webostv.developer.lge.com/sdk/installation
2. **Install SDK** on your computer
3. **Build IPK** (if you have source code):
   ```bash
   cd BingeTV-WebOS
   ares-package .
   ```
4. **Install IPK**:
   ```bash
   ares-install --device TV_IP_ADDRESS com.bingetv.app_1.0.0_all.ipk
   ```

#### Step 4: Launch App
- Press **Home** button on remote
- Find **BingeTV** in app list
- Launch the app

### Method 2: USB Installation

#### Step 1: Prepare USB Drive
1. Format USB drive as **FAT32**
2. Copy `com.bingetv.app_1.0.0_all.ipk` to USB drive

#### Step 2: Enable Developer Mode
- Follow Step 1 from Method 1 above

#### Step 3: Install from USB
1. Insert USB drive into TV
2. Open **LG Content Store**
3. Go to **My Apps** > **USB**
4. Select the IPK file
5. Click **Install**
6. Enter Developer Mode PIN if prompted

### Method 3: Download from Website
1. Open browser on LG TV
2. Go to `https://bingetv.co.ke/download.php`
3. Click **Download for LG TV**
4. Save file to USB or internal storage
5. Install via Content Store > My Apps

### Troubleshooting

**Developer Mode not working:**
- Ensure TV is connected to internet
- Try restarting TV
- Check webOS version (needs 4.0+)

**Installation fails:**
- Verify IPK file is not corrupted
- Check Developer Mode PIN is correct
- Ensure enough storage space
- Try different USB drive

**App doesn't appear:**
- Check Developer Mode is still enabled
- Restart TV
- Reinstall the app

---

## 📺 Samsung Smart TV (Tizen) Installation Guide

### Prerequisites
- Samsung Smart TV with Tizen 6.0 or higher
- USB drive or Developer Mode access
- Internet connection

### Method 1: Developer Mode Installation (Recommended)

#### Step 1: Enable Developer Mode
1. Go to **Settings** > **General** > **External Device Manager**
2. Select **Device Connection Manager**
3. Enable **Developer Mode**
4. Note your TV's **IP Address** (displayed on screen)

#### Step 2: Install Tizen Studio (Computer Required)
1. **Download Tizen Studio** from: https://developer.samsung.com/smarttv/develop/getting-started/setting-up-sdk.html
2. **Install Tizen Studio** with TV extension
3. **Set up Certificate**:
   - Open Tizen Studio
   - Go to **Tools** > **Certificate Manager**
   - Create new certificate profile
   - Follow wizard to generate certificate

#### Step 3: Build and Install TPK
1. **Open Project** in Tizen Studio:
   - File > Import > Tizen > Tizen Project
   - Select `BingeTV-Tizen` directory

2. **Build TPK**:
   - Right-click project > **Build Project**
   - Wait for build to complete

3. **Package TPK**:
   - Right-click project > **Tizen** > **Package** > **TPK**
   - Select your certificate profile
   - TPK will be created

4. **Install TPK**:
   - Right-click project > **Run As** > **Tizen Web App**
   - Or use Device Manager to install TPK

#### Step 4: Launch App
- Press **Home** button on remote
- Find **BingeTV** in app list
- Launch the app

### Method 2: USB Installation

#### Step 1: Prepare USB Drive
1. Format USB drive as **FAT32**
2. Copy `com.bingetv.app-1.0.0.tpk` to USB drive

#### Step 2: Enable Developer Mode
- Follow Step 1 from Method 1 above

#### Step 3: Install from USB
1. Insert USB drive into TV
2. Open **Smart Hub**
3. Go to **Apps** > **My Apps**
4. Select **USB** option
5. Find the TPK file
6. Click **Install**
7. Enter Developer Mode PIN if prompted

### Method 3: Using sdb (Command Line)

#### Step 1: Connect to TV
```bash
# Connect to TV via network
sdb connect TV_IP_ADDRESS

# Verify connection
sdb devices
```

#### Step 2: Install TPK
```bash
# Install TPK file
sdb install com.bingetv.app-1.0.0.tpk

# Launch app
sdb shell "launch_app com.bingetv.app"
```

### Method 4: Download from Website
1. Open browser on Samsung TV
2. Go to `https://bingetv.co.ke/download.php`
3. Click **Download for Samsung TV**
4. Save file to USB or internal storage
5. Install via Smart Hub > My Apps

### Troubleshooting

**Developer Mode issues:**
- Ensure TV is on same network as computer
- Check Tizen version (needs 6.0+)
- Restart TV and try again

**Certificate errors:**
- Recreate certificate in Tizen Studio
- Ensure certificate is not expired
- Check certificate profile is selected

**Installation fails:**
- Verify TPK file is not corrupted
- Check Developer Mode is enabled
- Ensure enough storage space
- Try different USB drive

**App doesn't launch:**
- Check logs: `sdb shell "cat /var/log/webapp/com.bingetv.app.log"`
- Reinstall the app
- Restart TV

---

## 🔧 General Troubleshooting

### Common Issues Across All Platforms

**App won't install:**
- Check device compatibility (minimum OS version)
- Verify file is not corrupted
- Ensure sufficient storage space
- Try restarting device

**Playback issues:**
- Check internet connection
- Verify playlist URL is accessible
- Try different channel
- Clear app cache/data

**Authentication problems:**
- Verify username/password
- Check MAC address format (XX:XX:XX:XX:XX:XX)
- Ensure account is active
- Contact support if issues persist

**Performance issues:**
- Close other apps
- Restart device
- Check network speed
- Update app to latest version

---

## 📞 Support

For additional help:
- **Website**: https://bingetv.co.ke/support.php
- **Email**: support@bingetv.co.ke
- **Phone**: +254 768 704 834

---

## 📝 Notes

- All apps require active internet connection
- Playlist URLs must be accessible from your network
- Some features may vary by platform
- Regular updates recommended for best experience

