# 📦 BingeTV App Packaging & Deployment Guide

## 🎯 Quick Start

### Package All Apps (One Command)
```bash
./scripts/package-all-apps.sh
```

### Deploy to Production
```bash
./scripts/deploy-apps.sh
```

---

## 📱 Individual App Packaging

### Android TV
**Already packaged!** ✅
- **File:** `apps/android/bingetv-android-tv.apk`
- **Size:** 8.3 MB
- **Download URL:** `https://bingetv.co.ke/apps/android/bingetv-android-tv.apk`

**To rebuild:**
```bash
cd apps/android
./gradlew assembleRelease
cp app/build/outputs/apk/release/app-release.apk bingetv-android-tv.apk
```

### Samsung Tizen
**Script created:** `apps/tizen/package-tizen.sh`

**Prerequisites:**
1. Install Tizen Studio: https://developer.tizen.org/development/tizen-studio/download
2. Add `tizen` CLI to PATH

**To package:**
```bash
cd apps/tizen
./package-tizen.sh
```

**Output:** `com.bingetv.app-1.0.0.tpk`

### LG WebOS
**Script created:** `apps/webos/package-webos.sh`

**Prerequisites:**
1. Install webOS TV SDK: https://webostv.developer.lge.com/sdk/installation/
2. Add `ares-package` to PATH

**To package:**
```bash
cd apps/webos
./package-webos.sh
```

**Output:** `com.bingetv.app_1.0.0_all.ipk`

---

## 🚀 Deployment

### Automatic Deployment
```bash
./scripts/deploy-apps.sh
```

This will:
1. ✅ Check for packaged apps
2. 📤 Sync to production server
3. 🎉 Display download URLs

### Manual Deployment
```bash
rsync -avz apps/ bluehost:/home1/fieldte5/bingetv.co.ke/apps/
```

---

## 📥 Download URLs

Once deployed, apps will be available at:

- **Android TV:** `https://bingetv.co.ke/apps/android/bingetv-android-tv.apk`
- **Samsung Tizen:** `https://bingetv.co.ke/apps/tizen/com.bingetv.app-1.0.0.tpk`
- **LG WebOS:** `https://bingetv.co.ke/apps/webos/com.bingetv.app_1.0.0_all.ipk`

---

## 🔧 Installation on TVs

### Android TV
1. Enable "Unknown Sources" in Settings
2. Download APK from URL above
3. Install using file manager

### Samsung Tizen
```bash
# Connect to TV
tizen connect <TV_IP>

# Install app
tizen install -n com.bingetv.app-1.0.0.tpk -t <TV_IP>
```

### LG WebOS
```bash
# Setup device
ares-setup-device

# Install app
ares-install --device <TV_NAME> com.bingetv.app_1.0.0_all.ipk
```

---

## 📋 File Structure

```
apps/
├── android/
│   ├── bingetv-android-tv.apk ✅ (8.3 MB)
│   └── app/build/outputs/apk/release/app-release.apk
├── tizen/
│   ├── package-tizen.sh ✅
│   ├── config.xml (auto-generated)
│   └── com.bingetv.app-1.0.0.tpk (after packaging)
└── webos/
    ├── package-webos.sh ✅
    ├── appinfo.json (auto-generated)
    └── com.bingetv.app_1.0.0_all.ipk (after packaging)
```

---

## ✅ Current Status

- ✅ **Android APK:** Ready (8.3 MB)
- ⏳ **Tizen TPK:** Script ready (needs Tizen Studio)
- ⏳ **WebOS IPK:** Script ready (needs webOS SDK)

---

## 🎉 Next Steps

1. **Install SDKs** (if packaging Tizen/WebOS):
   - Tizen Studio for Samsung TVs
   - webOS TV SDK for LG TVs

2. **Package remaining apps:**
   ```bash
   ./scripts/package-all-apps.sh
   ```

3. **Deploy to production:**
   ```bash
   ./scripts/deploy-apps.sh
   ```

4. **Test downloads:**
   - Visit https://bingetv.co.ke/apps
   - Download and install on test devices

---

**All scripts are executable and ready to use!** 🚀
