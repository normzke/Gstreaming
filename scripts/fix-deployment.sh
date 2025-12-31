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

REMOTE="bluehost:/home1/fieldte5/bingetv.co.ke"
BACKUP_DIR="/home1/fieldte5/bingetv.co.ke-backup-$(date +%Y%m%d%H%M%S)"

# Function to run commands with error checking
run_command() {
    local cmd="$1"
    local description="$2"
    
    echo -e "\n${GREEN}$description...${NC}"
    if [ $VERBOSE -eq 1 ]; then
        echo -e "${YELLOW}Command: $cmd${NC}"
    fi
    
    if [ $DRY_RUN -eq 0 ]; then
        eval "$cmd"
        if [ $? -ne 0 ]; then
            echo -e "❌ ${RED}Error: $description failed${NC}"
            exit 1
        fi
    fi
    
    echo -e "✅ ${GREEN}Success: $description${NC}"
}

# 1. Create backup of current files
run_command \
    "ssh bluehost \"mkdir -p $BACKUP_DIR && cp -r /home1/fieldte5/bingetv.co.ke/* $BACKUP_DIR/ 2>/dev/null || true\"" \
    "Creating backup of current files to $BACKUP_DIR"

# 2. Sync public directory
echo -e "\n${GREEN}=== SYNCING FILES ===${NC}"

run_command \
    "rsync $RSYNC_OPTS --delete \
    --exclude='uploads/' \
    --exclude='.DS_Store' \
    public/ $REMOTE/public/" \
    "Syncing public/ directory"

# 3. Sync user directory
run_command \
    "rsync $RSYNC_OPTS --delete \
    --exclude='.DS_Store' \
    user/ $REMOTE/user/" \
    "Syncing user/ directory"

# 4. Sync admin directory
run_command \
    "rsync $RSYNC_OPTS --delete \
    --exclude='.DS_Store' \
    admin/ $REMOTE/admin/" \
    "Syncing admin/ directory"

# 5. Sync config directory
run_command \
    "rsync $RSYNC_OPTS \
    --exclude='.DS_Store' \
    config/ $REMOTE/config/" \
    "Syncing config/ directory"

# 6. Sync lib directory
run_command \
    "rsync $RSYNC_OPTS --delete \
    --exclude='.DS_Store' \
    lib/ $REMOTE/lib/" \
    "Syncing lib/ directory"

# 7. Sync api directory if it exists
if [ -d "api" ]; then
    run_command \
        "rsync $RSYNC_OPTS --delete \
        --exclude='.DS_Store' \
        api/ $REMOTE/api/" \
        "Syncing api/ directory"
else
    echo -e "${YELLOW}⚠️  API directory not found, skipping...${NC}"
fi

# 8. Sync root .htaccess if it exists
if [ -f ".htaccess" ]; then
    run_command \
        "rsync $RSYNC_OPTS \
        .htaccess $REMOTE/.htaccess" \
        "Syncing root .htaccess"
else
    echo -e "${YELLOW}⚠️  .htaccess file not found, skipping...${NC}"
fi

# 9. Set proper permissions
run_command \
    "ssh bluehost \"
    chmod -R 755 \
        /home1/fieldte5/bingetv.co.ke/public \
        /home1/fieldte5/bingetv.co.ke/user \
        /home1/fieldte5/bingetv.co.ke/admin \
        /home1/fieldte5/bingetv.co.ke/api \
        /home1/fieldte5/bingetv.co.ke/config \
        /home1/fieldte5/bingetv.co.ke/lib && \
    chmod 644 \
        /home1/fieldte5/bingetv.co.ke/.htaccess \
        /home1/fieldte5/bingetv.co.ke/public/.htaccess \
        /home1/fieldte5/bingetv.co.ke/user/.htaccess \
        /home1/fieldte5/bingetv.co.ke/admin/.htaccess \
        2>/dev/null || true
    \"" \
    "Setting file permissions"

# Final summary
echo -e "\n${GREEN}=== DEPLOYMENT SUMMARY ===${NC}"
if [ $DRY_RUN -eq 1 ]; then
    echo -e "${YELLOW}This was a dry run. No changes were actually made.${NC}"
    echo -e "To perform the actual deployment, run: ${YELLOW}./fix-deployment.sh${NC}"
else
    echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
    echo -e "${GREEN}🔄 Files have been synchronized to: ${YELLOW}$REMOTE${NC}"
    echo -e "${GREEN}📦 Backup created at: ${YELLOW}$BACKUP_DIR${NC}"
    echo -e "\n${GREEN}Next steps:${NC}"
    echo "1. Test the website: https://bingetv.co.ke"
    echo "2. Verify admin panel: https://bingetv.co.ke/admin"
    echo "3. Check API endpoints if applicable"
    echo "4. If anything went wrong, restore from backup with:"
    echo -e "   ${YELLOW}ssh bluehost \"rm -rf /home1/fieldte5/bingetv.co.ke/* && cp -r $BACKUP_DIR/* /home1/fieldte5/bingetv.co.ke/\"${NC}"
fi
