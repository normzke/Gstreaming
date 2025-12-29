#!/bin/bash
# BingeTV WebOS App Packaging Script
# This script packages the WebOS app into an IPK file

echo "🔧 Packaging BingeTV WebOS App..."

# Check if ares-package is installed
if ! command -v ares-package &> /dev/null; then
    echo "❌ Error: webOS CLI not found!"
    echo "Please install webOS TV SDK"
    echo "Download from: https://webostv.developer.lge.com/sdk/installation/"
    exit 1
fi

# Navigate to webos directory
cd "$(dirname "$0")"

# Create appinfo.json if it doesn't exist
if [ ! -f "appinfo.json" ]; then
    echo "📝 Creating appinfo.json..."
    cat > appinfo.json << 'EOF'
{
  "id": "com.bingetv.app",
  "version": "1.0.0",
  "vendor": "BingeTV",
  "type": "web",
  "main": "index.html",
  "title": "BingeTV",
  "icon": "icon.png",
  "largeIcon": "icon.png",
  "bgImage": "splash.png",
  "iconColor": "#8B0000",
  "resolution": "1920x1080",
  "requiredMemory": 100,
  "disableBackHistoryAPI": true,
  "enableKeyboardFocus": true
}
EOF
fi

# Package the app
echo "📦 Creating IPK package..."
ares-package . -o ../

# Check if packaging was successful
if [ $? -eq 0 ]; then
    # Find the generated IPK file
    IPK_FILE=$(find ../ -maxdepth 1 -name "com.bingetv.app_*.ipk" -type f | head -n 1)
    
    if [ -n "$IPK_FILE" ]; then
        # Move to current directory with standard name
        mv "$IPK_FILE" "com.bingetv.app_1.0.0_all.ipk"
        echo "✅ WebOS app packaged successfully!"
        echo "📦 Output: com.bingetv.app_1.0.0_all.ipk"
        ls -lh com.bingetv.app_1.0.0_all.ipk
    else
        echo "⚠️  IPK file not found after packaging"
    fi
else
    echo "❌ Packaging failed!"
    exit 1
fi

echo ""
echo "🚀 Next steps:"
echo "1. Install on LG TV: ares-install --device <TV_NAME> com.bingetv.app_1.0.0_all.ipk"
echo "2. Or submit to LG Content Store for distribution"
