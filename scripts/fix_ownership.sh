#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "\n${GREEN}=== FIXING OWNERSHIP ===${NC}"
echo -e "Remote target: ${YELLOW}bluehost:/home1/fieldte5/bingetv.co.ke${NC}\n"

# 1. Fix file ownership
echo -e "${GREEN}1. Updating ownership to match web server user (nobody)...${NC}"

ssh -T bluehost << 'EOF'
    REMOTE_PATH="/home1/fieldte5/bingetv.co.ke"
    
    # Change ownership to nobody:nobody
    echo "Updating ownership to nobody:nobody..."
    chown -R nobody:nobody "$REMOTE_PATH"
    
    # But keep the directory owned by fieldte5 for FTP access
    echo "Restoring fieldte5 ownership on parent directory..."
    chown fieldte5:fieldte5 "$REMOTE_PATH"
    
    # Make sure the web server can still access the files
    echo "Setting group write permissions..."
    chmod g+w -R "$REMOTE_PATH"
    
    # Verify ownership
    echo -e "\nVerification:"
    echo "Parent directory:"
    ls -ld "$REMOTE_PATH"
    echo -e "\nPublic directory:"
    ls -ld "$REMOTE_PATH/public"
    echo -e "\nIndex.php:"
    ls -l "$REMOTE_PATH/public/index.php"
    
    echo -e "\n✅ Ownership updated"
EOF

echo -e "\n${GREEN}=== NEXT STEPS ===${NC}"
echo "1. Test the website: https://bingetv.co.ke"
echo "2. If you still see issues, you may need to:"
echo "   - Contact Bluehost support to change the web server user"
echo "   - Or set up proper group permissions"
echo "   - Or move the site to a different directory with correct ownership"
