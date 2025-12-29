# WebOS Build Instructions

## Quick Start Guide

### Prerequisites

1. **Download webOS TV SDK**
   - Visit: https://webostv.developer.lge.com/sdk/installation
   - Download and install webOS TV SDK for your OS

2. **Verify Installation**
   ```bash
   ares-setup-device --list
   ```

### Building IPK

#### Option 1: Using ares-package (Recommended)

```bash
cd TiviMateStreamer-WebOS

# Package the app
ares-package .

# Output: com.tivimatestreamer.app_1.0.0_all.ipk
```

#### Option 2: Manual Package Creation

```bash
# Create package directory
mkdir -p package/apps/com.tivimatestreamer.app

# Copy app files
cp -r *.json *.html css js package/apps/com.tivimatestreamer.app/

# Create control file
mkdir -p package/CONTROL
cat > package/CONTROL/control << EOF
Package: com.tivimatestreamer.app
Version: 1.0.0
Architecture: all
Maintainer: TiviMate Streamer
Description: TiviMate Streamer for webOS
EOF

# Create IPK
cd package
tar czf ../com.tivimatestreamer.app_1.0.0_all.ipk .
cd ..
```

### Installing on TV

#### Method 1: Developer Mode + CLI

1. **Enable Developer Mode on TV**
   - Settings > General > About This TV
   - Click "Developer Mode" 7 times
   - Enable Developer Mode
   - Note TV IP address

2. **Install via CLI**
   ```bash
   ares-install --device <TV_IP> com.tivimatestreamer.app_1.0.0_all.ipk
   ```

#### Method 2: USB Installation

1. Copy IPK to USB drive
2. Insert USB into TV
3. Use file manager to install

### Testing

```bash
# Launch app on connected TV
ares-launch --device <TV_IP> com.tivimatestreamer.app

# View logs
ares-log --device <TV_IP>

# Remote debugging
ares-inspect --device <TV_IP> --app com.tivimatestreamer.app
```

## Troubleshooting

- **ares-package not found**: Add webOS SDK to PATH
- **Installation fails**: Check Developer Mode is enabled
- **App doesn't launch**: Check logs with `ares-log`

