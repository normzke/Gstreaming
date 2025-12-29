# TiviMate Streamer - WebOS (LG Smart TV)

A webOS application for streaming TiviMate 8K Pro playlists on LG Smart TVs.

## Features

- ✅ Parse and load M3U playlists (TiviMate format)
- ✅ Browse channels organized by categories
- ✅ Stream live TV channels with HTML5 video player
- ✅ TV-optimized UI for LG Smart TVs
- ✅ Support for HLS, DASH, and standard streaming protocols
- ✅ Channel metadata support (logos, groups, EPG data)
- ✅ Remote control navigation

## Requirements

- **LG webOS TV SDK** (version 4.0 or higher)
- **Node.js** (for building)
- **LG Smart TV** (for testing)

## Project Structure

```
TiviMateStreamer-WebOS/
├── appinfo.json          # WebOS app manifest
├── index.html            # Main HTML file
├── css/
│   └── style.css         # Stylesheet
├── js/
│   ├── app.js            # Main application logic
│   ├── m3u-parser.js     # M3U playlist parser
│   └── webOSTV.js        # WebOS TV API wrapper
├── icon.png              # App icon (320x320)
├── bg.png                # Background image (1920x1080)
└── README.md
```

## Building the IPK

### Method 1: Using webOS TV SDK CLI

1. **Install webOS TV SDK**
   - Download from: https://webostv.developer.lge.com/sdk/installation
   - Follow installation instructions

2. **Set up project**
   ```bash
   cd TiviMateStreamer-WebOS
   # Ensure all files are in place
   ```

3. **Create app package**
   ```bash
   # Using ares-package command (from webOS SDK)
   ares-package .
   ```

4. **Output**
   - IPK file will be created in the project directory
   - Example: `com.tivimatestreamer.app_1.0.0_all.ipk`

### Method 2: Manual IPK Creation

1. **Create package structure**
   ```bash
   mkdir -p ipk/apps/com.tivimatestreamer.app
   cp -r * ipk/apps/com.tivimatestreamer.app/
   ```

2. **Create control file**
   ```bash
   mkdir -p ipk/CONTROL
   cat > ipk/CONTROL/control << EOF
   Package: com.tivimatestreamer.app
   Version: 1.0.0
   Architecture: all
   Maintainer: TiviMate Streamer
   Description: TiviMate Streamer for webOS
   EOF
   ```

3. **Build IPK**
   ```bash
   cd ipk
   tar czf ../com.tivimatestreamer.app_1.0.0_all.ipk .
   ```

## Installing on LG Smart TV

### Method 1: Developer Mode

1. **Enable Developer Mode on TV**
   - Settings > General > About This TV
   - Click "Developer Mode" multiple times
   - Enable "Developer Mode"

2. **Get TV IP address**
   - Settings > Network > Wi-Fi/Ethernet > Advanced Settings

3. **Install via CLI**
   ```bash
   ares-install --device <TV_IP_ADDRESS> com.tivimatestreamer.app_1.0.0_all.ipk
   ```

### Method 2: USB Installation

1. **Copy IPK to USB drive**
   ```bash
   cp com.tivimatestreamer.app_1.0.0_all.ipk /Volumes/USB_DRIVE/
   ```

2. **On TV**
   - Insert USB drive
   - Use file manager to navigate to IPK
   - Click to install
   - Allow installation from unknown sources

## Development

### Testing Locally

1. **Use webOS TV Emulator**
   ```bash
   # Launch emulator
   ares-setup-device
   ares-launch com.tivimatestreamer.app
   ```

2. **Or use browser for basic testing**
   - Open `index.html` in a browser
   - Note: Some webOS APIs won't work in browser

### Debugging

1. **Enable remote debugging**
   ```bash
   ares-inspect --device <TV_IP> --app com.tivimatestreamer.app
   ```

2. **View logs**
   ```bash
   ares-log --device <TV_IP>
   ```

## App Icons

You need to create:
- `icon.png` - 320x320 pixels (app icon)
- `bg.png` - 1920x1080 pixels (background/splash)

Place these files in the root directory.

## Configuration

Edit `appinfo.json` to customize:
- App ID
- Version
- Title
- Icon paths
- Memory requirements

## Limitations

- Requires webOS TV SDK for building
- Must be installed via Developer Mode or app store
- Some features may require webOS-specific APIs
- Video codec support depends on TV model

## Troubleshooting

**Build fails:**
- Ensure webOS TV SDK is installed
- Check `appinfo.json` syntax
- Verify all required files exist

**Installation fails:**
- Enable Developer Mode on TV
- Check TV and computer are on same network
- Verify IPK file is not corrupted

**App doesn't launch:**
- Check TV logs: `ares-log --device <TV_IP>`
- Verify appinfo.json is valid
- Check for JavaScript errors in console

## Resources

- LG webOS TV Developer Portal: https://webostv.developer.lge.com/
- webOS TV SDK Documentation: https://webostv.developer.lge.com/develop/app-developer-guide/
- webOS TV API Reference: https://webostv.developer.lge.com/api/web-api/

## License

This project is provided as-is for educational and personal use.

