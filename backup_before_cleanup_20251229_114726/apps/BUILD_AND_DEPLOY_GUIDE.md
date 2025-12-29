# BingeTV Apps - Complete Build & Deployment Guide

## 🚀 Quick Start

This guide will help you build all BingeTV streaming apps for different TV platforms and deploy them to your website.

## Prerequisites

### For All Platforms
- Git installed
- Text editor (VS Code recommended)
- Web server access (for deployment)

### For Android TV
- Android Studio (latest version)
- Java JDK 11 or higher
- Android SDK (API 21+)

### For WebOS (LG Smart TV)
- Node.js (v14 or higher)
- webOS TV SDK CLI
- LG Developer Account (for app store submission)

### For Samsung Tizen
- Tizen Studio
- Samsung Developer Account (for app store submission)

## 📱 Building the Apps

### 1. Android TV App

#### Step 1: Setup Android Studio
```bash
# Download Android Studio from:
# https://developer.android.com/studio

# Install and open Android Studio
# Install Android SDK Platform 21-34
```

#### Step 2: Open Project
```bash
cd /Users/la/Downloads/Bingetv/apps/android
# Open this folder in Android Studio
```

#### Step 3: Configure
```bash
# Copy local.properties.example to local.properties
cp local.properties.example local.properties

# Edit local.properties and set your SDK path:
# sdk.dir=/Users/YOUR_USERNAME/Library/Android/sdk
```

#### Step 4: Update App Configuration
Edit `app/src/main/res/values/strings.xml`:
```xml
<string name="app_name">BingeTV</string>
<string name="api_base_url">https://bingetv.co.ke</string>
```

#### Step 5: Build APK
```bash
# Using Gradle (command line)
./gradlew assembleRelease

# Or in Android Studio:
# Build > Build Bundle(s) / APK(s) > Build APK(s)

# APK will be at:
# app/build/outputs/apk/release/app-release.apk
```

#### Step 6: Sign APK (Optional but Recommended)
```bash
# Generate keystore
keytool -genkey -v -keystore bingetv-release-key.jks \
  -keyalg RSA -keysize 2048 -validity 10000 \
  -alias bingetv

# Sign APK
jarsigner -verbose -sigalg SHA256withRSA -digestalg SHA-256 \
  -keystore bingetv-release-key.jks \
  app/build/outputs/apk/release/app-release.apk bingetv
```

### 2. WebOS (LG Smart TV) App

#### Step 1: Install webOS SDK
```bash
# Download from:
# https://webostv.developer.lge.com/sdk/installation

# Or install via npm
npm install -g @webosose/ares-cli
```

#### Step 2: Configure App
```bash
cd /Users/la/Downloads/Bingetv/apps/webos

# Edit appinfo.json and update:
# - id: com.bingetv.app
# - version: 1.0.0
# - title: BingeTV
```

#### Step 3: Update API URL
Edit `js/app.js` and set:
```javascript
const API_BASE_URL = 'https://bingetv.co.ke';
```

#### Step 4: Build IPK
```bash
cd /Users/la/Downloads/Bingetv/apps/webos

# Package the app
ares-package .

# IPK will be created:
# com.bingetv.app_1.0.0_all.ipk
```

#### Step 5: Test on LG TV (Optional)
```bash
# Enable Developer Mode on your LG TV
# Settings > General > About This TV > Developer Mode

# Add device
ares-setup-device

# Install on TV
ares-install --device YOUR_TV_NAME com.bingetv.app_1.0.0_all.ipk

# Launch app
ares-launch --device YOUR_TV_NAME com.bingetv.app
```

### 3. Samsung Tizen App

#### Step 1: Install Tizen Studio
```bash
# Download from:
# https://developer.samsung.com/smarttv/develop/getting-started/setting-up-sdk.html

# Install Tizen Studio with TV extension
```

#### Step 2: Configure App
```bash
cd /Users/la/Downloads/Bingetv/apps/tizen

# Edit config.xml and update:
# - id: com.bingetv.app
# - version: 1.0.0
# - name: BingeTV
```

#### Step 3: Update API URL
Edit `js/app.js` and set:
```javascript
const API_BASE_URL = 'https://bingetv.co.ke';
```

#### Step 4: Build TPK
```bash
# Using Tizen Studio:
# 1. Import project into Tizen Studio
# 2. Right-click project > Build Project
# 3. Right-click project > Tizen > Package > TPK

# Or using CLI:
tizen package -t tpk -s YOUR_CERTIFICATE_PROFILE

# TPK will be created:
# com.bingetv.app-1.0.0.tpk
```

## 🌐 Website Integration

### Step 1: Create Apps Directory
```bash
cd /Users/la/Downloads/Bingetv/public
mkdir -p apps/android apps/webos apps/tizen
```

