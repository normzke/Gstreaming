#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "\n${GREEN}=== FIXING PERMISSIONS (FINAL) ===${NC}"
echo -e "Remote target: ${YELLOW}bluehost:/home1/fieldte5/bingetv.co.ke${NC}\n"

# 1. Fix permissions using group ownership
echo -e "${GREEN}1. Updating permissions using group ownership...${NC}"

ssh -T bluehost << 'EOF'
    REMOTE_PATH="/home1/fieldte5/bingetv.co.ke"
    
    # Create a new group if it doesn't exist
    if ! grep -q "^webgroup:" /etc/group; then
        echo "Creating webgroup..."
        groupadd webgroup
        usermod -a -G webgroup nobody
        usermod -a -G webgroup fieldte5
    fi
    
    # Change group ownership recursively
    echo "Updating group ownership..."
    chgrp -R webgroup "$REMOTE_PATH"
    
    # Set setgid bit on directories to ensure new files inherit the group
    echo "Setting setgid on directories..."
    find "$REMOTE_PATH" -type d -exec chmod g+s {} \;
    
    # Set directory permissions (drwxr-sr-x)
    echo "Setting directory permissions..."
    find "$REMOTE_PATH" -type d -exec chmod 2775 {} \;
    
    # Set file permissions (-rw-rw-r--)
    echo "Setting file permissions..."
    find "$REMOTE_PATH" -type f -exec chmod 664 {} \;
    
    # Make scripts executable
    echo "Making scripts executable..."
    find "$REMOTE_PATH" -name "*.sh" -exec chmod +x {} \;
    find "$REMOTE_PATH" -name "*.php" -exec chmod +x {} \;
    
    # Special permissions for public directory
    echo "Setting public directory permissions..."
    chmod 2775 "$REMOTE_PATH/public"
    chmod 664 "$REMOTE_PATH/public/index.php"
    
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
echo "2. If you still see issues, you may need to:"
echo "   - Contact Bluehost support to:"
echo "     * Add your user to the web server's group"
echo "     * Or change the web server's user/group"
echo "   - Or move the site to a different directory with correct ownership"
