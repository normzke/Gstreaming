#!/bin/bash

# BingeTV Tizen TPK Build Script
# Creates TPK package manually (without Tizen Studio)

echo "=========================================="
echo "BingeTV Tizen TPK Builder"
echo "=========================================="
echo ""

# Check if we're in the right directory
if [ ! -f "config.xml" ]; then
    echo "❌ Error: config.xml not found"
    echo "Please run this script from the apps/tizen directory"
    exit 1
fi

# Check for zip command
if ! command -v zip &> /dev/null; then
    echo "❌ zip command not found. Please install zip utility"
    exit 1
fi

echo "📦 Creating TPK package..."
echo ""

# Create temporary directory
TEMP_DIR="tpk_build"
rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR"

# Copy app files
echo "→ Copying app files..."
cp -r *.xml *.html css js "$TEMP_DIR/" 2>/dev/null || true

# Create author-signature.xml (minimal)
cat > "$TEMP_DIR/author-signature.xml" << EOF
<?xml version="1.0" encoding="UTF-8"?>
<AuthorSignature>
</AuthorSignature>
EOF

# Create signature1.xml (minimal - for unsigned package)
cat > "$TEMP_DIR/signature1.xml" << EOF
<?xml version="1.0" encoding="UTF-8"?>
<Signature>
</Signature>
EOF

# Create TPK (ZIP format)
TPK_NAME="com.bingetv.app-1.0.0.tpk"
cd "$TEMP_DIR"
zip -r "../$TPK_NAME" . -q
cd ..
rm -rf "$TEMP_DIR"

if [ -f "$TPK_NAME" ]; then
    TPK_SIZE=$(du -h "$TPK_NAME" | cut -f1)
    echo ""
    echo "=========================================="
    echo "✅ TPK Build Successful!"
    echo "=========================================="
    echo ""
    echo "⚠️  Note: This is an unsigned TPK"
    echo "   For production, sign with Tizen Studio certificate"
    echo ""
    echo "📦 TPK Location: $(pwd)/$TPK_NAME"
    echo "📏 TPK Size: $TPK_SIZE"
    echo ""
    echo "To install on Samsung TV:"
    echo "  1. Enable Developer Mode on TV"
    echo "  2. Use: sdb install $TPK_NAME"
    echo "  3. Or copy to USB and install via file manager"
    echo ""
    
    # Copy to public directory
    if [ -d "../../public/apps/tizen" ]; then
        mkdir -p ../../public/apps/tizen
        cp "$TPK_NAME" ../../public/apps/tizen/
        echo "✅ TPK copied to public/apps/tizen/"
    fi
else
    echo "❌ TPK build failed"
    exit 1
fi

