#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Remote server details
REMOTE="bluehost"
REMOTE_DIR="/home1/fieldte5/bingetv.co.ke"
TEMP_DIR="/tmp/bingetv-deploy-$(date +%s)"

# Function to run commands with error checking
run_command() {
    local cmd="$1"
    local description="$2"
    
    echo -e "\n${GREEN}$description...${NC}"
    echo -e "${YELLOW}Command: $cmd${NC}"
    
    eval "$cmd"
    if [ $? -ne 0 ]; then
        echo -e "❌ ${RED}Error: $description failed${NC}"
        exit 1
    fi
    
    echo -e "✅ ${GREEN}Success: $description${NC}"
}

# 1. Create a backup of current files
run_command \
    "ssh $REMOTE \"mkdir -p $TEMP_DIR && cp -r $REMOTE_DIR/* $TEMP_DIR/ 2>/dev/null || true\"" \
    "Creating backup of current files to $TEMP_DIR"

# 2. Create necessary directories
run_command \
    "ssh $REMOTE \"
    mkdir -p $REMOTE_DIR/public \
             $REMOTE_DIR/user \
             $REMOTE_DIR/admin \
             $REMOTE_DIR/config \
             $REMOTE_DIR/lib \
             $REMOTE_DIR/api \
             $REMOTE_DIR/temp-cleanup
    \"" \
    "Creating necessary directories"

# 3. Sync local files to remote
run_command \
    "rsync -avz --delete \
    --exclude='.git' \
    --exclude='.DS_Store' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.env' \
    --exclude='*.log' \
    --exclude='*.sql' \
    --exclude='*.zip' \
    --exclude='*.tar.gz' \
    --exclude='.htaccess' \
    /Users/la/Downloads/Bingetv/ $REMOTE:$REMOTE_DIR/" \
    "Syncing files to remote server"

# 4. Set proper permissions
run_command \
    "ssh $REMOTE \"
    chmod -R 755 $REMOTE_DIR/public \
                 $REMOTE_DIR/user \
                 $REMOTE_DIR/admin \
                 $REMOTE_DIR/api \
                 $REMOTE_DIR/config \
                 $REMOTE_DIR/lib && \
    chmod 644 $REMOTE_DIR/.htaccess \
              $REMOTE_DIR/public/.htaccess \
              $REMOTE_DIR/user/.htaccess \
              $REMOTE_DIR/admin/.htaccess 2>/dev/null || true
    \"" \
    "Setting file permissions"

# 5. Final report
echo -e "\n${GREEN}=== DEPLOYMENT COMPLETE ===${NC}"
echo -e "✅ ${GREEN}Files have been deployed to: ${YELLOW}$REMOTE:$REMOTE_DIR${NC}"
echo -e "📦 ${YELLOW}Backup created at: ${REMOTE}:${TEMP_DIR}${NC}"
echo -e "\n${YELLOW}Next steps:${NC}"
echo "1. Test the website: https://bingetv.co.ke"
echo "2. Verify admin panel: https://bingetv.co.ke/admin"
echo "3. If anything went wrong, restore from backup with:"
echo -e "   ${YELLOW}ssh $REMOTE \"rm -rf $REMOTE_DIR/* && cp -r $TEMP_DIR/* $REMOTE_DIR/\"${NC}"
