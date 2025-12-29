# TiviMate Streamer - Samsung Tizen TV

A Tizen application for streaming TiviMate 8K Pro playlists on Samsung Smart TVs.

## Features

- ✅ Parse and load M3U playlists (TiviMate format)
- ✅ Browse channels organized by categories
- ✅ Stream live TV channels with HTML5 video player
- ✅ TV-optimized UI for Samsung Smart TVs
- ✅ Support for HLS, DASH, and standard streaming protocols
- ✅ Channel metadata support (logos, groups, EPG data)
- ✅ Remote control navigation

## Requirements

- **Samsung Smart TV SDK** (Tizen Studio)
- **Tizen Studio** with TV extension
- **Samsung Smart TV** (for testing)

## Project Structure

```
TiviMateStreamer-Tizen/
├── config.xml            # Tizen app configuration
├── index.html            # Main HTML file
├── css/
│   └── style.css         # Stylesheet
├── js/
│   ├── app.js            # Main application logic
│   ├── m3u-parser.js     # M3U playlist parser
│   └── tizen.js          # Tizen API wrapper
├── icon.png              # App icon (117x117)
└── README.md
```

## Building the TPK

### Method 1: Using Tizen Studio (Recommended)

1. **Install Tizen Studio**
   - Download from: https://developer.samsung.com/smarttv/develop/getting-started/setting-up-sdk.html
   - Install with TV extension

2. **Import Project**
   - Open Tizen Studio
   - File > Import > Tizen > Tizen Project
   - Select `TiviMateStreamer-Tizen` directory
   - Click Finish

3. **Build Project**
   - Right-click project > Build Project
   - Or: Project > Build Project

4. **Package Application**
   - Right-click project > Tizen > Package > TPK
   - TPK file will be created in project directory
   - Example: `com.tivimatestreamer.app-1.0.0.tpk`

### Method 2: Using Tizen CLI

1. **Install Tizen CLI**
   ```bash
   # Tizen CLI comes with Tizen Studio
   # Or install separately
   ```

2. **Build TPK**
   ```bash
   cd TiviMateStreamer-Tizen
   tizen package -t tpk -s <certificate-profile-name>
   ```

## Installing on Samsung Smart TV

### Method 1: Developer Mode

1. **Enable Developer Mode on TV**
   - Settings > General > External Device Manager > Device Connection Manager
   - Enable "Developer Mode"
   - Note the TV's IP address

2. **Connect TV to Tizen Studio**
   - In Tizen Studio: Tools > Device Manager
   - Add device with TV's IP address
   - Connect to device

3. **Install TPK**
   - Right-click project > Run As > Tizen Web App
   - Or: Tools > Tizen > Tizen SDK Command Line > Run
   ```bash
   sdb install com.tivimatestreamer.app-1.0.0.tpk
   ```

### Method 2: USB Installation

1. **Copy TPK to USB drive**
   ```bash
   cp com.tivimatestreamer.app-1.0.0.tpk /Volumes/USB_DRIVE/
   ```

2. **On TV**
   - Insert USB drive
   - Use file manager to navigate to TPK
   - Click to install
   - Allow installation from unknown sources

## Development

### Testing Locally

1. **Use Tizen TV Emulator**
   - Launch Tizen Studio
   - Tools > Emulator Manager
   - Create/Launch TV emulator
   - Run app on emulator

2. **Or use browser for basic testing**
   - Open `index.html` in a browser
   - Note: Some Tizen APIs won't work in browser

### Debugging

1. **Enable remote debugging**
   - Connect TV via network
   - In Tizen Studio: Run > Debug Configurations
   - Set breakpoints and debug

2. **View logs**
   ```bash
   sdb shell
   cat /var/log/webapp/com.tivimatestreamer.app.log
   ```

## App Icon

You need to create:
- `icon.png` - 117x117 pixels (app icon)

Place this file in the root directory.

## Configuration

Edit `config.xml` to customize:
- App ID
- Version
- Name/Description
- Privileges
- Screen orientation

## Certificate Setup

For building TPK, you need a certificate:

1. **Create Certificate Profile**
   - Tizen Studio > Tools > Certificate Manager
   - Create new certificate profile
   - Follow wizard to create certificate

2. **Use Certificate in Build**
   - Project > Properties > Tizen > Certificate
   - Select your certificate profile

## Limitations

- Requires Tizen Studio for building
- Must be installed via Developer Mode or app store
- Some features may require Tizen-specific APIs
- Video codec support depends on TV model
- Certificate required for TPK signing

## Troubleshooting

**Build fails:**
- Ensure Tizen Studio is installed with TV extension
- Check `config.xml` syntax
- Verify certificate is set up
- Check all required files exist

**Installation fails:**
- Enable Developer Mode on TV
- Check TV and computer are on same network
- Verify TPK file is not corrupted
- Check certificate is valid

**App doesn't launch:**
- Check TV logs via `sdb shell`
- Verify config.xml is valid
- Check for JavaScript errors
- Ensure all privileges are declared in config.xml

**Video playback issues:**
- Check codec support on TV model
- Verify stream URL is accessible
- Check network connectivity
- Some streams may require specific codecs

## Resources

- Samsung Developer Portal: https://developer.samsung.com/smarttv
- Tizen TV Documentation: https://developer.samsung.com/smarttv/develop/getting-started/quick-start-guide.html
- Tizen TV API Reference: https://developer.tizen.org/development/api-references

## License

This project is provided as-is for educational and personal use.

