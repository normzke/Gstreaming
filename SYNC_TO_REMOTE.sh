#!/bin/bash

# BingeTV - Sync Script (Interactive)
# This script will prompt for your SSH password
# Run: bash SYNC_TO_REMOTE.sh

REMOTE_USER="fieldte5"
REMOTE_HOST="bingetv.co.ke"
REMOTE_PATH="/home1/fieldte5/bingetv.co.ke/"

echo "=========================================="
echo "BingeTV - Remote Sync Script"
echo "=========================================="
echo ""
echo "This will sync the following files:"
echo "  ✓ Root .htaccess"
echo "  ✓ User portal files (dashboard, channels)"
echo "  ✓ User payments (submit-mpesa.php)"
echo "  ✓ Admin files (manual-payments.php, header)"
echo "  ✓ Database migrations"
echo ""
echo "You will be prompted for SSH password multiple times."
echo "Press Ctrl+C to cancel, or Enter to continue..."
read

echo ""
echo "=== Syncing Files ==="
echo ""

# Sync user dashboard
echo "→ Syncing user/dashboard/index.php..."
rsync -avz user/dashboard/index.php "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}user/dashboard/"

# Sync user channels
echo "→ Syncing user/channels.php..."
rsync -avz user/channels.php "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}user/"

# Sync user payments directory
echo "→ Syncing user/payments/submit-mpesa.php..."
rsync -avz user/payments/submit-mpesa.php "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}user/payments/"

# Sync admin manual-payments
echo "→ Syncing admin/manual-payments.php..."
rsync -avz admin/manual-payments.php "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}admin/"

# Sync admin header
echo "→ Syncing admin/includes/header.php..."
rsync -avz admin/includes/header.php "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}admin/includes/"

# Sync database migration
echo "→ Syncing database migration..."
rsync -avz database/migrations/010_manual_mpesa_confirmations.sql "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}database/migrations/"

# Sync migration runner
echo "→ Syncing run_migration_010.php..."
rsync -avz run_migration_010.php "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}"

echo ""
echo "=== Setting Permissions ==="
echo ""

ssh "${REMOTE_USER}@${REMOTE_HOST}" << 'ENDSSH'
cd /home1/fieldte5/bingetv.co.ke/

# Set file permissions
chmod 644 user/dashboard/index.php
chmod 644 user/channels.php
chmod 644 user/payments/submit-mpesa.php
chmod 644 admin/manual-payments.php
chmod 644 admin/includes/header.php
chmod 644 database/migrations/010_manual_mpesa_confirmations.sql
chmod 644 run_migration_010.php

echo "✅ Permissions set"
ENDSSH

echo ""
echo "=========================================="
echo "✅ Sync Complete!"
echo "=========================================="
echo ""
echo "Next Steps:"
echo "1. Visit: https://bingetv.co.ke/run_migration_010.php"
echo "2. Verify migration success"
echo "3. Delete run_migration_010.php"
echo "4. Test all pages"
echo ""

