# Platform Compatibility Guide

## Important Note About APK Files

**APK files are Android-specific** and can only run on:
- ✅ Android devices (phones, tablets)
- ✅ Android TV devices
- ✅ Android-based set-top boxes (NVIDIA Shield, Mi Box, etc.)

APK files **CANNOT** run on:
- ❌ WebOS (LG Smart TVs)
- ❌ Samsung Tizen TVs
- ❌ Roku
- ❌ Apple TV
- ❌ Fire TV (uses Android, but may need Amazon-specific build)

## Current Project: Android TV APK

This project builds an **Android TV APK** that works on:
- ✅ Android TV devices
- ✅ NVIDIA Shield TV
- ✅ Xiaomi Mi Box
- ✅ Any Android TV or Android-based streaming device

## WebOS (LG Smart TVs) - Separate Project Required

WebOS uses a completely different platform:

### Requirements:
- **Language**: JavaScript/HTML5/CSS
- **SDK**: LG webOS TV SDK
- **Build Output**: `.ipk` file (not APK)
- **Distribution**: LG Content Store

### Development Steps:
1. Download LG webOS TV SDK
2. Create new webOS project
3. Develop using JavaScript/HTML5
4. Use webOS media player APIs
5. Build `.ipk` package
6. Submit to LG Content Store

### Resources:
- LG Developer Portal: https://webostv.developer.lge.com/
- webOS TV SDK Documentation

## Samsung Tizen TVs - Separate Project Required

Samsung Tizen uses a different platform:

### Requirements:
- **Language**: JavaScript/HTML5/CSS or Tizen Native (C++)
- **SDK**: Samsung Smart TV SDK
- **Build Output**: `.tpk` file (not APK)
- **Distribution**: Samsung Smart TV App Store

### Development Steps:
1. Download Samsung Smart TV SDK
2. Create new Tizen project
3. Develop using JavaScript/HTML5 or Tizen Native
4. Use Tizen media player APIs
5. Build `.tpk` package
6. Submit to Samsung App Store

### Resources:
- Samsung Developer Portal: https://developer.samsung.com/smarttv
- Tizen TV Documentation

## Solution Options

### Option 1: Build Separate Apps (Recommended)
Create three separate projects:
1. **Android TV** (this project) → APK
2. **WebOS** → JavaScript/HTML5 → IPK
3. **Samsung Tizen** → JavaScript/HTML5 → TPK

**Pros:**
- Native performance on each platform
- Access to platform-specific features
- Better user experience

**Cons:**
- Three separate codebases to maintain
- More development time

### Option 2: Web-Based Solution
Create a web app that works on all platforms:
- HTML5 video player
- Responsive design
- Works in TV browsers

**Pros:**
- Single codebase
- Works on all platforms with browsers

**Cons:**
- Limited TV remote control support
- May not work as smoothly as native apps
- Browser limitations

### Option 3: Hybrid Approach
- Android TV: Native APK (this project)
- WebOS & Samsung: Web app optimized for TV browsers

## Recommendation

For the best user experience:
1. **Use this Android TV APK** for Android TV devices
2. **Create separate WebOS app** if you need LG TV support
3. **Create separate Tizen app** if you need Samsung TV support

The M3U playlist parsing logic can be shared/ported to JavaScript for WebOS and Tizen versions.

## Quick Comparison

| Platform | File Format | Language | SDK Required |
|----------|-------------|----------|--------------|
| Android TV | `.apk` | Kotlin/Java | Android SDK |
| WebOS (LG) | `.ipk` | JavaScript/HTML5 | LG webOS SDK |
| Samsung Tizen | `.tpk` | JavaScript/HTML5 | Samsung TV SDK |
| Roku | `.pkg` | BrightScript | Roku SDK |
| Apple TV | `.app` | Swift/Objective-C | Xcode |

## Getting Started with Other Platforms

If you want to create WebOS or Samsung versions, you would need to:

1. **Set up development environment** for the target platform
2. **Port the M3U parser** to JavaScript
3. **Create TV-optimized UI** using platform-specific frameworks
4. **Implement video playback** using platform media APIs
5. **Test on actual TV hardware** (emulators may not be sufficient)
6. **Submit to respective app stores**

## Current Project Status

✅ **Android TV APK**: Complete and ready to build
❌ **WebOS IPK**: Not included (requires separate project)
❌ **Samsung Tizen TPK**: Not included (requires separate project)

This project focuses on Android TV compatibility. For WebOS and Samsung support, separate projects would be needed.

