# CRITICAL: Add Username Column to Database

## Issue:
Registration fails with: "Registration failed. Please try again."

**Root Cause:** The `username` column doesn't exist in the users table.

---

## ✅ **SOLUTION - Run This SQL:**

### Via cPanel phpPgAdmin (Recommended - 2 minutes):

1. **Login to Bluehost cPanel**
2. **Find PostgreSQL or phpPgAdmin**
3. **Select database:** `fieldte5_bingetv`
4. **Click SQL tab**
5. **Copy and paste this SQL:**

```sql
-- Add username column
ALTER TABLE users ADD COLUMN IF NOT EXISTS username VARCHAR(50);

-- Add unique constraint
ALTER TABLE users ADD CONSTRAINT users_username_key UNIQUE (username);

-- Update existing users (use email prefix as username)
UPDATE users 
SET username = LOWER(REGEXP_REPLACE(SPLIT_PART(email, '@', 1), '[^a-zA-Z0-9]', '', 'g'))
WHERE username IS NULL;

-- Make username required
ALTER TABLE users ALTER COLUMN username SET NOT NULL;

-- Add index for performance
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);

-- Verify
SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'users' AND column_name = 'username';
```

6. **Click Execute/Run**
7. **Should see:** `username | character varying`

---

## ✅ **Alternative: Via Command Line**

```bash
ssh bluehost
psql -h /var/run/postgresql -U fieldte5_bingetv1 -d fieldte5_bingetv

# Then paste the SQL above
```

---

## ✅ **After Running SQL:**

1. **Test registration:** https://bingetv.co.ke/register.php
2. **Should work perfectly!**
3. **Clean up:** Delete `run_migration_009.php` from server for security

---

## 📋 **Checking for Other Missing Tables:**

While you're in the database, verify these tables exist:

### Required Tables:
- ✅ `users` (should have username column after migration)
- ✅ `packages`
- ✅ `channels`
- ✅ `user_subscriptions`
- ✅ `payments`
- ✅ `gallery_items`
- ✅ `package_channels`
- ✅ `user_streaming_access`
- ✅ `remember_tokens` (for login persistence)

### Check Missing Tables:
```sql
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'public' 
ORDER BY table_name;
```

If any tables are missing, the migration files are in:
- `/Users/la/Downloads/GStreaming/database/migrations/`

---

## 🎯 **After Username Column is Added:**

**Registration will work with:**
- Username field ✅
- Email verification ✅
- All validations ✅
- Pricing model ✅
- Everything ready! ✅

---

**Run the SQL above and registration will work immediately!** 🚀

