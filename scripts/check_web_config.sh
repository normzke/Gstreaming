#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "\n${GREEN}=== CHECKING WEB SERVER CONFIGURATION ===${NC}"
echo -e "Remote target: ${YELLOW}bluehost${NC}\n"

# 1. Check web server configuration
echo -e "${GREEN}1. Checking web server configuration...${NC}"

ssh -T bluehost << 'EOF'
    # Find the main Apache/Nginx configuration
    echo "=== Web Server Type ==="
    if [ -f "/usr/local/apache/bin/httpd" ]; then
        echo "Apache is installed"
        APACHE_CONF="/usr/local/apache/conf/httpd.conf"
        if [ -f "$APACHE_CONF" ]; then
            echo -e "\n=== Main Apache Config ($APACHE_CONF) ==="
            grep -i "DocumentRoot\|ServerName\|VirtualHost" "$APACHE_CONF" | grep -v "^#"
        fi
        
        # Check for virtual hosts
        echo -e "\n=== Virtual Hosts ==="
        if [ -d "/usr/local/apache/conf/vhosts/" ]; then
            for vhost in /usr/local/apache/conf/vhosts/*.conf; do
                echo -e "\n--- $vhost ---"
                grep -i "DocumentRoot\|ServerName\|ServerAlias" "$vhost" | grep -v "^#"
            done
        fi
    fi
    
    # Check for Nginx
    if [ -f "/usr/local/nginx/conf/nginx.conf" ]; then
        echo -e "\n=== Nginx Config ==="
        grep -i "server_name\|root\|listen" /usr/local/nginx/conf/nginx.conf | grep -v "^[ \t]*#"
    fi
    
    # Check for cPanel Apache include
    if [ -d "/var/cpanel" ]; then
        echo -e "\n=== cPanel User Data ==="
        USER_DATA="/var/cpanel/userdata/fieldte5/bingetv.co.ke"
        if [ -f "$USER_DATA" ]; then
            echo -e "Document Root: $(grep "^documentroot" "$USER_DATA" | cut -d: -f2)"
            echo -e "Server Admin: $(grep "^serveradmin" "$USER_DATA" | cut -d: -f2)"
        else
            echo "Could not find cPanel user data for bingetv.co.ke"
            echo "Available domains:"
            ls -1 /var/cpanel/userdata/fieldte5/
        fi
    fi
    
    # Check for .htaccess in public directory
    echo -e "\n=== .htaccess in public directory ==="
    if [ -f "/home1/fieldte5/bingetv.co.ke/public/.htaccess" ]; then
        cat "/home1/fieldte5/bingetv.co.ke/public/.htaccess"
    else
        echo "No .htaccess file found in public directory"
    fi
    
    # Check directory permissions
    echo -e "\n=== Directory Permissions ==="
    echo "/home1/fieldte5:"
    ls -ld /home1/fieldte5
    echo -e "\n/home1/fieldte5/bingetv.co.ke:"
    ls -ld /home1/fieldte5/bingetv.co.ke
    echo -e "\n/home1/fieldte5/bingetv.co.ke/public:"
    ls -ld /home1/fieldte5/bingetv.co.ke/public
    
    # Check if PHP is working
    echo -e "\n=== PHP Test ==="
    if [ -f "/home1/fieldte5/bingetv.co.ke/public/test_php.php" ]; then
        echo "Test file exists at: https://bingetv.co.ke/test_php.php"
        echo "Contents:"
        cat "/home1/fieldte5/bingetv.co.ke/public/test_php.php"
    else
        echo "Creating PHP test file..."
        echo '<?php 
        header("Content-Type: text/plain");
        echo "PHP Test\n";
        echo "PHP Version: " . phpversion() . "\n";
        echo "Server Software: " . $_SERVER["SERVER_SOFTWARE"] . "\n";
        echo "Document Root: " . $_SERVER["DOCUMENT_ROOT"] . "\n";
        echo "Script Filename: " . $_SERVER["SCRIPT_FILENAME"] . "\n";
        echo "Current Working Directory: " . getcwd() . "\n";
        ?>' > "/home1/fieldte5/bingetv.co.ke/public/test_php.php"
        chmod 644 "/home1/fieldte5/bingetv.co.ke/public/test_php.php"
        echo "Test file created at: https://bingetv.co.ke/test_php.php"
    fi
    
    # Check web server error logs
    echo -e "\n=== Checking Error Logs (last 10 lines) ==="
    if [ -f "/home1/fieldte5/bingetv.co.ke/logs/error_log" ]; then
        echo "=== Application Error Log ==="
        tail -n 10 "/home1/fieldte5/bingetv.co.ke/logs/error_log"
    fi
    
    if [ -f "/usr/local/apache/logs/error_log" ]; then
        echo -e "\n=== Apache Error Log ==="
        tail -n 10 "/usr/local/apache/logs/error_log" | grep -i "bingetv"
    fi
    
    if [ -f "/var/log/nginx/error.log" ]; then
        echo -e "\n=== Nginx Error Log ==="
        tail -n 10 "/var/log/nginx/error.log" | grep -i "bingetv"
    fi
    
    echo -e "\n✅ Web server configuration check complete"
EOF

echo -e "\n${GREEN}=== NEXT STEPS ===${NC}"
echo "1. Check the output above for any misconfigurations"
echo "2. Try accessing: https://bingetv.co.ke/test_php.php"
echo "3. If you still see issues, you may need to contact Bluehost support to:"
echo "   - Verify the document root is correctly set to /home1/fieldte5/bingetv.co.ke/htdocs"
echo "   - Check for any server-level restrictions"
echo "   - Review the web server error logs for specific error messages"
