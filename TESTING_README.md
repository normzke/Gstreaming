# GStreaming Testing & Deployment Guide

This document provides comprehensive instructions for testing and deploying the GStreaming platform.

## 🧪 Testing Overview

The GStreaming platform includes a comprehensive testing suite that validates all system components, APIs, and functionality before deployment.

## 📋 Prerequisites

### System Requirements
- **PHP**: Version 8.0 or higher
- **PostgreSQL**: Version 12 or higher
- **Web Server**: Apache/Nginx with mod_rewrite enabled
- **Extensions**: PDO, PDO_PGSQL, cURL, JSON, mbstring, OpenSSL, Session

### Required PHP Extensions
```bash
# Check if required extensions are installed
php -m | grep -E "(pdo|pdo_pgsql|curl|json|mbstring|openssl|session)"
```

## 🚀 Quick Start Testing

### 1. Run All Tests (Recommended)
```bash
# Execute the master test runner
php run-tests.php
```

This will run all test suites in sequence:
- Database migrations
- Core system tests
- API endpoint tests
- Deployment readiness tests

### 2. Individual Test Suites

#### Database Migrations
```bash
# Run database migrations
php database/run-migrations.php run

# Check migration status
php database/run-migrations.php status

# Rollback a migration (if needed)
php database/run-migrations.php rollback <migration_name>
```

#### Core System Tests
```bash
# Test database, user management, subscriptions, payments
php tests/test-suite.php
```

#### API Tests
```bash
# Test all API endpoints
php tests/api-tests.php http://localhost:4000/GStreaming
```

#### Deployment Tests
```bash
# Test system readiness for production
php tests/deployment-test.php
```

## 📊 Test Results Interpretation

### Status Levels
- **✅ PASS**: Test completed successfully
- **⚠️ WARN**: Test completed with warnings (review recommended)
- **❌ FAIL**: Test failed (fix required)
- **🚨 CRITICAL**: Critical issue found (deployment blocked)

### Overall System Status
- **READY**: All tests passed, system ready for deployment
- **WARNING**: Minor issues found, review before deployment
- **NOT_READY**: Critical issues found, do not deploy

## 🔧 Test Configuration

### Database Configuration
Ensure your `config/database.php` is properly configured:
```php
// Example configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gstreaming');
define('DB_USER', 'your_username');
define('DB_PASSWORD', 'your_password');
define('DB_PORT', '5432');
```

### M-PESA Configuration
For payment testing, configure M-PESA credentials in the admin panel:
- Access: `http://localhost:4000/GStreaming/admin/mpesa-config.php`
- Login: admin / admin123
- Configure Consumer Key, Secret, Shortcode, and Passkey

### Test Environment Setup
```bash
# Start local development server
php -S localhost:4000 -t .

# Or use Apache/Nginx with proper configuration
```

## 📝 Test Coverage

### Core System Tests
- ✅ Database connection and schema validation
- ✅ User registration and authentication
- ✅ Package management
- ✅ Subscription creation and management
- ✅ M-PESA integration
- ✅ Payment processing
- ✅ Admin analytics
- ✅ Channel management
- ✅ Email notifications
- ✅ Security features

### API Tests
- ✅ Public endpoints accessibility
- ✅ User registration API
- ✅ User login API
- ✅ Package listing API
- ✅ Payment initiation API
- ✅ Payment status API
- ✅ Subscription creation API
- ✅ Admin analytics API
- ✅ Error handling

### Deployment Tests
- ✅ System requirements validation
- ✅ Database integrity checks
- ✅ Configuration validation
- ✅ Security features
- ✅ Performance tests
- ✅ File permissions
- ✅ API integrity
- ✅ Payment system
- ✅ Email system
- ✅ Admin panel
- ✅ User experience

## 🛠️ Troubleshooting

### Common Issues

#### Database Connection Failed
```
❌ Database connection failed: Connection refused
```
**Solution**: 
1. Ensure PostgreSQL is running
2. Check database credentials in `config/database.php`
3. Verify database exists and user has proper permissions

#### M-PESA Configuration Missing
```
⚠️ M-PESA configuration not set up - payments will use simulation mode
```
**Solution**:
1. Access admin panel: `http://localhost:4000/GStreaming/admin/`
2. Go to M-PESA Config
3. Enter your M-PESA API credentials

