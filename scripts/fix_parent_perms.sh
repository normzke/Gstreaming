#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "\n${GREEN}=== FIXING PARENT DIRECTORY PERMISSIONS ===${NC}"
echo -e "Remote target: ${YELLOW}bluehost:/home1/fieldte5${NC}\n"

# 1. Fix parent directory permissions
echo -e "${GREEN}1. Updating parent directory permissions...${NC}"

ssh -T bluehost << 'EOF'
    # Set permissions on the parent directory (add read and execute for group and others)
    echo "Updating /home1/fieldte5 permissions..."
    chmod 711 /home1/fieldte5
    
    # Set permissions on the site directory
    echo "Updating /home1/fieldte5/bingetv.co.ke permissions..."
    chmod 755 /home1/fieldte5/bingetv.co.ke
    
    # Verify permissions
    echo -e "\nVerification:"
    echo "/home1/fieldte5:"
    ls -ld /home1/fieldte5
    echo -e "\n/home1/fieldte5/bingetv.co.ke:"
    ls -ld /home1/fieldte5/bingetv.co.ke
    
    # Test web server access
    echo -e "\nTesting web server access..."
    if [ -f "/home1/fieldte5/bingetv.co.ke/public/test_php.php" ]; then
        echo "Test file exists at: https://bingetv.co.ke/test_php.php"
    else
        echo "Creating test file..."
        echo '<?php echo "PHP is working! Server: " . $_SERVER["SERVER_SOFTWARE"] . "<br>PHP Version: " . phpversion(); ?>' > "/home1/fieldte5/bingetv.co.ke/public/test_php.php"
        chmod 644 "/home1/fieldte5/bingetv.co.ke/public/test_php.php"
        echo "Test file created at: https://bingetv.co.ke/test_php.php"
    fi
    
    echo -e "\n✅ Parent directory permissions updated"
EOF

echo -e "\n${GREEN}=== NEXT STEPS ===${NC}"
echo "1. Test the website: https://bingetv.co.ke"
echo "2. Test PHP execution: https://bingetv.co.ke/test_php.php"
echo "3. If you still see issues, contact Bluehost support and ask them to:"
echo "   - Verify the document root is correctly set to /home1/fieldte5/bingetv.co.ke/htdocs"
echo "   - Check for any mod_security or other server-level restrictions"
echo "   - Review the web server error logs for specific error messages"
