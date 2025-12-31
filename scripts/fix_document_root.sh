#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "\n${GREEN}=== FIXING DOCUMENT ROOT ===${NC}"
echo -e "Remote target: ${YELLOW}bluehost:/home1/fieldte5/bingetv.co.ke${NC}\n"

# 1. Create symbolic link from document root to public directory
echo -e "${GREEN}1. Creating symbolic link from document root to public directory...${NC}"

ssh -T bluehost << 'EOF'
    REMOTE_PATH="/home1/fieldte5/bingetv.co.ke"
    
    # Remove existing htdocs if it exists
    if [ -L "$REMOTE_PATH/htdocs" ] || [ -e "$REMOTE_PATH/htdocs" ]; then
        echo "Removing existing htdocs..."
        rm -rf "$REMOTE_PATH/htdocs"
    fi
    
    # Create symbolic link
    echo "Creating symbolic link from htdocs to public..."
    ln -sf "$REMOTE_PATH/public" "$REMOTE_PATH/htdocs"
    
    # Set permissions
    echo "Setting permissions..."
    chmod 755 "$REMOTE_PATH"
    chmod 755 "$REMOTE_PATH/public"
    chmod 644 "$REMOTE_PATH/public/index.php" 2>/dev/null || true
    
    # Verify
    echo -e "\nVerification:"
    echo "Symbolic link:"
    ls -la "$REMOTE_PATH/" | grep htdocs
    echo -e "\nPublic directory permissions:"
    ls -ld "$REMOTE_PATH/public"
    echo -e "\nIndex.php permissions:"
    ls -l "$REMOTE_PATH/public/index.php" 2>/dev/null || echo "index.php not found"
    
    echo -e "\n✅ Document root fix completed"
EOF

echo -e "\n${GREEN}=== NEXT STEPS ===${NC}"
echo "1. Test the website: https://bingetv.co.ke"
echo "2. If you still see issues, check the web server configuration:"
echo "   - Document root should be set to: /home1/fieldte5/bingetv.co.ke/htdocs"
echo "   - Or contact Bluehost support to update the document root"
