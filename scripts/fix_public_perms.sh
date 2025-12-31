#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "\n${GREEN}=== FIXING PUBLIC DIRECTORY PERMISSIONS ===${NC}"
echo -e "Remote target: ${YELLOW}bluehost:/home1/fieldte5/bingetv.co.ke/public${NC}\n"

# 1. Fix permissions for the public directory only
echo -e "${GREEN}1. Updating public directory permissions...${NC}"

ssh -T bluehost << 'EOF'
    PUBLIC_PATH="/home1/fieldte5/bingetv.co.ke/public"
    
    # Set directory permissions (drwxr-xr-x)
    echo "Setting directory permissions..."
    find "$PUBLIC_PATH" -type d -exec chmod 755 {} \;
    
    # Set file permissions (-rw-r--r--)
    echo "Setting file permissions..."
    find "$PUBLIC_PATH" -type f -exec chmod 644 {} \;
    
    # Make PHP files executable
    echo "Making PHP files executable..."
    find "$PUBLIC_PATH" -name "*.php" -exec chmod +x {} \;
    
    # Verify permissions
    echo -e "\nVerification:"
    echo "Public directory:"
    ls -ld "$PUBLIC_PATH"
    echo -e "\nIndex.php:"
    ls -l "$PUBLIC_PATH/index.php"
    
    # Test if web server can access the files
    echo -e "\nTesting web server access..."
    if [ -f "$PUBLIC_PATH/test_php.php" ]; then
        echo "Test file exists. Try accessing: https://bingetv.co.ke/test_php.php"
    else
        echo "Creating test file..."
        echo '<?php echo "PHP is working! Server: " . $_SERVER["SERVER_SOFTWARE"] . "<br>PHP Version: " . phpversion(); ?>' > "$PUBLIC_PATH/test_php.php"
        chmod 644 "$PUBLIC_PATH/test_php.php"
        echo "Test file created. Try accessing: https://bingetv.co.ke/test_php.php"
    fi
    
    echo -e "\n✅ Public directory permissions updated"
EOF

echo -e "\n${GREEN}=== NEXT STEPS ===${NC}"
echo "1. Test the website: https://bingetv.co.ke"
echo "2. Test PHP execution: https://bingetv.co.ke/test_php.php"
echo "3. If you still see issues, you may need to contact Bluehost support to:"
echo "   - Check the web server's document root configuration"
echo "   - Verify the web server user has execute permissions on parent directories"
