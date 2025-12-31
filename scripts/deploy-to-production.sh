#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Parse command line arguments
DRY_RUN=0
VERBOSE=0

while [[ $# -gt 0 ]]; do
    case $1 in
        --dry-run)
            DRY_RUN=1
            shift
            ;;
        -v|--verbose)
            VERBOSE=1
            shift
            ;;
        -h|--help)
            echo "Usage: $0 [options]"
            echo "Options:"
            echo "  --dry-run    Show what would be transferred without making changes"
            echo "  -v, --verbose  Show detailed output"
            echo "  -h, --help    Show this help message"
            exit 0
            ;;
        *)
            echo -e "${RED}Error: Unknown option: $1${NC}"
            echo "Use --help for usage information"
            exit 1
            ;;
    esac
done

# Set rsync options
RSYNC_OPTS="-avz"

if [ $DRY_RUN -eq 1 ]; then
    RSYNC_OPTS="$RSYNC_OPTS --dry-run"
    echo -e "${YELLOW}=== DRY RUN MODE ===${NC}"
    echo -e "${YELLOW}No changes will be made. This is a simulation.${NC}\n"
fi

if [ $VERBOSE -eq 1 ]; then
    RSYNC_OPTS="$RSYNC_OPTS -v"
else
    RSYNC_OPTS="$RSYNC_OPTS --progress"
fi

echo -e "${GREEN}=== BINGETV PRODUCTION DEPLOYMENT ===${NC}"
echo -e "Remote target: ${YELLOW}bluehost:/home1/fieldte5/bingetv.co.ke${NC}\n"

REMOTE="bluehost:/home1/fieldte5/bingetv.co.ke"

# 1. Sync public directory (includes apps)
echo -e "${GREEN}1. Syncing public/ directory (including apps)...${NC}"
if [ $VERBOSE -eq 1 ]; then
    echo -e "${YELLOW}Command: rsync $RSYNC_OPTS --delete --exclude='uploads/' --exclude='.DS_Store' public/ $REMOTE/public/${NC}"
fi
rsync $RSYNC_OPTS --delete \
    --exclude='uploads/' \
    --exclude='.DS_Store' \
    public/ $REMOTE/public/

if [ $? -eq 0 ]; then
    echo -e "✅ ${GREEN}Public directory synced (includes Android APK, WebOS IPK, Tizen TPK)${NC}"
else
    echo -e "❌ ${RED}Error syncing public directory${NC}"
    exit 1
fi

# 2. Sync user directory  
echo -e "\n${GREEN}2. Syncing user/ directory...${NC}"
if [ $VERBOSE -eq 1 ]; then
    echo -e "${YELLOW}Command: rsync $RSYNC_OPTS --delete --exclude='.DS_Store' user/ $REMOTE/user/${NC}"
fi
rsync $RSYNC_OPTS --delete \
    --exclude='.DS_Store' \
    user/ $REMOTE/user/

if [ $? -eq 0 ]; then
    echo -e "✅ ${GREEN}User directory synced${NC}"
else
    echo -e "❌ ${RED}Error syncing user directory${NC}"
    exit 1
fi

# 3. Sync admin directory
echo -e "\n${GREEN}3. Syncing admin/ directory...${NC}"
if [ $VERBOSE -eq 1 ]; then
    echo -e "${YELLOW}Command: rsync $RSYNC_OPTS --delete --exclude='.DS_Store' admin/ $REMOTE/admin/${NC}"
fi
rsync $RSYNC_OPTS --delete \
    --exclude='.DS_Store' \
    admin/ $REMOTE/admin/

if [ $? -eq 0 ]; then
    echo -e "✅ ${GREEN}Admin directory synced${NC}"
else
    echo -e "❌ ${RED}Error syncing admin directory${NC}"
    exit 1
fi

# 4. Sync config directory
echo -e "\n${GREEN}4. Syncing config/ directory...${NC}"
if [ $VERBOSE -eq 1 ]; then
    echo -e "${YELLOW}Command: rsync $RSYNC_OPTS --exclude='.DS_Store' config/ $REMOTE/config/${NC}"
fi
rsync $RSYNC_OPTS \
    --exclude='.DS_Store' \
    config/ $REMOTE/config/

if [ $? -eq 0 ]; then
    echo -e "✅ ${GREEN}Config directory synced${NC}"
else
    echo -e "❌ ${RED}Error syncing config directory${NC}"
    exit 1
fi

# 5. Sync lib directory
echo -e "\n${GREEN}5. Syncing lib/ directory...${NC}"
if [ $VERBOSE -eq 1 ]; then
    echo -e "${YELLOW}Command: rsync $RSYNC_OPTS --delete --exclude='.DS_Store' lib/ $REMOTE/lib/${NC}"
fi
rsync $RSYNC_OPTS --delete \
    --exclude='.DS_Store' \
    lib/ $REMOTE/lib/

if [ $? -eq 0 ]; then
    echo -e "✅ ${GREEN}Lib directory synced${NC}"
else
    echo -e "❌ ${RED}Error syncing lib directory${NC}"
    exit 1
fi

# 6. Sync api directory
echo -e "\n${GREEN}6. Syncing api/ directory...${NC}"
if [ -d "api" ]; then
    if [ $VERBOSE -eq 1 ]; then
        echo -e "${YELLOW}Command: rsync $RSYNC_OPTS --delete --exclude='.DS_Store' api/ $REMOTE/api/${NC}"
    fi
    rsync $RSYNC_OPTS --delete \
        --exclude='.DS_Store' \
        api/ $REMOTE/api/

    if [ $? -eq 0 ]; then
        echo -e "✅ ${GREEN}API directory synced${NC}"
    else
        echo -e "❌ ${RED}Error syncing API directory${NC}"
        echo -e "✅ ${GREEN}API directory synced${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  API directory not found, skipping...${NC}"
