# BingeTV Email Configuration Guide

## Current Status: ⚠️ Email Not Configured (Using Placeholders)

### 📧 **What Needs Email Configuration:**

1. **User Registration** - Email verification link
2. **Forgot Password** - Password reset link
3. **Contact/Support Forms** - Customer inquiries
4. **Order Confirmations** - Subscription receipts
5. **Payment Notifications** - M-Pesa confirmations

---

## 🔧 **Configuration Required:**

### Option 1: Gmail SMTP (Recommended for Testing)

**File to Edit:** `/config/config.php`

**Current Settings (Lines 44-51):**
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'support@bingetv.co.ke');
define('SMTP_PASSWORD', 'your_app_password_here'); // ⚠️ CHANGE THIS
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_FROM_EMAIL', 'support@bingetv.co.ke');
define('SMTP_FROM_NAME', 'BingeTV Support');
```

**Steps to Configure Gmail:**

1. **Create/Use Gmail Account:**
   - Use `support@bingetv.co.ke` if it's a Gmail/Google Workspace account
   - Or create a new Gmail account for testing

2. **Generate App Password:**
   - Go to: https://myaccount.google.com/apppasswords
   - Create app password for "Mail"
   - Copy the 16-character password
   - Replace `your_app_password_here` with this password

3. **Update config.php:**
   ```php
   define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx'); // Your app password
   ```

### Option 2: cPanel Email (Recommended for Production)

If you have email accounts via cPanel/Bluehost:

```php
define('SMTP_HOST', 'mail.bingetv.co.ke'); // Your mail server
define('SMTP_PORT', 587); // or 465 for SSL
define('SMTP_USERNAME', 'support@bingetv.co.ke');
define('SMTP_PASSWORD', 'your_cpanel_email_password');
define('SMTP_ENCRYPTION', 'tls'); // or 'ssl'
define('SMTP_FROM_EMAIL', 'support@bingetv.co.ke');
define('SMTP_FROM_NAME', 'BingeTV Support');
```

**To find your cPanel email settings:**
1. Login to cPanel
2. Go to "Email Accounts"
3. Click "Configure Mail Client" for support@bingetv.co.ke
4. Use the SMTP settings shown

### Option 3: Use PHP mail() Function (Fallback)

The system already has a fallback! If SMTP fails, it uses basic PHP `mail()`:

```php
// This is already implemented in lib/email.php
function sendEmailFallback($to, $subject, $message) {
    $headers = 'From: ' . SMTP_FROM_EMAIL . "\r\n" .
               'Reply-To: ' . SMTP_FROM_EMAIL . "\r\n" .
               'Content-type: text/html; charset=UTF-8';
    return mail($to, $subject, $message, $headers);
}
```

**Note:** `mail()` function works automatically on most hosting but may go to spam.

---

## 🔍 **Current Email System Status:**

### ✅ **Already Working:**
- Email functionality implemented in `/lib/email.php`
- Email verification token system ready
- Registration sends verification emails
- Forgot password sends reset links
- Fallback to PHP mail() if SMTP unavailable

### ⚠️ **Needs Configuration:**
- SMTP password (currently placeholder)
- Test email sending
- Verify emails don't go to spam

---

## 🧪 **Testing Email Configuration:**

After configuring SMTP, test with these pages:

1. **Registration:**
   - Go to: https://bingetv.co.ke/register.php
   - Fill form and submit
   - Check email for verification link

2. **Forgot Password:**
   - Go to: https://bingetv.co.ke/forgot-password.php
   - Enter email
   - Check email for reset link

3. **Contact Form:**
   - Go to: https://bingetv.co.ke/support.php
   - Submit inquiry
   - Check if email received

---

## 📝 **Quick Setup Instructions:**

### For Gmail (5 minutes):

1. Open `/config/config.php`
2. Generate Gmail App Password: https://myaccount.google.com/apppasswords
3. Replace line 48:
   ```php
   define('SMTP_PASSWORD', 'your_app_password_here');
   ```
   With:
   ```php
   define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx'); // Your 16-char app password
   ```
4. Save and sync to server:
   ```bash
   rsync -avz config/config.php bluehost:/home1/fieldte5/bingetv.co.ke/config/
   ```
5. Test registration!

### For cPanel Email (10 minutes):

1. Login to Bluehost cPanel
2. Create email: support@bingetv.co.ke
3. Get SMTP settings from "Configure Mail Client"
4. Update all SMTP_* settings in config.php
5. Sync to server
6. Test!

---

## ⚠️ **Security Notes:**

- Never commit SMTP passwords to git
- Use environment variables in production
- Enable 2FA on email account
- Monitor for abuse/spam

---

## ✅ **Current Fallback:**

Even without SMTP configured, emails will attempt to send via PHP `mail()` function. They might work but could go to spam. For production, proper SMTP is strongly recommended!

