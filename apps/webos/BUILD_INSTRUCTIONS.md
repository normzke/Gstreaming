# Samsung Tizen Build Instructions

## Quick Start Guide

### Prerequisites

1. **Download Tizen Studio**
   - Visit: https://developer.samsung.com/smarttv/develop/getting-started/setting-up-sdk.html
   - Download Tizen Studio with TV extension
   - Install following the guide

2. **Set up Certificate**
   - Open Tizen Studio
   - Tools > Certificate Manager
   - Create new certificate profile
   - Follow wizard to generate certificate

### Building TPK

#### Option 1: Using Tizen Studio (Recommended)

1. **Import Project**
   - File > Import > Tizen > Tizen Project
   - Select `TiviMateStreamer-Tizen` directory
   - Click Finish

2. **Build Project**
   - Right-click project > Build Project
   - Wait for build to complete

3. **Package TPK**
   - Right-click project > Tizen > Package > TPK
   - Select certificate profile
   - TPK will be created in project directory

#### Option 2: Using Tizen CLI

```bash
cd TiviMateStreamer-Tizen

# Build TPK
tizen package -t tpk -s <certificate-profile-name>

# Output: com.tivimatestreamer.app-1.0.0.tpk
```

### Installing on TV

#### Method 1: Developer Mode + Tizen Studio

1. **Enable Developer Mode on TV**
   - Settings > General > External Device Manager > Device Connection Manager
   - Enable Developer Mode
   - Note TV IP address

2. **Connect in Tizen Studio**
   - Tools > Device Manager
   - Add device with TV IP
   - Connect to device

3. **Install TPK**
   - Right-click project > Run As > Tizen Web App
   - Or drag TPK to Device Manager

#### Method 2: Using sdb (Command Line)

```bash
# Connect to TV
sdb connect <TV_IP>

# Install TPK
sdb install com.tivimatestreamer.app-1.0.0.tpk

# Launch app
sdb shell "launch_app com.tivimatestreamer.app"
```

#### Method 3: USB Installation

1. Copy TPK to USB drive
2. Insert USB into TV
3. Use file manager to install

### Testing

```bash
# View logs
sdb shell
cat /var/log/webapp/com.tivimatestreamer.app.log

# Launch app
sdb shell "launch_app com.tivimatestreamer.app"

# Remote debugging
# Use Tizen Studio debugger or Chrome DevTools
```

## Troubleshooting

- **Build fails**: Check certificate is set up correctly
- **Installation fails**: Verify Developer Mode is enabled
- **App doesn't launch**: Check logs with `sdb shell`
- **Certificate errors**: Recreate certificate profile

