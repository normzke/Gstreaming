#!/bin/bash

# BingeTV WebOS IPK Build Script
# Builds IPK without full webOS SDK - creates package manually

echo "=========================================="
echo "BingeTV WebOS IPK Builder"
echo "=========================================="
echo ""

# Check if we're in the right directory
if [ ! -f "appinfo.json" ]; then
    echo "❌ Error: appinfo.json not found"
    echo "Please run this script from the apps/webos directory"
    exit 1
fi

# Create package directory
PACKAGE_DIR="package"
rm -rf "$PACKAGE_DIR"
mkdir -p "$PACKAGE_DIR/apps/com.bingetv.app"
mkdir -p "$PACKAGE_DIR/CONTROL"

echo "📦 Creating IPK package..."
echo ""

# Copy app files
echo "→ Copying app files..."
cp -r *.json *.html css js "$PACKAGE_DIR/apps/com.bingetv.app/" 2>/dev/null || true

# Create control file
echo "→ Creating control file..."
cat > "$PACKAGE_DIR/CONTROL/control" << EOF
Package: com.bingetv.app
Version: 1.0.0
Architecture: all
Maintainer: BingeTV
Description: BingeTV Streaming App for webOS
EOF

# Create IPK
echo "→ Building IPK..."
cd "$PACKAGE_DIR"
tar czf ../com.bingetv.app_1.0.0_all.ipk .
cd ..

if [ -f "com.bingetv.app_1.0.0_all.ipk" ]; then
    IPK_SIZE=$(du -h "com.bingetv.app_1.0.0_all.ipk" | cut -f1)
    echo ""
    echo "=========================================="
    echo "✅ IPK Build Successful!"
    echo "=========================================="
    echo ""
    echo "📦 IPK Location: $(pwd)/com.bingetv.app_1.0.0_all.ipk"
    echo "📏 IPK Size: $IPK_SIZE"
    echo ""
    echo "To install on LG TV:"
    echo "  1. Enable Developer Mode on TV"
    echo "  2. Use: ares-install --device TV_IP com.bingetv.app_1.0.0_all.ipk"
    echo "  3. Or copy to USB and install via file manager"
    echo ""
    
    # Copy to public directory
    if [ -d "../../public/apps/webos" ]; then
        mkdir -p ../../public/apps/webos
        cp com.bingetv.app_1.0.0_all.ipk ../../public/apps/webos/
        echo "✅ IPK copied to public/apps/webos/"
    fi
    
    # Cleanup
    rm -rf "$PACKAGE_DIR"
else
    echo "❌ IPK build failed"
    exit 1
fi

