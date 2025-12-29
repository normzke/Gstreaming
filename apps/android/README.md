# TiviMate Streamer - Android TV App

An Android TV application for streaming TiviMate 8K Pro playlists on Android TV devices.

## Features

- ✅ Parse and load M3U playlists (TiviMate format)
- ✅ Browse channels organized by categories
- ✅ Stream live TV channels with ExoPlayer
- ✅ TV-optimized UI using Android Leanback
- ✅ Support for HLS, DASH, and standard streaming protocols
- ✅ Channel metadata support (logos, groups, EPG data)

## Compatibility

### Android TV
- ✅ Fully supported - Native APK installation
- Minimum Android version: 5.0 (API 21)
- Target Android version: 14 (API 34)

### WebOS (LG Smart TVs)
⚠️ **Not directly compatible** - APK files cannot run on WebOS. WebOS requires:
- Separate WebOS app development using JavaScript/HTML5
- LG Content Store submission
- Different build process (.ipk files)

### Samsung Tizen TVs
⚠️ **Not directly compatible** - APK files cannot run on Tizen. Samsung TVs require:
- Separate Tizen app development using JavaScript/HTML5
- Samsung Smart TV SDK
- Different build process (.tpk files)

## Building the APK

### Prerequisites

1. **Android Studio** (latest version recommended)
2. **Android SDK** (API 21+)
3. **JDK 8 or higher**

### Build Steps

1. **Clone or download this project**

2. **Open in Android Studio**
   ```bash
   # Open Android Studio and select "Open an existing project"
   # Navigate to the TiviMateStreamer directory
   ```

3. **Configure local.properties**
   ```bash
   # Copy the example file
   cp local.properties.example local.properties
   
   # Edit local.properties and set your Android SDK path:
   # sdk.dir=/Users/YOUR_USERNAME/Library/Android/sdk
   # (or C:\Users\YOUR_USERNAME\AppData\Local\Android\Sdk on Windows)
   ```

4. **Sync Gradle**
   - Android Studio will automatically sync, or click "Sync Now"

5. **Build APK**
   ```bash
   # Using Gradle command line:
   ./gradlew assembleRelease
   
   # Or in Android Studio:
   # Build > Build Bundle(s) / APK(s) > Build APK(s)
   ```

6. **Find your APK**
   - The APK will be located at: `app/build/outputs/apk/release/app-release.apk`

### Installing on Android TV

1. **Enable Developer Options** on your Android TV:
   - Go to Settings > About
   - Click on "Build" 7 times
   - Go back to Settings > Developer options
   - Enable "Unknown sources" or "Install unknown apps"

2. **Transfer APK to TV**:
   - Use ADB: `adb install app-release.apk`
   - Or use a USB drive or network file transfer

3. **Launch the app** from your TV's app launcher

## Usage

1. **Launch the app** on your Android TV
2. **Enter your M3U playlist URL** when prompted
   - Example: `http://example.com/playlist.m3u`
   - Or: `https://your-server.com/tivimate/playlist.m3u`
3. **Browse channels** organized by categories
4. **Select a channel** to start streaming
5. **Use remote control** to navigate and control playback

## M3U Playlist Format

The app supports standard M3U playlists with TiviMate-compatible metadata:

```
#EXTM3U
#EXTINF:-1 tvg-id="channel1" tvg-name="Channel 1" tvg-logo="http://example.com/logo.png" group-title="News",Channel 1
http://example.com/stream1.m3u8
#EXTINF:-1 tvg-id="channel2" tvg-name="Channel 2" group-title="Sports",Channel 2
http://example.com/stream2.m3u8
```

## Project Structure

```
TiviMateStreamer/
├── app/
│   ├── src/
│   │   └── main/
│   │       ├── java/com/tivimatestreamer/app/
│   │       │   ├── MainActivity.kt          # Main TV browsing interface
│   │       │   ├── PlaybackActivity.kt      # Video playback screen
│   │       │   ├── CardPresenter.kt         # Channel card presenter
│   │       │   ├── ExoPlayerAdapter.kt      # ExoPlayer integration
│   │       │   ├── PlaylistInputDialogFragment.kt
│   │       │   ├── model/
│   │       │   │   └── Channel.kt           # Channel data model
│   │       │   └── parser/
│   │       │       └── M3UParser.kt         # M3U playlist parser
│   │       ├── res/                         # Resources (layouts, strings, etc.)
│   │       └── AndroidManifest.xml
│   └── build.gradle
├── build.gradle
├── settings.gradle
└── README.md
```

## Dependencies

- **AndroidX Leanback**: TV-optimized UI components
- **ExoPlayer**: High-performance media player for streaming
- **OkHttp**: HTTP client for fetching playlists
- **Kotlin Coroutines**: Asynchronous operations

## Limitations

1. **WebOS and Samsung Tizen**: Cannot run APK files. Separate apps needed.
2. **Playlist Storage**: Playlists are not saved between sessions (enter URL each time)
3. **EPG Data**: EPG (Electronic Program Guide) display not implemented
4. **Favorites**: No favorites/bookmarks feature yet

## Future Enhancements

- [ ] Save multiple playlists
- [ ] Favorites/bookmarks
- [ ] EPG integration
- [ ] Search functionality
- [ ] Parental controls
- [ ] WebOS version
- [ ] Samsung Tizen version

## Troubleshooting

### Build Errors
- Ensure Android SDK is properly configured in `local.properties`
- Check that you have the required SDK platforms installed (API 21-34)
- Verify Gradle version compatibility

### Playback Issues
- Check network connectivity
- Verify playlist URL is accessible
- Ensure stream URLs in playlist are valid
- Check if streams require authentication (not currently supported)

### Installation Issues
- Enable "Unknown sources" in Developer options
- Ensure TV has enough storage space
- Try uninstalling any previous version first

## License

This project is provided as-is for educational and personal use.

## Support for WebOS and Samsung Tizen

To create versions for WebOS and Samsung Tizen, you would need to:

1. **WebOS (LG TVs)**:
   - Use LG webOS TV SDK
   - Develop using JavaScript/HTML5
   - Build .ipk package
   - Submit to LG Content Store

2. **Samsung Tizen**:
   - Use Samsung Smart TV SDK
   - Develop using JavaScript/HTML5
   - Build .tpk package
   - Submit to Samsung Smart TV App Store

These would be separate projects with different codebases, as they use different platforms and APIs.

