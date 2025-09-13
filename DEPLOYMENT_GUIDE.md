# 🚀 GStreaming Deployment Guide

## 📋 Pre-Deployment Checklist

### ✅ System Requirements
- **PHP**: 7.4+ (8.0+ recommended)
- **PostgreSQL**: 12+ 
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Extensions**: PDO, PDO_PGSQL, cURL, JSON, OpenSSL
- **SSL Certificate**: Required for M-PESA production

### ✅ Features Implemented
- ✅ Complete M-PESA API integration
- ✅ Admin panel with M-PESA configuration
- ✅ User dashboard with subscription management
- ✅ Subscription renewal system
- ✅ WhatsApp support integration (+254768704834)
- ✅ Streaming access generation
- ✅ Email notifications
- ✅ Responsive design
- ✅ Security features

## 🛠 Localhost Setup (Port 4000)

### 1. **Start Development Server**
```bash
cd /Users/la/Downloads/GStreaming
php -S localhost:4000
```

### 2. **Access URLs**
- **Homepage**: http://localhost:4000/GStreaming/
- **Admin Panel**: http://localhost:4000/GStreaming/admin/
- **System Test**: http://localhost:4000/GStreaming/test-system.php

### 3. **Default Credentials**
- **Admin Login**: admin / admin123
- **Test Phone**: 254708374149 (M-PESA sandbox)

### 4. **Database Setup**
```sql
-- Create database
CREATE DATABASE gstreaming_db;

-- Import schema
psql -U username -d gstreaming_db -f database/schema.sql
```

## 🔧 M-PESA Configuration

### 1. **Sandbox Setup (Testing)**
1. Visit: https://developer.safaricom.co.ke/
2. Create account and register application
3. Get sandbox credentials:
   - Consumer Key
   - Consumer Secret  
   - Passkey
   - Shortcode: 174379

### 2. **Admin Configuration**
1. Login to admin panel
2. Go to "M-PESA Config"
3. Enter credentials:
   ```
   Consumer Key: [Your Sandbox Consumer Key]
   Consumer Secret: [Your Sandbox Consumer Secret]
   Shortcode: 174379
   Passkey: [Your Sandbox Passkey]
   Callback URL: http://localhost:4000/GStreaming/api/mpesa/callback.php
   Environment: sandbox
   ```
4. Click "Test Connection"

### 3. **Production Setup**
1. Apply for M-PESA API access
2. Get production credentials
3. Update configuration:
   ```
   Environment: production
   Callback URL: https://yourdomain.com/api/mpesa/callback.php
   ```
4. Update SSL certificate

## 📱 WhatsApp Integration

### **Contact Information**
- **Phone**: +254768704834
- **Floating Button**: Available on all pages
- **Context-Aware**: Different messages per page

### **WhatsApp Links**
- Homepage: General support
- Channels: Channel-related help
- Subscription: Payment assistance
- Dashboard: Account support

## 🧪 Testing Checklist

### **Run System Test**
```bash
# Access test page
http://localhost:4000/GStreaming/test-system.php
```

### **Test Scenarios**
1. **User Registration**
   - Register new user
   - Verify email validation
   - Check session creation

2. **Package Selection**
   - Browse packages
   - Select package
   - Verify redirect to subscription

3. **Payment Flow**
   - Enter M-PESA phone
   - Initiate payment
   - Complete on phone
   - Verify confirmation

4. **Streaming Access**
   - Receive credentials
   - Copy to clipboard
   - Test device setup

5. **Dashboard Features**
   - View subscription details
   - Check renewal options
   - Test credential copying

6. **Admin Functions**
   - Login to admin panel
   - Configure M-PESA
   - Manage channels
   - View payments

## 🚀 Production Deployment

### 1. **Server Setup**
```bash
# Install dependencies
sudo apt update
sudo apt install apache2 postgresql php8.1 php8.1-pgsql php8.1-curl

# Enable modules
sudo a2enmod rewrite
sudo a2enmod ssl
sudo systemctl restart apache2
```

### 2. **Database Migration**
```bash
# Create production database
sudo -u postgres createdb gstreaming_prod

# Import schema
psql -U postgres -d gstreaming_prod -f database/schema.sql
```

### 3. **File Upload**
```bash
# Upload files to web directory
sudo cp -r GStreaming/* /var/www/html/
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
```

