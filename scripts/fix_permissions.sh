#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "\n${GREEN}=== FIXING PERMISSIONS ===${NC}"
echo -e "Remote target: ${YELLOW}bluehost:/home1/fieldte5/bingetv.co.ke${NC}\n"

# 1. Fix directory and file permissions
echo -e "${GREEN}1. Updating permissions...${NC}"

ssh -T bluehost << 'EOF'
    REMOTE_PATH="/home1/fieldte5/bingetv.co.ke"
    
    # Set directory permissions
    echo "Setting directory permissions..."
    find "$REMOTE_PATH" -type d -exec chmod 755 {} \;
    
    # Set file permissions
    echo "Setting file permissions..."
    find "$REMOTE_PATH" -type f -exec chmod 644 {} \;
    
    # Make scripts executable
    echo "Making scripts executable..."
    find "$REMOTE_PATH" -name "*.sh" -exec chmod +x {} \;
    find "$REMOTE_PATH" -name "*.php" -exec chmod +x {} \;
    
    # Special permissions for public directory
    echo "Setting public directory permissions..."
    chmod 755 "$REMOTE_PATH/public"
    chmod 644 "$REMOTE_PATH/public/index.php"
    
    # Verify permissions
    echo -e "\nVerification:"
    echo "Parent directory:"
    ls -ld "$REMOTE_PATH"
    echo -e "\nPublic directory:"
    ls -ld "$REMOTE_PATH/public"
    echo -e "\nIndex.php:"
    ls -l "$REMOTE_PATH/public/index.php"
    
    echo -e "\n✅ Permissions updated"
EOF

echo -e "\n${GREEN}=== NEXT STEPS ===${NC}"
echo "1. Test the website: https://bingetv.co.ke"
echo "2. If you still see issues, check the web server user and group:"
echo "   - The web server is running as user: nobody"
echo "   - Consider changing the web server user/group to match file ownership"
echo "   - Or change file ownership to match the web server user"
