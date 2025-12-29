#!/bin/bash
# BingeTV Tizen App Packaging Script
# This script packages the Tizen app into a TPK file

echo "🔧 Packaging BingeTV Tizen App..."

# Check if tizen CLI is installed
if ! command -v tizen &> /dev/null; then
    echo "❌ Error: Tizen CLI not found!"
    echo "Please install Tizen Studio and add 'tizen' to your PATH"
    echo "Download from: https://developer.tizen.org/development/tizen-studio/download"
    exit 1
fi

# Navigate to tizen directory
cd "$(dirname "$0")"

# Create config.xml if it doesn't exist
if [ ! -f "config.xml" ]; then
    echo "📝 Creating config.xml..."
    cat > config.xml << 'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<widget xmlns="http://www.w3.org/ns/widgets" 
        xmlns:tizen="http://tizen.org/ns/widgets" 
        id="com.bingetv.app" 
        version="1.0.0" 
        viewmodes="maximized">
    <tizen:application id="com.bingetv.app" package="com.bingetv.app" required_version="6.0"/>
    <content src="index.html"/>
    <feature name="http://tizen.org/feature/screen.size.all"/>
    <icon src="icon.png"/>
    <name>BingeTV</name>
    <tizen:profile name="tv"/>
    <tizen:setting screen-orientation="landscape" context-menu="enable" background-support="disable" encryption="disable" install-location="auto" hwkey-event="enable"/>
    <tizen:privilege name="http://tizen.org/privilege/internet"/>
    <tizen:privilege name="http://tizen.org/privilege/filesystem.read"/>
    <tizen:privilege name="http://tizen.org/privilege/filesystem.write"/>
</widget>
EOF
fi

# Package the app
echo "📦 Creating TPK package..."
tizen package -t tpk -s bingetv -- .

# Check if packaging was successful
if [ $? -eq 0 ]; then
    # Find the generated TPK file
    TPK_FILE=$(find . -maxdepth 1 -name "*.tpk" -type f | head -n 1)
    
    if [ -n "$TPK_FILE" ]; then
        # Rename to standard name
        mv "$TPK_FILE" "com.bingetv.app-1.0.0.tpk"
        echo "✅ Tizen app packaged successfully!"
        echo "📦 Output: com.bingetv.app-1.0.0.tpk"
        ls -lh com.bingetv.app-1.0.0.tpk
    else
        echo "⚠️  TPK file not found after packaging"
    fi
else
    echo "❌ Packaging failed!"
    exit 1
fi

echo ""
echo "🚀 Next steps:"
echo "1. Install on Samsung TV: tizen install -n com.bingetv.app-1.0.0.tpk -t <TV_IP>"
echo "2. Or upload to Samsung Seller Office for distribution"