### 4. **Configuration**
```php
// Update config/config.php
define('BASE_URL', 'https://yourdomain.com/');
define('DB_HOST', 'localhost');
define('DB_NAME', 'gstreaming_prod');
define('DB_USER', 'your_db_user');
define('DB_PASSWORD', 'your_secure_password');
```

### 5. **SSL Setup**
```bash
# Install Let's Encrypt
sudo apt install certbot python3-certbot-apache

# Get certificate
sudo certbot --apache -d yourdomain.com
```

## 🔒 Security Configuration

### **File Permissions**
```bash
# Secure sensitive files
chmod 600 config/config.php
chmod 600 config/database.php
chmod 600 .htaccess

# Protect uploads
chmod 755 uploads/
chmod 644 uploads/*
```

### **Apache Security**
```apache
# Add to .htaccess
<Files "*.php">
    Order Allow,Deny
    Allow from all
</Files>

<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>
```

### **Database Security**
```sql
-- Create limited user
CREATE USER gstreaming_user WITH PASSWORD 'secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO gstreaming_user;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO gstreaming_user;
```

## 📊 Monitoring & Maintenance

### **Log Monitoring**
```bash
# Check PHP errors
tail -f /var/log/apache2/error.log

# Check application logs
tail -f /var/www/html/logs/app.log
```

### **Database Backup**
```bash
# Daily backup script
#!/bin/bash
pg_dump -U postgres gstreaming_prod > /backups/gstreaming_$(date +%Y%m%d).sql
```

### **Performance Monitoring**
- Monitor M-PESA API response times
- Track payment success rates
- Monitor user registration trends
- Check server resource usage

## 🆘 Troubleshooting

### **Common Issues**

#### **Database Connection Failed**
```bash
# Check PostgreSQL status
sudo systemctl status postgresql

# Verify credentials
psql -U username -d gstreaming_db -c "SELECT 1;"
```

#### **M-PESA API Errors**
1. Verify credentials in admin panel
2. Check callback URL accessibility
3. Test with sandbox environment
4. Review API logs

#### **Payment Not Confirming**
1. Check callback.php accessibility
2. Verify database permissions
3. Review payment status API
4. Check M-PESA transaction logs

#### **Streaming Access Issues**
1. Verify subscription status
2. Check user_streaming_access table
3. Test credential generation
4. Review streaming URL format

### **Support Contacts**
- **WhatsApp**: +254768704834
- **Email**: support@gstreaming.com
- **Technical**: Check logs and error messages

## 📈 Post-Deployment

### **Immediate Tasks**
1. ✅ Test complete user journey
2. ✅ Verify M-PESA payments
3. ✅ Check email notifications
4. ✅ Test admin panel functions
5. ✅ Monitor error logs

### **Ongoing Maintenance**
1. **Daily**: Check payment confirmations
2. **Weekly**: Review user registrations
3. **Monthly**: Update channel list
4. **Quarterly**: Security updates

### **Scaling Considerations**
1. **Load Balancing**: For high traffic
2. **CDN**: For static assets
3. **Database Optimization**: Query performance
4. **Caching**: Redis/Memcached
5. **Monitoring**: Application performance

## 🎉 Success Metrics

### **System Ready When:**
- ✅ All tests pass (80%+ success rate)
- ✅ M-PESA integration working
- ✅ User registration functional
- ✅ Payment flow complete
- ✅ Streaming access generated
- ✅ Admin panel operational
- ✅ WhatsApp support active
- ✅ Mobile responsive design
- ✅ Security measures in place

### **Launch Checklist:**
- [ ] Domain configured
- [ ] SSL certificate installed
- [ ] M-PESA production credentials
- [ ] Email notifications working
- [ ] Admin training completed
- [ ] Support procedures documented
- [ ] Backup systems in place
- [ ] Monitoring tools configured

---

## 🚀 Ready to Launch!

Your GStreaming platform is now fully configured with:
- Complete M-PESA integration
- User dashboard with renewals
- Admin panel management
- WhatsApp support
- Streaming access system
- Security measures
- Mobile responsiveness

**Access your platform at: http://localhost:4000/GStreaming/**

For production deployment, follow the steps above and ensure all tests pass before going live! 🎉
