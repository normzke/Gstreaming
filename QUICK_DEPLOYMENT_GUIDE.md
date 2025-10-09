# Quick Deployment Guide

## 🎯 What Was Fixed

1. **404 Error on Subscription Page** - Fixed broken link from dashboard
2. **Manual M-Pesa System** - Added fallback for when automatic payment fails
3. **All Navigation Links** - Verified and corrected all user portal links

---

## 📦 Upload These Files via cPanel File Manager

### Step 1: Login to cPanel
- URL: Your cPanel URL
- Navigate to File Manager
- Go to: `/home1/fieldte5/bingetv.co.ke/`

### Step 2: Upload Files

Copy these files from local to remote:

```
LOCAL → REMOTE

Root Directory:
/Users/la/Downloads/GStreaming/run_migration_010.php 
→ /home1/fieldte5/bingetv.co.ke/run_migration_010.php

Database:
/Users/la/Downloads/GStreaming/database/migrations/010_manual_mpesa_confirmations.sql
→ /home1/fieldte5/bingetv.co.ke/database/migrations/010_manual_mpesa_confirmations.sql

User Portal:
/Users/la/Downloads/GStreaming/user/dashboard/index.php
→ /home1/fieldte5/bingetv.co.ke/user/dashboard/index.php

/Users/la/Downloads/GStreaming/user/channels.php
→ /home1/fieldte5/bingetv.co.ke/user/channels.php

/Users/la/Downloads/GStreaming/user/payments/submit-mpesa.php
→ /home1/fieldte5/bingetv.co.ke/user/payments/submit-mpesa.php

Admin Portal:
/Users/la/Downloads/GStreaming/admin/manual-payments.php
→ /home1/fieldte5/bingetv.co.ke/admin/manual-payments.php

/Users/la/Downloads/GStreaming/admin/includes/header.php
→ /home1/fieldte5/bingetv.co.ke/admin/includes/header.php
```

---

## 🔧 After Upload

### Step 3: Run Database Migration
1. Visit: `https://bingetv.co.ke/run_migration_010.php`
2. Look for: ✅ **"Migration 010 executed successfully!"**
3. **IMPORTANT:** Delete `run_migration_010.php` after success

### Step 4: Set Permissions (if needed)
In cPanel File Manager:
- Select all uploaded files
- Change Permissions: Files = 644, Directories = 755

---

## ✅ Testing (Login as User)

User credentials:
- Email/Username: `kemboi.norman1@gmail.com`
- Password: `Normas@4340`

### Test These Pages:

1. **Dashboard**
   - Go to: `https://bingetv.co.ke/user/dashboard/`
   - Click "Subscribe Now" → Should go to subscription page (NOT 404)

2. **Subscriptions**
   - URL: `https://bingetv.co.ke/user/subscriptions/subscribe.php`
   - Should load without errors

3. **Gallery**
   - URL: `https://bingetv.co.ke/user/gallery.php`
   - Should load correctly

4. **Channels**
   - URL: `https://bingetv.co.ke/user/channels.php`
   - Should load correctly

5. **Payments**
   - URL: `https://bingetv.co.ke/user/payments/process.php`
   - Should show TWO payment options:
     - "Pay with M-PESA (Automatic)"
     - "Already Paid? Submit M-PESA Confirmation"

6. **Manual M-Pesa**
   - URL: `https://bingetv.co.ke/user/payments/submit-mpesa.php`
   - Should show form to paste M-Pesa message

7. **Support**
   - URL: `https://bingetv.co.ke/user/support.php`
   - Should load correctly

8. **Public Help**
   - URL: `https://bingetv.co.ke/help.php`
   - Should load (no login required)

---

## 🛠️ If Something Goes Wrong

### Migration Failed?
- Check PHP error logs in cPanel
- Verify database credentials in `config/config.php`
- Ensure `manual_payment_submissions` table doesn't already exist

### 404 Errors?
- Verify .htaccess exists in root
- Check file paths match exactly
- Ensure file permissions are correct (644)

### 500 Errors?
- Check PHP error logs
- Verify all `require_once` paths are correct
- Ensure database connection works

---

## 📋 Quick Checklist

- [ ] Logged into cPanel File Manager
- [ ] Uploaded all 7 files to correct directories
- [ ] Visited `https://bingetv.co.ke/run_migration_010.php`
- [ ] Saw "Migration 010 executed successfully!"
- [ ] Deleted `run_migration_010.php`
- [ ] Tested dashboard → Subscribe Now button (no 404)
- [ ] Tested Gallery page loads
- [ ] Tested Subscriptions page loads
- [ ] Tested Payments page shows manual M-Pesa option
- [ ] Tested Support page loads
- [ ] Tested Help page loads

---

## 🎉 Success Criteria

✅ All pages load without 404 errors
✅ Dashboard "Subscribe Now" links work correctly
✅ Payment page shows manual M-Pesa option
✅ Manual M-Pesa submission form is accessible
✅ Admin can access manual payment review page

---

## 📞 Need Help?

If you encounter errors:
1. Check PHP error logs in cPanel
2. Verify all files uploaded to correct paths
3. Ensure migration completed successfully
4. Check file permissions (644 for files, 755 for directories)

---

**Total Files to Upload: 7**
**Estimated Time: 10-15 minutes**
**Difficulty: Easy (just drag & drop in cPanel)**

Good luck! 🚀

