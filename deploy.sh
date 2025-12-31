#!/bin/bash

# Deployment script for syncing local changes to Bluehost cPanel
# Usage: ./deploy.sh [--dry-run] [--force]

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Load configuration from environment or use defaults
LOCAL_PATH="/Users/la/Downloads/Bingetv"
REMOTE_HOST=""  # Will be prompted if not set
REMOTE_PATH="~/public_html"  # Default Bluehost path, update if different

# Directories to sync (relative to project root)
SYNC_DIRS=(
    "admin"
    "config"
    "includes"
    "lib"
    "public"
    "user"
)

# Individual files to sync (relative to project root)
SYNC_FILES=(
    ".htaccess"
    "index.php"
)

# Files to exclude (patterns, not paths)
EXCLUDE=(
    "*.log"
    "*.tmp"
    "*.swp"
    ".DS_Store"
    "*.bak"
    "*.backup"
    "storage/*"
    "vendor/*"
    "node_modules/*"
)

# Post-deploy commands to run on the remote server
POST_DEPLOY=(
    "find . -type d -exec chmod 755 {} \\;"
    "find . -type f -exec chmod 644 {} \\;"
    "chmod 600 config/config.php"
    "chmod -R 755 public/uploads"
    "chmod -R 755 storage"
)

# Parse command line arguments
DRY_RUN=0
FORCE=0
VERBOSE=0

while [[ $# -gt 0 ]]; do
    case $1 in
        --dry-run)
            DRY_RUN=1
            shift
            ;;
        --force)
            FORCE=1
            shift
            ;;
        -v|--verbose)
            VERBOSE=1
            shift
            ;;
        -h|--help)
            echo "Usage: $0 [options]"
            echo "Options:"
            echo "  --dry-run    Show what would be transferred"
            echo "  --force      Force overwrite of existing files"
            echo "  -v, --verbose  Show more detailed output"
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
    echo -e "${YELLOW}Running in dry-run mode - no changes will be made${NC}"
    RSYNC_OPTS="$RSYNC_OPTS --dry-run"
fi

if [ $FORCE -eq 1 ]; then
    RSYNC_OPTS="$RSYNC_OPTS --delete"
fi

if [ $VERBOSE -eq 1 ]; then
    RSYNC_OPTS="$RSYNC_OPTS -v"
else
    RSYNC_OPTS="$RSYNC_OPTS --progress"
fi

# Build exclude string
exclude_opts=()
for pattern in "${EXCLUDE[@]}"; do
    exclude_opts+=(--exclude="$pattern")
done

# Function to run commands on remote server
run_remote() {
    local cmd="$1"
    echo -e "${GREEN}Running on remote:${NC} $cmd"
    if [ $DRY_RUN -eq 0 ]; then
        ssh -t $REMOTE_HOST "cd $REMOTE_PATH && $cmd"
    else
        echo -e "${YELLOW}[DRY RUN] Would run:${NC} $cmd"
    fi
}

# Get remote host from environment or prompt if not set
if [ -z "$REMOTE_HOST" ]; then
    read -p "Enter SSH connection string (e.g., username@server.bluehost.com): " REMOTE_HOST
fi

# Validate remote host
if [ -z "$REMOTE_HOST" ]; then
    echo -e "${RED}Error: No remote host specified${NC}"
    exit 1
fi

echo -e "${GREEN}Starting deployment to ${REMOTE_HOST}:${REMOTE_PATH}${NC}"

# Create remote directories if they don't exist
echo -e "${GREEN}Creating remote directories...${NC}"
for dir in "${SYNC_DIRS[@]}"; do
    run_remote "mkdir -p \"$dir\""
done

# Sync directories
echo -e "${GREEN}Syncing directories...${NC}"
for dir in "${SYNC_DIRS[@]}"; do
    echo -e "${GREEN}Syncing:${NC} $dir/"
    rsync $RSYNC_OPTS "${exclude_opts[@]}" \
        -e ssh \
        "$LOCAL_PATH/$dir/" \
        "$REMOTE_HOST:$REMOTE_PATH/$dir/"
    
    # Check rsync exit status
    if [ $? -ne 0 ]; then
        echo -e "${RED}Error syncing $dir/${NC}"
        exit 1
    fi
done

# Sync individual files
echo -e "${GREEN}Syncing individual files...${NC}"
for file in "${SYNC_FILES[@]}"; do
    if [ -f "$LOCAL_PATH/$file" ]; then
        echo -e "${GREEN}Syncing:${NC} $file"
        rsync $RSYNC_OPTS "${exclude_opts[@]}" \
            -e ssh \
            "$LOCAL_PATH/$file" \
            "$REMOTE_HOST:$REMOTE_PATH/"
        
        # Check rsync exit status
        if [ $? -ne 0 ]; then
            echo -e "${RED}Error syncing $file${NC}"
            exit 1
        fi
    else
        echo -e "${YELLOW}Warning: File not found: $file${NC}"
    fi
done

# Run post-deploy commands
echo -e "${GREEN}Running post-deploy commands...${NC}"
for cmd in "${POST_DEPLOY[@]}"; do
    run_remote "$cmd"
done

# Final status
echo -e "\n${GREEN}Deployment complete!${NC}"

# Show disk usage
echo -e "\n${GREEN}Remote disk usage:${NC}"
run_remote "df -h ."

echo -e "\n${GREEN}Deployment to $REMOTE_HOST completed successfully!${NC}"

if [ $DRY_RUN -eq 1 ]; then
    echo -e "\n${YELLOW}This was a dry run. No changes were actually made.${NC}"
fi
