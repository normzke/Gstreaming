#!/bin/bash

# BingeTV File Structure Reorganization Script
# This script organizes all files into a proper structure

echo "🗂️  Starting BingeTV file structure reorganization..."

# Create organized directory structure
echo "📁 Creating organized directory structure..."

# Core application directories
mkdir -p public/{css,js,images,uploads}
mkdir -p app/{controllers,models,views,helpers}
mkdir -p config/{environments,initializers}
mkdir -p database/{migrations,seeds,schema}
mkdir -p lib/{services,utilities,validators}
mkdir -p storage/{cache,logs,sessions}
mkdir -p tests/{unit,integration,features}
mkdir -p docs/{api,deployment,user-guides}
mkdir -p scripts/{deployment,maintenance,testing}

echo "✅ Directory structure created"

# Move core application files
echo "📦 Moving core application files..."

# Public assets
mv assets/css/* public/css/ 2>/dev/null || true
mv assets/js/* public/js/ 2>/dev/null || true
mv assets/images/* public/images/ 2>/dev/null || true

# Application files
mv index.php public/
mv login.php public/
mv register.php public/
mv logout.php public/
mv dashboard.php public/
mv channels.php public/
mv gallery.php public/
mv subscribe.php public/
mv payment.php public/

# Configuration files
mv config/* config/ 2>/dev/null || true

# Database files
mv database/migrations/* database/migrations/ 2>/dev/null || true
mv database/seeds/* database/seeds/ 2>/dev/null || true
mv database/schema.sql database/schema/ 2>/dev/null || true

# Library files
mv includes/* lib/ 2>/dev/null || true

# API files
mv api/* app/controllers/ 2>/dev/null || true

# Admin files (already organized)
# admin/ stays as is

# Storage files
mkdir -p storage/cache
mkdir -p storage/logs

# Documentation files
mv *.md docs/ 2>/dev/null || true

# Scripts
mv *.sh scripts/ 2>/dev/null || true
mv *.php scripts/ 2>/dev/null || true

# Test files
mv tests/* tests/ 2>/dev/null || true

# Clean up empty directories
find . -type d -empty -delete 2>/dev/null || true

echo "✅ File reorganization completed"

# Create new organized structure summary
cat > ORGANIZED_STRUCTURE.md << 'EOF'
# BingeTV Organized File Structure

## 📁 Directory Structure

```
BingeTV/
├── public/                     # Public web files
│   ├── css/                   # Stylesheets
│   ├── js/                    # JavaScript files
│   ├── images/                # Images and media
│   ├── uploads/               # User uploads
│   ├── index.php              # Main homepage
│   ├── login.php              # User login
│   ├── register.php           # User registration
│   ├── dashboard.php          # User dashboard
│   ├── channels.php           # Channels page
│   ├── gallery.php            # Gallery page
│   ├── subscribe.php          # Subscription page
│   └── payment.php            # Payment page
│
├── app/                       # Application logic
│   ├── controllers/           # API controllers
│   │   ├── auth/             # Authentication APIs
│   │   ├── mpesa/            # M-PESA APIs
│   │   ├── payment/          # Payment APIs
│   │   └── subscription/     # Subscription APIs
│   ├── models/               # Data models
│   ├── views/                # View templates
│   └── helpers/              # Helper functions
│
├── admin/                     # Admin panel
│   ├── api/                  # Admin APIs
│   ├── index.php             # Admin dashboard
│   ├── login.php             # Admin login
│   ├── users.php             # User management
│   ├── packages.php          # Package management
│   ├── payments.php          # Payment management
│   ├── subscriptions.php     # Subscription management
│   ├── channels.php          # Channel management
│   ├── gallery.php           # Gallery management
│   ├── social-media.php      # Social media management
│   └── mpesa-config.php      # M-PESA configuration
│
├── config/                    # Configuration files
│   ├── config.php            # Main configuration
│   ├── database.php          # Database configuration
│   ├── environments/         # Environment configs
│   └── initializers/         # Initialization scripts
│
├── database/                  # Database files
│   ├── migrations/           # Database migrations
│   ├── seeds/                # Database seeds
│   └── schema/               # Schema files
│
├── lib/                       # Library files
│   ├── services/             # Service classes
│   ├── utilities/            # Utility functions
│   ├── validators/           # Validation classes
│   ├── functions.php         # Core functions
│   ├── cache.php             # Caching system
│   ├── seo.php               # SEO utilities
│   ├── payment-processor.php # Payment processing
│   └── performance.php       # Performance monitoring
│
├── storage/                   # Storage files
│   ├── cache/                # Cache files
│   ├── logs/                 # Log files
│   └── sessions/             # Session files
│
├── tests/                     # Test files
│   ├── unit/                 # Unit tests
│   ├── integration/          # Integration tests
│   └── features/             # Feature tests
│
├── docs/                      # Documentation
│   ├── api/                  # API documentation
│   ├── deployment/           # Deployment guides
│   └── user-guides/          # User guides
│
├── scripts/                   # Scripts
│   ├── deployment/           # Deployment scripts
│   ├── maintenance/          # Maintenance scripts
│   └── testing/              # Testing scripts
│
├── .htaccess                  # Apache configuration
├── robots.txt                 # SEO robots file
├── sitemap.php               # Dynamic sitemap
└── sitemap-images.php        # Image sitemap
```

## 🎯 Benefits of This Structure

1. **Clear Separation**: Public files, admin files, and application logic are clearly separated
2. **Scalability**: Easy to add new features and maintain code
3. **Security**: Sensitive files are not in public directory
4. **Organization**: Related files are grouped together
5. **Maintenance**: Easy to find and update specific functionality
6. **Professional**: Follows industry best practices

## 🔧 Usage

- **Public Files**: All user-accessible files are in `public/`
- **Admin Files**: All admin functionality is in `admin/`
- **Application Logic**: Business logic is in `app/` and `lib/`
- **Configuration**: All config files are in `config/`
- **Database**: All database-related files are in `database/`
- **Documentation**: All docs are in `docs/`
- **Scripts**: All utility scripts are in `scripts/`
EOF

echo "📋 Structure documentation created: ORGANIZED_STRUCTURE.md"
echo "🎉 File reorganization completed successfully!"
