#!/bin/bash

# Pre-Deployment Cleanup Script
# Removes redundant .md files and build scripts before deployment

echo "=== BingeTV Pre-Deployment Cleanup ==="
echo ""

# Create backup directory
BACKUP_DIR="backup_before_cleanup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

echo "1. Creating backup of .md files..."
find . -name "*.md" -type f -not -path "./node_modules/*" -not -path "./.git/*" | while read file; do
    mkdir -p "$BACKUP_DIR/$(dirname "$file")"
    cp "$file" "$BACKUP_DIR/$file"
done
echo "✅ Backup created in $BACKUP_DIR"

echo ""
echo "2. Removing redundant .md files from root..."
# Keep only essential README files
rm -f FINAL_STATUS.md
rm -f STREAMING_APPS_IMPLEMENTATION_PLAN.md
rm -f MANUAL_MPESA_SYSTEM.md
rm -f NAVIGATION_FIX_COMPLETE.md
rm -f SITE_STATUS.md
rm -f EXTRACTION_STATUS.md
rm -f COMPLETE_LINK_TEST.md
rm -f PAYMENT_AND_DEVICES_UPDATE.md
rm -f ALL_PAGES_TEST.md
rm -f FIXES_SUMMARY.md
rm -f APPS_INTEGRATION_SUMMARY.md
rm -f PRICING_IMPLEMENTATION_PLAN.md
rm -f README_PULL_SCRIPT.md
rm -f FINAL_UPDATES_SUMMARY.md
rm -f COMPLETE_SYSTEM_READY.md
rm -f STRUCTURE_MAPPING.md
rm -f BUILD_SUMMARY.md
rm -f REGISTRATION_TEST_COMPLETE.md
rm -f SIDEBAR_NAVIGATION_UPDATE.md
rm -f COMPLETE_IMPLEMENTATION_SUMMARY.md
rm -f MANUAL_FIX_INSTRUCTIONS.md
rm -f EXTRACTION_INSTRUCTIONS.md
rm -f EMAIL_SETUP_GUIDE.md
rm -f PENDING_TASKS.md
rm -f CRITICAL_DATABASE_FIX.md
rm -f FIX_REGISTRATION_DATABASE.md
rm -f DEPLOYMENT_COMPLETE_*.md
rm -f APPS_COMPLETE_READY_TO_DEPLOY.md
rm -f README_APPS_DEPLOYMENT.md
rm -f BUILD_SUMMARY_REPORT.md

echo "✅ Redundant .md files removed"

echo ""
echo "3. Removing build scripts from apps/..."
rm -f apps/BUILD_ALL_APPS.sh
rm -f apps/BUILD_AND_DEPLOY_GUIDE.md
rm -f apps/COMPLETE_INTEGRATION_GUIDE.md
rm -f apps/android/build-apk.sh
rm -f apps/android/BUILD_QUICK_GUIDE.md
rm -f apps/android/BUILD_INSTRUCTIONS.md
rm -f apps/android/PLATFORM_COMPATIBILITY.md
rm -f apps/android/PROJECT_SUMMARY.md
rm -f apps/android/QUICK_BUILD.md
rm -f apps/webos/README.md
rm -f apps/tizen/README.md

echo "✅ Build scripts removed"

echo ""
echo "4. Removing development scripts..."
rm -f SYNC_TO_REMOTE.sh
rm -f PULL_FROM_REMOTE.sh
rm -f scripts/sync-all-fixes.sh
rm -f scripts/pull-from-remote.sh
rm -f setup_streaming_database.php

echo "✅ Development scripts removed"

echo ""
echo "5. Cleaning up test files..."
rm -rf tests/
rm -rf docs/deployment/

echo "✅ Test files removed"

echo ""
echo "6. Removing Android build artifacts..."
rm -rf apps/android/app/build/
rm -rf apps/android/.gradle/
rm -rf apps/android/build/

echo "✅ Build artifacts cleaned"

echo ""
echo "=== Cleanup Summary ==="
echo "✅ Backup created: $BACKUP_DIR"
echo "✅ Redundant .md files removed"
echo "✅ Build scripts removed"
echo "✅ Development files cleaned"
echo "✅ Test files removed"
echo ""
echo "📦 Application is now ready for deployment!"
echo ""
echo "Next steps:"
echo "1. Review the changes"
echo "2. Run: ./scripts/proper-sync.sh"
echo "3. Verify deployment at https://bingetv.co.ke"
