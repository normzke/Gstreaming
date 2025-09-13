# GStreaming Installation Guide

## Overview
GStreaming is a comprehensive TV streaming platform for Kenyan subscribers, built with PHP and PostgreSQL for cPanel hosting compatibility.

## System Requirements

### Server Requirements
- **PHP**: 8.0 or higher
- **PostgreSQL**: 12.0 or higher
- **Web Server**: Apache or Nginx
- **Hosting**: cPanel compatible hosting
- **SSL Certificate**: Required for M-PESA integration

### PHP Extensions Required
- PDO PostgreSQL
- OpenSSL
- cURL
- JSON
- mbstring
- fileinfo

## Installation Steps

### 1. Upload Files
1. Upload all files to your cPanel hosting account
2. Extract files to your domain's public_html directory
3. Ensure proper file permissions (755 for directories, 644 for files)

### 2. Database Setup
1. Create a PostgreSQL database in cPanel
2. Create a database user with full privileges
3. Import the database schema:
   ```bash
   psql -h localhost -U your_username -d your_database -f database/schema.sql
   ```

### 3. Configuration
1. Copy `config/config.php` and update the following:
   ```php
   // Database Configuration
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_database_name');
   define('DB_USER', 'your_database_user');
   define('DB_PASS', 'your_database_password');
   
   // M-PESA Configuration
   define('MPESA_CONSUMER_KEY', 'your_mpesa_consumer_key');
   define('MPESA_CONSUMER_SECRET', 'your_mpesa_consumer_secret');
   define('MPESA_SHORTCODE', 'your_shortcode');
   define('MPESA_PASSKEY', 'your_passkey');
   define('MPESA_TILL_NUMBER', 'your_till_number');
   define('MPESA_PAYBILL_NUMBER', 'your_paybill_number');
   
   // Site Configuration
   define('SITE_URL', 'https://yourdomain.com');
   define('SITE_EMAIL', 'support@yourdomain.com');
   ```

### 4. M-PESA Integration Setup
1. Register for M-PESA API access at [Safaricom Developer Portal](https://developer.safaricom.co.ke/)
2. Create an app and get your consumer key and secret
3. Set up your Till Number and Paybill Number
4. Configure callback URLs:
   - STK Push Callback: `https://yourdomain.com/api/mpesa/callback.php?type=stkpush`
   - C2B Callback: `https://yourdomain.com/api/mpesa/callback.php?type=c2b`

### 5. Email Configuration
1. Set up SMTP settings in `config/config.php`
2. Configure email templates in the admin panel
3. Test email functionality

### 6. File Permissions
Set proper permissions for uploads directory:
```bash
chmod 755 uploads/
chmod 755 uploads/gallery/
chmod 755 uploads/users/
```

### 7. SSL Certificate
Ensure your domain has a valid SSL certificate for secure M-PESA transactions.

## Admin Setup

### Default Admin Account
- **Username**: admin
- **Password**: admin123
- **URL**: `https://yourdomain.com/admin/`

**Important**: Change the default password immediately after first login.

### Admin Features
- User management
- Package management
- Payment monitoring
- Channel management
- Gallery management
- Email template management
- System settings

## Package Configuration

### Default Packages
The system comes with 4 default packages:
1. **Basic Plan**: KES 500/month - 50 channels, SD quality
2. **Premium Plan**: KES 1,200/month - 200 channels, HD quality
3. **Family Plan**: KES 2,000/month - 500 channels, HD quality, 5 devices
4. **VIP Plan**: KES 3,500/month - 1000 channels, 4K quality, 10 devices

### Customizing Packages
1. Login to admin panel
2. Go to Packages section
3. Edit or create new packages
4. Configure features and pricing

## Gateway App Setup

### Android App
1. Download the APK file from `gateway/downloads/`
2. Upload to your server
3. Configure download links in the system

### iOS App
1. Upload the IPA file (requires Apple Developer account)
2. Configure distribution settings
3. Update download links

## Security Considerations

### 1. Database Security
- Use strong database passwords
- Limit database user privileges
- Enable SSL for database connections

### 2. File Security
- Set proper file permissions
- Disable directory browsing
- Use .htaccess for additional security

### 3. Application Security
- Keep PHP and PostgreSQL updated
- Use strong admin passwords
- Enable CSRF protection (already implemented)
- Regular security audits

## Testing

### 1. Test User Registration
1. Visit your website
2. Register a new user account
3. Verify email functionality

### 2. Test M-PESA Integration
1. Use M-PESA sandbox for testing
2. Test STK Push functionality
3. Verify callback handling

### 3. Test Admin Panel
1. Login with admin credentials
2. Test all admin functions
3. Verify user management

## Maintenance

### Regular Tasks
1. **Database Backups**: Set up automated daily backups
2. **Log Monitoring**: Check error logs regularly
3. **Security Updates**: Keep all components updated
4. **Performance Monitoring**: Monitor server resources

### Backup Script
Create a backup script for your database:
```bash
#!/bin/bash
pg_dump -h localhost -U username -d database_name > backup_$(date +%Y%m%d).sql
```

## Troubleshooting

### Common Issues

#### 1. Database Connection Error
- Check database credentials
- Verify PostgreSQL is running
- Check firewall settings

#### 2. M-PESA Integration Issues
- Verify API credentials
- Check callback URL accessibility
- Review M-PESA logs

#### 3. Email Not Working
- Check SMTP settings
- Verify email server configuration
- Test with simple PHP mail function

#### 4. File Upload Issues
- Check file permissions
- Verify upload directory exists
- Check PHP upload limits

### Error Logs
Check these log files:
- PHP error log
- Apache/Nginx error log
- Application logs in `logs/` directory

## Support

### Documentation
- API documentation available in `/docs/`
- User manual in `/help/`
- Admin guide in `/admin/docs/`

### Contact
- Email: support@gstreaming.com
- Phone: +254 700 000 000
- Website: https://gstreaming.com

## License
This software is proprietary. All rights reserved.

---

**Note**: This installation guide assumes basic knowledge of web hosting and server administration. For advanced configurations, consult with your hosting provider or a system administrator.
