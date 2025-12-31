#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

REMOTE="bluehost"
REMOTE_DIR="/home1/fieldte5/bingetv.co.ke"
TEMP_DIR="/tmp/bingetv-cleanup-$(date +%s)"

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

# 3. Move files to their correct locations
# Move public files
run_command \
    "ssh $REMOTE \"
    mv $REMOTE_DIR/index.php $REMOTE_DIR/public/ 2>/dev/null || true && \
    mv $REMOTE_DIR/*.php $REMOTE_DIR/public/ 2>/dev/null || true && \
    mv $REMOTE_DIR/*.html $REMOTE_DIR/public/ 2>/dev/null || true && \
    mv $REMOTE_DIR/*.txt $REMOTE_DIR/public/ 2>/dev/null || true && \
    mv $REMOTE_DIR/*.md $REMOTE_DIR/public/ 2>/dev/null || true && \
    mv $REMOTE_DIR/*.apk $REMOTE_DIR/public/ 2>/dev/null || true
    \"" \
    "Moving public files to public/ directory"

# Move specific directories to public/
run_command \
    "ssh $REMOTE \"
    mv $REMOTE_DIR/css $REMOTE_DIR/public/ 2>/dev/null || true && \
    mv $REMOTE_DIR/js $REMOTE_DIR/public/ 2>/dev/null || true && \
    mv $REMOTE_DIR/images $REMOTE_DIR/public/ 2>/dev/null || true && \
    mv $REMOTE_DIR/apps $REMOTE_DIR/public/ 2>/dev/null || true
    \"" \
    "Moving web assets to public/ directory"

# 4. Clean up any remaining files
echo -e "\n${YELLOW}Cleaning up...${NC}"
run_command \
    "ssh $REMOTE \"
    # Move any remaining PHP/HTML files to temp for review
    find $REMOTE_DIR -maxdepth 1 -type f \( -name '*.php' -o -name '*.html' \) -exec mv {} $REMOTE_DIR/temp-cleanup/ \; 2>/dev/null || true
    \"" \
    "Moving remaining files to temp-cleanup for review"

# 5. Set proper permissions
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
    "Setting proper permissions"

# 6. Final report
echo -e "\n${GREEN}=== CLEANUP COMPLETE ===${NC}"
echo -e "✅ ${GREEN}Remote directory has been reorganized${NC}"
echo -e "📦 ${YELLOW}Backup created at: ${REMOTE}:${TEMP_DIR}${NC}"
echo -e "📁 ${YELLOW}Files for review at: ${REMOTE}:${REMOTE_DIR}/temp-cleanup${NC}"
echo -e "\n${GREEN}Directory structure:${NC}"
ssh $REMOTE "find $REMOTE_DIR -maxdepth 2 -type d | sort"

echo -e "\n${YELLOW}Next steps:${NC}"
echo "1. Test the website: https://bingetv.co.ke"
echo "2. If anything went wrong, restore from backup with:"
echo -e "   ${YELLOW}ssh $REMOTE \"rm -rf $REMOTE_DIR/* && cp -r $TEMP_DIR/* $REMOTE_DIR/\"${NC}"
