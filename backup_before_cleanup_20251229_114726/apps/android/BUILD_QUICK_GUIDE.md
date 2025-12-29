# 🚀 Build Android TV APK - Quick Guide

## Step 1: Open Project in Android Studio

1. **Launch Android Studio**
2. Click **"Open"** (or File > Open)
3. Navigate to: `/Users/la/Downloads/Bingetv/apps/android`
4. Click **"OK"**

## Step 2: Wait for Setup (5-10 minutes first time)

Android Studio will automatically:
- ✅ Download Gradle wrapper
- ✅ Download SDK platforms
- ✅ Sync dependencies
- ✅ Index project

**Watch the progress bar at the bottom of Android Studio**

## Step 3: Build the APK

1. Go to menu: **Build > Build Bundle(s) / APK(s) > Build APK(s)**
2. Wait for build (watch bottom progress bar)
3. Look for notification: **"APK(s) generated successfully"**
4. Click **"locate"** in the notification

**APK Location:** `app/build/outputs/apk/debug/app-debug.apk`

## Step 4: Deploy APK

Run these commands in Terminal:

```bash
# Copy APK to deployment directory
cp /Users/la/Downloads/Bingetv/apps/android/app/build/outputs/apk/debug/app-debug.apk \
   /Users/la/Downloads/Bingetv/public/apps/android/bingetv-android-tv.apk

# Verify it was copied
ls -lh /Users/la/Downloads/Bingetv/public/apps/android/

# Check file size
du -h /Users/la/Downloads/Bingetv/public/apps/android/bingetv-android-tv.apk
```

## ✅ Success Checklist

After build completes:
- [ ] APK file exists at `app/build/outputs/apk/debug/app-debug.apk`
- [ ] File size is 5-20 MB
- [ ] No errors in Build tab
- [ ] APK copied to `public/apps/android/bingetv-android-tv.apk`
- [ ] **All 3 platforms ready!** (Android, WebOS, Tizen)

## 🔧 Troubleshooting

### "SDK not found"
1. File > Project Structure > SDK Location
2. Click "Download" next to Android SDK
3. Wait for download

### "Gradle sync failed"
1. File > Invalidate Caches / Restart
2. Click "Invalidate and Restart"

### Build errors
- Check "Build" tab at bottom for details
- Click any "Install" links in error messages

## 🎉 After Build

You'll have all 3 apps ready:
- ✅ Android TV (APK)
- ✅ LG WebOS (IPK) 
- ✅ Samsung Tizen (TPK)

Ready to upload to your website and go live! 🚀