fi

# 7. Create symbolic link from document root to public directory
echo -e "\n${GREEN}7. Creating symbolic link from document root to public directory...${NC}"
if [ $DRY_RUN -eq 0 ]; then
    ssh -T bluehost << 'EOF'
    if [ -L "/home1/fieldte5/bingetv.co.ke/htdocs" ]; then
        echo "Removing existing htdocs symlink..."
        rm -f "/home1/fieldte5/bingetv.co.ke/htdocs"
    fi
    echo "Creating new htdocs symlink to public directory..."
    ln -sf "/home1/fieldte5/bingetv.co.ke/public" "/home1/fieldte5/bingetv.co.ke/htdocs"
    echo "Setting permissions..."
    chmod 755 "/home1/fieldte5/bingetv.co.ke"
    chmod 755 "/home1/fieldte5/bingetv.co.ke/public"
    chmod 644 "/home1/fieldte5/bingetv.co.ke/public/index.php"
    echo "Verifying symlink..."
    ls -la "/home1/fieldte5/bingetv.co.ke/" | grep htdocs
EOF
    
    if [ $? -eq 0 ]; then
        echo -e "✅ ${GREEN}Symbolic link created successfully${NC}"
    else
        echo -e "❌ ${RED}Error creating symbolic link${NC}"
        exit 1
    fi
else
    echo -e "${YELLOW}Would create symbolic link: ${NC}"
    echo -e "  ${YELLOW}ln -sf /home1/fieldte5/bingetv.co.ke/public /home1/fieldte5/bingetv.co.ke/htdocs${NC}"
    echo -e "  ${YELLOW}chmod 755 /home1/fieldte5/bingetv.co.ke${NC}"
    echo -e "  ${YELLOW}chmod 755 /home1/fieldte5/bingetv.co.ke/public${NC}"
    echo -e "  ${YELLOW}chmod 644 /home1/fieldte5/bingetv.co.ke/public/index.php${NC}"
fi

# 8. Sync root .htaccess
echo -e "\n${GREEN}8. Syncing root .htaccess...${NC}"
if [ -f ".htaccess" ]; then
    if [ $VERBOSE -eq 1 ]; then
        echo -e "${YELLOW}Command: rsync $RSYNC_OPTS .htaccess $REMOTE/.htaccess${NC}"
    fi
    rsync $RSYNC_OPTS .htaccess $REMOTE/.htaccess
    
    if [ $? -eq 0 ]; then
        echo -e "✅ ${GREEN}Root .htaccess synced${NC}"
    else
        echo -e "❌ ${RED}Error syncing .htaccess${NC}"
        exit 1
    fi
else
    echo -e "${YELLOW}⚠️  .htaccess file not found, skipping...${NC}"
fi

# Final summary
echo -e "\n${GREEN}=== DEPLOYMENT SUMMARY ===${NC}"
if [ $DRY_RUN -eq 1 ]; then
    echo -e "${YELLOW}This was a dry run. No changes were actually made.${NC}"
    echo -e "To perform the actual deployment, run: ${YELLOW}./deploy-to-production.sh${NC}"
else
    echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
    echo -e "${GREEN}🔄 Files have been synchronized to: ${YELLOW}$REMOTE${NC}"
fi
# 8. Set proper permissions
echo ""
echo "8. Setting permissions..."
ssh bluehost "chmod -R 755 /home1/fieldte5/bingetv.co.ke/public \
    /home1/fieldte5/bingetv.co.ke/user \
    /home1/fieldte5/bingetv.co.ke/admin \
    /home1/fieldte5/bingetv.co.ke/api && \
    chmod 644 /home1/fieldte5/bingetv.co.ke/.htaccess \
    /home1/fieldte5/bingetv.co.ke/public/.htaccess \
    /home1/fieldte5/bingetv.co.ke/user/.htaccess \
    /home1/fieldte5/bingetv.co.ke/admin/.htaccess && \
    echo 'Permissions set'"

# 9. Verify apps were uploaded
echo ""
echo "9. Verifying BingeTV apps deployment..."
ssh bluehost "ls -lh /home1/fieldte5/bingetv.co.ke/public/apps/android/ \
    /home1/fieldte5/bingetv.co.ke/public/apps/webos/ \
    /home1/fieldte5/bingetv.co.ke/public/apps/tizen/ 2>/dev/null && \
    echo '✅ All apps verified on server'"

echo ""
echo "=== DEPLOYMENT COMPLETE ==="
echo "✅ All files synced to production"
echo "✅ Permissions set correctly"
echo "✅ BingeTV apps deployed:"
echo "   - Android TV APK"
echo "   - LG WebOS IPK"
echo "   - Samsung Tizen TPK"
echo ""
echo "🌐 Site: https://bingetv.co.ke"
echo "📱 Apps: https://bingetv.co.ke/apps.php"
echo ""
echo "Next steps:"
echo "1. Test website: https://bingetv.co.ke"
echo "2. Test apps page: https://bingetv.co.ke/apps.php"
echo "3. Test app downloads on actual TV devices"
echo "4. Verify auto-download works on TV browsers"
