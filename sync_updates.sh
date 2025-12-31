#!/bin/bash

# Sync Updates Script
# Syncs only the files modified during the Auth & Session Fixes
# Usage: ./sync_updates.sh [remote_host]

# Configuration
LOCAL_PATH="/Users/la/Downloads/Bingetv"
# Default remote path based on documentation, can be overridden by user
REMOTE_PATH="/home1/fieldte5/bingetv.co.ke"

# Files to sync (relative to project root)
FILES_TO_SYNC=(
    "lib/email.php"
    "admin/packages.php"
    "admin/channels.php"
    "admin/users.php"
    "admin/subscriptions.php"
    "public/images/site.webmanifest"
)

# Remote Host
REMOTE_HOST="$1"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== BingeTV Auth Fixes Sync ===${NC}"

# Check for remote host argument
if [ -z "$REMOTE_HOST" ]; then
    echo -e "${YELLOW}No remote host provided.${NC}"
    read -p "Enter SSH connection string (e.g., username@server_ip): " REMOTE_HOST
fi

if [ -z "$REMOTE_HOST" ]; then
    echo -e "${RED}Error: Remote host is required.${NC}"
    exit 1
fi

echo -e "Syncing to: ${GREEN}${REMOTE_HOST}:${REMOTE_PATH}${NC}"
echo -e "Files to sync:"
for file in "${FILES_TO_SYNC[@]}"; do
    echo -e " - $file"
done

# Confirm
read -p "Continue? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 1
fi

# Sync Loop
for file in "${FILES_TO_SYNC[@]}"; do
    echo -ne "Syncing $file... "
    rsync -az -e ssh "$LOCAL_PATH/$file" "$REMOTE_HOST:$REMOTE_PATH/$file"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}OK${NC}"
    else
        echo -e "${RED}FAILED${NC}"
        HAS_ERROR=1
    fi
done

if [ -z "$HAS_ERROR" ]; then
    # Sync Admin Files
for file in "${ADMIN_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "Syncing $file..."
        rsync -avz -e "ssh -p 2222" "$file" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/$file"
    else
        echo "Warning: $file not found locally"
    fi
done

echo -e "\n${GREEN}All files synced successfully!${NC}"
else
    echo -e "\n${RED}Some files failed to sync. Check errors above.${NC}"
fi
