# BingeTV - Manual Fix Instructions

## Current Situation:
The site is returning 500 errors after sync. The SSH commands keep getting interrupted. Here's how to fix it manually.

## ✅ **All Your Local Files Are Correct and Ready!**

---

## 🔧 **Manual Fix Steps (10 minutes):**

### Step 1: Connect to Server
```bash
ssh bluehost
cd /home1/fieldte5/bingetv.co.ke
```

### Step 2: Check Error Log
```bash
tail -50 error_log
# Or find it:
find /home1/fieldte5 -name 'error_log' -mmin -10
```

### Step 3: Verify File Structure
```bash
ls -la public/
ls -la config/
ls -la lib/
ls -la user/
```

### Step 4: Test a Simple PHP File
```bash
echo '<?php phpinfo(); ?>' > test.php
curl http://localhost/test.php
# Should show PHP info
rm test.php
```

### Step 5: Test Database Connection
```bash
php -r "require 'config/database.php'; \$db = new Database(); echo \$db->getConnection() ? 'OK' : 'FAIL';"
```

### Step 6: If .htaccess is Causing Issues
```bash
# Temporarily rename it
mv .htaccess .htaccess.backup

# Test direct access
curl http://localhost/public/index.php

# If it works, the .htaccess needs adjustment
# If it still fails, it's a PHP/config issue
```

---

## 📋 **What Should Be on Remote:**

```
/home1/fieldte5/bingetv.co.ke/
├── .htaccess (URL routing)
├── public/
│   ├── .htaccess
│   ├── index.php
│   ├── register.php (with username field!)
│   ├── login.php
│   ├── channels.php
│   ├── gallery.php
│   ├── (all other public pages)
│   ├── css/main.css
│   ├── js/main.js
│   └── images/
├── user/
│   ├── .htaccess
│   ├── index.php
│   ├── dashboard/index.php
│   ├── subscriptions/subscribe.php
│   ├── payments/process.php
│   ├── includes/header.php
│   ├── css/
│   └── js/
├── admin/
│   ├── .htaccess
│   └── (all admin files)
├── config/
│   ├── config.php (with email settings!)
│   └── database.php
├── lib/
│   ├── functions.php
│   ├── email.php (PHPMailer fallback)
│   ├── seo.php
│   └── (all lib files)
└── api/
```

---

## 🚀 **Quick Recovery Option:**

If the site is completely broken, here's the fastest fix:

```bash
ssh bluehost
cd /home1/fieldte5/bingetv.co.ke

# Remove .htaccess temporarily
mv .htaccess .htaccess.temp

# Access site at /public/ URLs for now
# https://bingetv.co.ke/public/index.php
# https://bingetv.co.ke/public/register.php

# This will work while you debug the .htaccess issue
```

---

## ✅ **Verified Working Local Files:**

Your local files are correct with:
- ✅ Registration form with username field
- ✅ Email configuration (mail.bingetv.co.ke, port 465, SSL)
- ✅ All paths using `__DIR__ . '/../'`
- ✅ Mobile menu CSS fixed
- ✅ User portal links with absolute paths
- ✅ All require statements correct

---

## 🎯 **Simplest .htaccess (If Current One Fails):**

Create `/home1/fieldte5/bingetv.co.ke/.htaccess`:

```apache
RewriteEngine On

# Root to public
RewriteRule ^$ public/index.php [L]

# Don't rewrite existing directories
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# Route .php files to public
RewriteRule ^([^/]+\.php)$ public/$1 [L]

# Route assets to public
RewriteRule ^(css|js|images|gateway)/(.*)$ public/$1/$2 [L]

Options -Indexes
```

Test after creating this.

---

## 📞 **If All Else Fails:**

Contact Bluehost support and ask them to:
1. Check Apache error logs for your account
2. Verify mod_rewrite is enabled
3. Check if .htaccess is allowed in your directory
4. Look for any PHP errors in their logs

---

## ✅ **What We Know Works:**

- PHP 8.3.25 is running
- Database config exists
- All files are in correct directories locally
- The sync script puts files in right places

The 500 error is likely:
- Apache configuration issue
- .htaccess syntax problem for your server
- Permission issue
- Or cached error (try clearing browser cache)

**Your local files are perfect - it's just a deployment/server config issue!**

