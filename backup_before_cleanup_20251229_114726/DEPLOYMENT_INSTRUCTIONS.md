# Deployment Instructions - Manual M-Pesa & Link Fixes

## Overview
This deployment fixes:
1. Broken subscription links in user dashboard
2. Adds manual M-Pesa confirmation system
3. Updates payment process page with manual submission option

## Files to Upload via cPanel File Manager

### 1. User Portal Files
Upload to `/home1/fieldte5/bingetv.co.ke/user/`:

- `user/dashboard/index.php` - Fixed subscription links
- `user/channels.php` - Fixed subscription redirects
- `user/payments/process.php` - Added manual M-Pesa link (already has it)
- `user/payments/submit-mpesa.php` - NEW FILE for manual M-Pesa submission

### 2. Admin Files
Upload to `/home1/fieldte5/bingetv.co.ke/admin/`:

- `admin/manual-payments.php` - NEW FILE for admin review interface
- `admin/includes/header.php` - Added navigation link to manual payments

### 3. Database Migration
Upload to `/home1/fieldte5/bingetv.co.ke/`:

- `database/migrations/010_manual_mpesa_confirmations.sql` - SQL migration file
- `run_migration_010.php` - PHP script to run the migration

### 4. Documentation (Optional)
Upload to `/home1/fieldte5/bingetv.co.ke/`:

- `MANUAL_MPESA_SYSTEM.md` - Documentation for the manual M-Pesa system

## Deployment Steps

### Step 1: Upload Files via cPanel File Manager

1. Log in to cPanel
2. Open File Manager
3. Navigate to `/home1/fieldte5/bingetv.co.ke/`
4. Upload the files listed above to their respective directories

### Step 2: Set Permissions

After uploading, set these permissions:
- Directories: 755
- Files: 644

### Step 3: Run Database Migration

1. Navigate to: `https://bingetv.co.ke/run_migration_010.php`
2. Verify the output shows "✅ Migration 010 executed successfully!"
3. After successful migration, DELETE the file `run_migration_010.php` for security

### Step 4: Verify All Pages

Test these URLs:
- `https://bingetv.co.ke/user/gallery.php` - Should redirect to login if not logged in
- `https://bingetv.co.ke/user/subscriptions/subscribe.php` - Subscription page
- `https://bingetv.co.ke/user/payments/process.php` - Payment process page
- `https://bingetv.co.ke/user/payments/submit-mpesa.php` - Manual M-Pesa submission (login required)
- `https://bingetv.co.ke/user/support.php` - Support page
- `https://bingetv.co.ke/help.php` - Public help page
- `https://bingetv.co.ke/admin/manual-payments.php` - Admin manual payment review (admin login required)

### Step 5: Test User Flow

1. **Login as a user** using credentials:
   - Email/Username: kemboi.norman1@gmail.com
   - Password: Normas@4340

2. **Test Dashboard Links**:
   - Click "Subscribe Now" buttons - should go to `/user/subscriptions/subscribe.php`
   - Select a package
   - On payment page, verify both options appear:
     - "Pay with M-PESA (Automatic)"
     - "Already Paid? Submit M-PESA Confirmation"

3. **Test Manual M-Pesa Submission**:
   - Click "Already Paid? Submit M-PESA Confirmation"
   - Should land on `/user/payments/submit-mpesa.php`
   - Fill out the form with a test M-Pesa message
   - Submit for admin review

4. **Test Admin Review** (requires admin login):
   - Go to `/admin/manual-payments.php`
   - Verify submitted manual payments appear
   - Test approve/reject functionality

## Files Changed Summary

### Modified Files:
1. `/user/dashboard/index.php` - Fixed relative paths for subscription links
2. `/user/channels.php` - Fixed subscription redirect paths
3. `/admin/includes/header.php` - Added "Manual M-Pesa" navigation link

### New Files:
1. `/user/payments/submit-mpesa.php` - User interface to submit M-Pesa confirmation
2. `/admin/manual-payments.php` - Admin interface to review manual submissions
3. `/database/migrations/010_manual_mpesa_confirmations.sql` - Database schema
4. `/run_migration_010.php` - Migration runner script
5. `/MANUAL_MPESA_SYSTEM.md` - Documentation

## Rollback Plan

If issues occur:
1. Keep backups of original files before uploading
2. To rollback migration, run this SQL:
   ```sql
   DROP TABLE IF EXISTS manual_payment_submissions;
   ALTER TABLE payments DROP COLUMN IF EXISTS is_manual_confirmation;
   ALTER TABLE payments DROP COLUMN IF EXISTS manual_submission_id;
   ```

## Post-Deployment Checklist

- [ ] All files uploaded successfully
- [ ] File permissions set correctly
- [ ] Migration 010 executed successfully
- [ ] `run_migration_010.php` deleted after migration
- [ ] Dashboard subscription links working (no 404 errors)
- [ ] Manual M-Pesa submission page accessible
- [ ] Admin manual payment review page accessible
- [ ] All navigation links working
- [ ] User can submit manual M-Pesa confirmation
- [ ] Admin can review and approve submissions

## Support

If you encounter issues:
1. Check PHP error logs in cPanel
2. Verify database migration completed by checking if `manual_payment_submissions` table exists
3. Ensure all file paths match the directory structure
4. Verify .htaccess is not blocking access to new pages

## Notes

- The manual M-Pesa system is a fallback for when automatic STK push fails
- Admin review is required to prevent fraud
- Users can submit M-Pesa confirmation messages which admin can approve/reject
- Upon approval, the subscription is automatically activated

