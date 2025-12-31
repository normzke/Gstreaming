#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

REMOTE_PATH="/home1/fieldte5/bingetv.co.ke"
BACKUP_PATH="/home1/fieldte5/backup_latest"

# Check if backup directory exists
echo -e "${YELLOW}=== Checking Backup Directory ===${NC}"
if ! ssh bluehost "[ -d \"$BACKUP_PATH\" ]"; then
    echo -e "${RED}Backup directory not found at $BACKUP_PATH${NC}"
    echo -e "${YELLOW}Looking for other backup directories...${NC}"
    echo -e "${YELLOW}Available backups:${NC}"
    ssh bluehost "find /home1/fieldte5 -maxdepth 1 -type d -name 'backup_*' -o -name 'bkp_*' | sort -r | head -5"
    echo -e "\n${YELLOW}Please enter the correct backup path:${NC}"
    read -r BACKUP_PATH
fi

echo -e "\n${GREEN}=== Comparing Files ===${NC}"
echo -e "Remote: $REMOTE_PATH"
echo -e "Backup: $BACKUP_PATH\n"

# 1. Check for missing files in current compared to backup
echo -e "${YELLOW}1. Files in backup but missing in current:${NC}"
ssh bluehost "cd $BACKUP_PATH && find . -type f | while read -r file; do if [ ! -f \"$REMOTE_PATH/\$file\" ]; then echo \"MISSING: \$file\"; fi; done"

# 2. Check for modified files
echo -e "\n${YELLOW}2. Modified files (different from backup):${NC}"
ssh bluehost "cd $BACKUP_PATH && find . -type f | while read -r file; do if [ -f \"$REMOTE_PATH/\$file\" ]; then if ! cmp -s \"\$file\" \"$REMOTE_PATH/\$file\"; then echo \"MODIFIED: \$file\"; fi; fi; done"

# 3. Check for extra files in current not in backup
echo -e "\n${YELLOW}3. Extra files in current not in backup:${NC}"
ssh bluehost "cd $REMOTE_PATH && find . -type f | while read -r file; do if [ ! -f \"$BACKUP_PATH/\$file\" ]; then echo \"EXTRA: \$file\"; fi; done"

# 4. Check important directories
echo -e "\n${GREEN}=== Checking Important Directories ===${NC}"

check_dir() {
    local dir=$1
    echo -e "\n${YELLOW}Checking $dir:${NC}"
    
    # Check if directory exists
    if ! ssh bluehost "[ -d \"$REMOTE_PATH/$dir\" ]"; then
        echo -e "${RED}Directory $dir is missing${NC}"
        return
    fi
    
    # Count files
    local file_count=$(ssh bluehost "find \"$REMOTE_PATH/$dir\" -type f | wc -l")
    echo -e "Files: $file_count"
    
    # List first few files
    echo -e "\nFirst 5 files:"
    ssh bluehost "find \"$REMOTE_PATH/$dir\" -type f | head -5"
}

# Check important directories
check_dir "app"
check_dir "config"
check_dir "public"
check_dir "templates"
check_dir "vendor"
check_dir "logs"

# 5. Check database configuration
echo -e "\n${GREEN}=== Checking Database Configuration ===${NC}"
echo -e "Current config.php:"
ssh bluehost "grep -A 10 'DB_' $REMOTE_PATH/config/config.php 2>/dev/null || echo 'config.php not found or DB_ constants not found'"

echo -e "\nBackup config.php:"
ssh bluehost "grep -A 10 'DB_' $BACKUP_PATH/config/config.php 2>/dev/null || echo 'Backup config.php not found or DB_ constants not found'"

# 6. Check .htaccess files
echo -e "\n${GREEN}=== Checking .htaccess Files ===${NC}"
echo -e "Current .htaccess in public/:"
ssh bluehost "cat $REMOTE_PATH/public/.htaccess 2>/dev/null || echo 'No .htaccess found in public/'"

echo -e "\nBackup .htaccess in public/:"
ssh bluehost "cat $BACKUP_PATH/public/.htaccess 2>/dev/null || echo 'No .htaccess found in backup public/'"

echo -e "\n${GREEN}=== Comparison Complete ===${NC}"
echo -e "Use this information to identify any missing or modified files that might be causing issues."
echo -e "To restore a file from backup, you can use:"
echo -e "  scp bluehost:$BACKUP_PATH/path/to/file ./local/path/"
echo -e "  scp ./local/path/file bluehost:$REMOTE_PATH/path/to/file\n"
