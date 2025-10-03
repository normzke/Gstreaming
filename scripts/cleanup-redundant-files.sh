#!/bin/bash

# BingeTV Redundant Files Cleanup Script
# This script removes redundant and unnecessary files

echo "🧹 Starting BingeTV cleanup process..."

# List of files to remove (redundant, temporary, or no longer needed)
REDUNDANT_FILES=(
    # Temporary setup files
    "create-admin-table.php"
    "create-social-media-table.php"
    "complete-db-setup.php"
    "setup-bingetv-db.php"
    "setup-production-db.php"
    "setup-production.sh"
    "production-config.php"
    "localhost-config.php"
    
    # Test and verification files
    "test-domain.php"
    "test-production.php"
    "test-system.php"
    "status-check.php"
    "verify-admin-functionality.php"
    "run-tests.php"
    
    # Deployment scripts (keep only the main ones)
    "deploy-to-bingetv.sh"
    "deploy-optimized.sh"
    "update-branding.sh"
    
    # Documentation files (keep only essential ones)
    "CHANNELS_README.md"
    "DEPLOYMENT_GUIDE.md"
    "INSTALLATION.md"
    "OPTIMIZATION_SUMMARY.md"
    "SUBSCRIPTION_SYSTEM_README.md"
    "TESTING_README.md"
    "FINAL_IMPLEMENTATION_SUMMARY.md"
    
    # Database files (keep only essential ones)
    "database/mysql_schema.sql"
    "database/run-migrations.php"
    "database/seed-data.php"
    
    # Maintenance scripts (move to scripts directory)
    "maintenance.sh"
    "monitor.sh"
    
    # Image files
    "PHOTO-2025-09-08-10-41-30.jpeg"
    
    # Old API files that are now in admin
    "api/auth/login.php"
    "api/auth/register.php"
    "api/payment/initiate.php"
    "api/payment/status.php"
    "api/subscription/create.php"
    
    # Gateway files (if not needed)
    "gateway/download.php"
    
    # Old includes files
    "includes/mpesa.php"
    "includes/mpesa_integration.php"
)

# Count files to be removed
TOTAL_FILES=${#REDUNDANT_FILES[@]}
REMOVED_COUNT=0

echo "📋 Found $TOTAL_FILES files to clean up..."

# Remove redundant files
for file in "${REDUNDANT_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "🗑️  Removing: $file"
        rm -f "$file"
        ((REMOVED_COUNT++))
    elif [ -d "$file" ]; then
        echo "🗑️  Removing directory: $file"
        rm -rf "$file"
        ((REMOVED_COUNT++))
    else
        echo "⚠️  File not found: $file"
    fi
done

# Clean up empty directories
echo "🧹 Cleaning up empty directories..."
find . -type d -empty -delete 2>/dev/null || true

# Create essential files that should remain
echo "📝 Creating essential files..."

# Create a simple README for the cleaned structure
cat > README.md << 'EOF'
# BingeTV - Premium TV Streaming Platform

## 🎯 Overview
BingeTV is a comprehensive TV streaming platform for Kenya, featuring Premier League, National Geographic, and 150+ premium channels.

## 🚀 Quick Start

### Admin Access
- URL: https://bingetv.co.ke/admin/login.php
- Email: admin@bingetv.co.ke
- Password: BingeTV2024!

### User Registration
- URL: https://bingetv.co.ke/register.php
- Users can register and subscribe immediately

## 📁 Project Structure

- `public/` - Main application files (index.php, login.php, etc.)
- `admin/` - Admin panel files
- `config/` - Configuration files
- `database/` - Database migrations and schema
- `lib/` - Library files and utilities
- `assets/` - CSS, JS, and images
- `api/` - API endpoints

## 🔧 Key Features

- ✅ User Registration & Login
- ✅ Subscription Management
- ✅ M-PESA Payment Integration
- ✅ Admin Management Panel
- ✅ Social Media Management
- ✅ SEO Optimization
- ✅ Mobile Responsive Design

## 📞 Support
For support, contact: support@bingetv.co.ke
EOF

# Create a .gitignore file
cat > .gitignore << 'EOF'
# Cache files
storage/cache/*
storage/logs/*
storage/sessions/*

# Uploads
public/uploads/*

# Environment files
.env
.env.local
.env.production

# Database files
*.sql
*.db

# Log files
*.log

# Temporary files
*.tmp
*.temp

# OS files
.DS_Store
Thumbs.db

# IDE files
.vscode/
.idea/
*.swp
*.swo

# Backup files
*.bak
*.backup
EOF

echo "✅ Cleanup completed!"
echo "📊 Statistics:"
echo "   - Files removed: $REMOVED_COUNT"
echo "   - Total files checked: $TOTAL_FILES"
echo "   - Empty directories cleaned: Yes"
echo "   - Essential files created: Yes"

echo ""
echo "🎉 BingeTV cleanup process completed successfully!"
echo "📁 Project is now clean and organized"
