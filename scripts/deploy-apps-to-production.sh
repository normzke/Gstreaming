#!/bin/bash

# BingeTV Apps Deployment Script
# Syncs Android, Tizen, and WebOS apps to production server

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuration
LOCAL_APPS_DIR="/Users/la/Downloads/Bingetv/apps"
REMOTE_HOST="fieldte5@bingetv.co.ke"
REMOTE_PATH="/home1/fieldte5/bingetv.co.ke/public/apps"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}BingeTV Apps Deployment${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if files exist locally
echo -e "${YELLOW}Checking local files...${NC}"

if [ ! -f "$LOCAL_APPS_DIR/android/bingetv-android-tv.apk" ]; then
    echo -e "${RED}Error: Android APK not found${NC}"
    exit 1
fi

if [ ! -f "$LOCAL_APPS_DIR/tizen/com.bingetv.app-1.0.0.tpk" ]; then
    echo -e "${RED}Error: Tizen TPK not found${NC}"
    exit 1
fi

if [ ! -f "$LOCAL_APPS_DIR/webos/com.bingetv.app_1.0.0_all.ipk" ]; then
    echo -e "${RED}Error: WebOS IPK not found${NC}"
    exit 1
fi

echo -e "${GREEN}✓ All APK files found${NC}"
echo ""

# Show file sizes
echo -e "${YELLOW}File sizes:${NC}"
ls -lh "$LOCAL_APPS_DIR/android/bingetv-android-tv.apk" | awk '{print "  Android: " $5}'
ls -lh "$LOCAL_APPS_DIR/tizen/com.bingetv.app-1.0.0.tpk" | awk '{print "  Tizen:   " $5}'
ls -lh "$LOCAL_APPS_DIR/webos/com.bingetv.app_1.0.0_all.ipk" | awk '{print "  WebOS:   " $5}'
echo ""

# Confirm deployment
read -p "Deploy to production? (y/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}Deployment cancelled${NC}"
    exit 0
fi

# Deploy Android APK
echo -e "${GREEN}Deploying Android APK...${NC}"
rsync -avz --progress \
    "$LOCAL_APPS_DIR/android/bingetv-android-tv.apk" \
    "$REMOTE_HOST:$REMOTE_PATH/android/"

if [ $? -ne 0 ]; then
    echo -e "${RED}Error deploying Android APK${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Android APK deployed${NC}"
echo ""

# Deploy Tizen TPK
echo -e "${GREEN}Deploying Tizen TPK...${NC}"
rsync -avz --progress \
    "$LOCAL_APPS_DIR/tizen/com.bingetv.app-1.0.0.tpk" \
    "$REMOTE_HOST:$REMOTE_PATH/tizen/"

if [ $? -ne 0 ]; then
    echo -e "${RED}Error deploying Tizen TPK${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Tizen TPK deployed${NC}"
echo ""

# Deploy WebOS IPK
echo -e "${GREEN}Deploying WebOS IPK...${NC}"
rsync -avz --progress \
    "$LOCAL_APPS_DIR/webos/com.bingetv.app_1.0.0_all.ipk" \
    "$REMOTE_HOST:$REMOTE_PATH/webos/"

if [ $? -ne 0 ]; then
    echo -e "${RED}Error deploying WebOS IPK${NC}"
    exit 1
fi
echo -e "${GREEN}✓ WebOS IPK deployed${NC}"
echo ""

# Set proper permissions
echo -e "${GREEN}Setting file permissions...${NC}"
ssh "$REMOTE_HOST" "chmod 644 $REMOTE_PATH/android/bingetv-android-tv.apk"
ssh "$REMOTE_HOST" "chmod 644 $REMOTE_PATH/tizen/com.bingetv.app-1.0.0.tpk"
ssh "$REMOTE_HOST" "chmod 644 $REMOTE_PATH/webos/com.bingetv.app_1.0.0_all.ipk"
echo -e "${GREEN}✓ Permissions set${NC}"
echo ""

# Verify deployment
echo -e "${GREEN}Verifying deployment...${NC}"
ssh "$REMOTE_HOST" "ls -lh $REMOTE_PATH/android/bingetv-android-tv.apk $REMOTE_PATH/tizen/com.bingetv.app-1.0.0.tpk $REMOTE_PATH/webos/com.bingetv.app_1.0.0_all.ipk"
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Deployment Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Download URLs:${NC}"
echo -e "  Android: https://bingetv.co.ke/apps/android/bingetv-android-tv.apk"
echo -e "  Tizen:   https://bingetv.co.ke/apps/tizen/com.bingetv.app-1.0.0.tpk"
echo -e "  WebOS:   https://bingetv.co.ke/apps/webos/com.bingetv.app_1.0.0_all.ipk"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo -e "  1. Test download links in browser"
echo -e "  2. Verify QR codes on https://bingetv.co.ke/apps"
echo -e "  3. Test auto-detection on TV browsers"
echo ""