#### API Tests Failed
```
❌ API tests failed: cURL error: Connection refused
```
**Solution**:
1. Ensure web server is running
2. Check if port 4000 is accessible
3. Verify `.htaccess` file is present

#### File Permissions Issues
```
⚠️ Directory 'uploads/' is not writable
```
**Solution**:
```bash
# Fix directory permissions
chmod 755 uploads/
chmod 755 assets/images/
chmod 755 admin/uploads/
```

### Test Data Management

#### Reset Test Data
```bash
# Clean up test data after testing
# The test suite automatically cleans up test users and related data
```

#### Add More Test Data
```bash
# Run test data migration
php database/run-migrations.php run
# This will add comprehensive test data including users, packages, channels
```

## 📈 Performance Testing

### Database Performance
- Query execution time should be < 0.1 seconds
- Index validation ensures optimal performance
- Foreign key constraints maintain data integrity

### File System Performance
- File read/write operations should be < 0.01 seconds
- Large file handling capability tested
- Upload directory permissions validated

### API Performance
- API response times tested
- Concurrent request handling validated
- Error response times measured

## 🔒 Security Testing

### Password Security
- Password hashing validation
- Password verification testing
- Secure password storage confirmed

### Input Sanitization
- XSS protection validation
- SQL injection prevention testing
- Input validation and sanitization

### Session Security
- Session cookie security flags
- Session hijacking prevention
- Secure session management

## 📋 Deployment Checklist

Before deploying to production, ensure:

### ✅ System Requirements
- [ ] PHP 8.0+ installed
- [ ] PostgreSQL 12+ installed
- [ ] Required PHP extensions available
- [ ] Web server configured with mod_rewrite

### ✅ Database
- [ ] Database created and configured
- [ ] Migrations executed successfully
- [ ] Test data seeded (optional)
- [ ] Database indexes created
- [ ] Backup strategy in place

### ✅ Configuration
- [ ] Database credentials configured
- [ ] M-PESA API credentials set
- [ ] Email configuration tested
- [ ] Site URL configured for production
- [ ] SSL certificate installed (recommended)

### ✅ Security
- [ ] File permissions set correctly
- [ ] Admin passwords changed from defaults
- [ ] HTTPS enabled (recommended)
- [ ] Input validation working
- [ ] SQL injection protection active

### ✅ Testing
- [ ] All tests pass
- [ ] No critical issues found
- [ ] Performance acceptable
- [ ] API endpoints working
- [ ] Payment system functional

## 🚀 Production Deployment

### 1. Pre-Deployment
```bash
# Run final deployment test
php tests/deployment-test.php

# Ensure all tests pass
php run-tests.php
```

### 2. Database Setup
```bash
# Create production database
createdb gstreaming_production

# Update database configuration
# Edit config/database.php with production credentials

# Run migrations
php database/run-migrations.php run
```

### 3. File Upload
```bash
# Upload files to production server
# Ensure proper file permissions are set
chmod -R 755 .
chmod -R 777 uploads/
chmod -R 777 admin/uploads/
```

### 4. Configuration
```bash
# Update production configuration
# Edit config/config.php
# Set SITE_URL to production domain
# Configure M-PESA for production environment
```

### 5. Post-Deployment Testing
```bash
# Test production deployment
php tests/api-tests.php https://yourdomain.com
php tests/deployment-test.php
```

## 📞 Support

If you encounter issues during testing or deployment:

1. **Check the test output** for specific error messages
2. **Review the deployment report** generated after tests
3. **Verify system requirements** are met
4. **Check configuration files** for proper settings
5. **Ensure database connectivity** and permissions

## 📄 Test Reports

Test reports are automatically generated and saved as:
- `test-report-YYYY-MM-DD-HH-MM-SS.txt` - Comprehensive test results
- `deployment-report-YYYY-MM-DD-HH-MM-SS.txt` - Deployment readiness report

These reports contain detailed information about test results, warnings, and recommendations for deployment.

---

**Remember**: Always run tests before deploying to production to ensure system stability and functionality! 🎉