### Step 2: Copy Built Apps
```bash
# Copy Android APK
cp /Users/la/Downloads/Bingetv/apps/android/app/build/outputs/apk/release/app-release.apk \
   /Users/la/Downloads/Bingetv/public/apps/android/bingetv-android-tv.apk

# Copy WebOS IPK
cp /Users/la/Downloads/Bingetv/apps/webos/com.bingetv.app_1.0.0_all.ipk \
   /Users/la/Downloads/Bingetv/public/apps/webos/

# Copy Tizen TPK
cp /Users/la/Downloads/Bingetv/apps/tizen/com.bingetv.app-1.0.0.tpk \
   /Users/la/Downloads/Bingetv/public/apps/tizen/
```

### Step 3: Create Download Page
Create `/Users/la/Downloads/Bingetv/public/apps.php` (see next section)

### Step 4: Update Navigation
Add link to main navigation in `index.php`:
```php
<a href="apps.php">Download Apps</a>
```

## 📥 Download Page Features

The download page should include:
- ✅ Automatic TV platform detection
- ✅ Direct download links for all platforms
- ✅ Installation instructions
- ✅ QR codes for easy mobile download
- ✅ System requirements
- ✅ Troubleshooting guide

## 🔐 User Credential Flow

### Admin Creates User
1. Admin logs into `/admin/streaming-users.php`
2. Clicks "Create New User"
3. Enters:
   - Username
   - Password
   - Email
   - Subscription Tier (Basic/Standard/Premium/Family)
   - Device Limit
4. System auto-generates:
   - Streaming Token
   - Playlist URL: `https://bingetv.co.ke/api/playlist.php?token=XXXXX`

### User Receives Credentials
Email template sent to user:
```
Welcome to BingeTV!

Your streaming credentials:
Username: john_doe
Password: ********
Streaming URL: https://bingetv.co.ke/api/playlist.php?token=abc123...

Download our apps:
- Android TV: https://bingetv.co.ke/apps/android/bingetv-android-tv.apk
- LG WebOS: https://bingetv.co.ke/apps/webos/com.bingetv.app_1.0.0_all.ipk
- Samsung Tizen: https://bingetv.co.ke/apps/tizen/com.bingetv.app-1.0.0.tpk

Installation guides: https://bingetv.co.ke/apps.php
```

### User Installs & Uses App
1. User downloads app for their TV platform
2. Installs app on TV
3. Opens app
4. Enters username & password
5. App authenticates with backend
6. App loads personalized playlist
7. User starts streaming!

## 🔧 Configuration Files

### API Base URL
Update in all apps:
- **Android**: `app/src/main/res/values/strings.xml`
- **WebOS**: `js/app.js`
- **Tizen**: `js/app.js`

Set to: `https://bingetv.co.ke`

### App Icons
Replace default icons:
- **Android**: `app/src/main/res/mipmap-*/ic_launcher.png`
- **WebOS**: `icon.png` (320x320)
- **Tizen**: `icon.png` (117x117)

### App Names
Update app display names:
- **Android**: `app/src/main/res/values/strings.xml`
- **WebOS**: `appinfo.json`
- **Tizen**: `config.xml`

## 📊 Testing Checklist

### Before Deployment
- [ ] All apps build without errors
- [ ] API endpoints are accessible
- [ ] Database is setup (run `setup_streaming_database.php`)
- [ ] Test user created in admin panel
- [ ] Playlist URL generates valid M3U
- [ ] Apps can authenticate
- [ ] Streaming works on all platforms

### After Deployment
- [ ] Download links work
- [ ] Apps install successfully
- [ ] Login works
- [ ] Channels load
- [ ] Streaming playback works
- [ ] All features functional

## 🚨 Troubleshooting

### Android TV App Won't Install
- Enable "Unknown Sources" in TV settings
- Check APK is not corrupted
- Ensure Android version is 5.0+

### WebOS App Won't Install
- Enable Developer Mode on TV
- Check IPK file integrity
- Verify webOS version is 4.0+

### Tizen App Won't Install
- Enable Developer Mode on TV
- Check TPK is properly signed
- Verify Tizen version is 6.0+

### Streaming Doesn't Work
- Check internet connection
- Verify playlist URL is accessible
- Ensure user account is active
- Check stream URLs are valid

### Authentication Fails
- Verify credentials are correct
- Check user is active in database
- Ensure API endpoint is accessible
- Check streaming token is valid

## 📈 Next Steps

### Phase 1: Initial Launch
1. ✅ Build all apps
2. ✅ Setup database
3. ✅ Create test users
4. ✅ Deploy to website
5. ✅ Test thoroughly

### Phase 2: Content Addition
1. Add channels to database
2. Configure channel categories
3. Add channel logos
4. Test playlist generation
5. Verify streaming quality

### Phase 3: User Onboarding
1. Create user documentation
2. Setup email templates
3. Create video tutorials
4. Launch marketing campaign
5. Monitor user feedback

### Phase 4: App Store Submission
1. Prepare store listings
2. Create promotional materials
3. Submit to Google Play (Android TV)
4. Submit to LG Content Store (WebOS)
5. Submit to Samsung Apps (Tizen)

## 📞 Support

For issues or questions:
- Check documentation in `/docs`
- Review app README files
- Contact: support@bingetv.co.ke

---

**Last Updated**: 2025-12-28
**Version**: 1.0.0
