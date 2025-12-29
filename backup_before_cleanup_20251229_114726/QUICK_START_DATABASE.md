# BingeTV Database Quick Start Guide

## 🚨 **IMMEDIATE FIX FOR REGISTRATION:**

### Run This Single SQL Command in phpPgAdmin:

```sql
ALTER TABLE users ADD COLUMN username VARCHAR(50) UNIQUE;
```

**That's it!** Registration will work immediately after this.

---

## 📋 **Full Database Setup (If Tables Are Missing):**

### Step 1: Check What Tables Exist

Run in phpPgAdmin SQL tab:
```sql
SELECT table_name FROM information_schema.tables 
WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
ORDER BY table_name;
```

### Step 2: Run Missing Migrations

If tables are missing, run these migration files in order:

**Location on server:** `/home1/fieldte5/bingetv.co.ke/database/migrations/`

1. `001_initial_schema.sql` - Creates all core tables
2. `004_missing_tables.sql` - Additional tables
3. `005_orders_table.sql` - Orders functionality
4. `006_missing_tables.sql` - More tables
5. `007_remember_tokens_table.sql` - Login persistence
6. `008_email_verification_fields.sql` - Email verification
7. `009_add_username_column.sql` - **USERNAME COLUMN** (NEW)

### Step 3: Verify Users Table Has All Fields

```sql
SELECT column_name, data_type, is_nullable
FROM information_schema.columns
WHERE table_name = 'users'
ORDER BY ordinal_position;
```

**Should include:**
- id
- **username** ← Must have this!
- email
- phone
- password_hash
- first_name
- last_name
- is_active
- email_verified
- email_verification_token
- email_verification_expires
- created_at
- updated_at

---

## 🎯 **Minimal Setup (Just to Get Registration Working):**

If you just want registration to work NOW, only run:

```sql
ALTER TABLE users ADD COLUMN username VARCHAR(50) UNIQUE;
```

Everything else can be added later.

---

## ✅ **After Adding Username Column:**

1. Test registration: https://bingetv.co.ke/register.php
2. Fill form with username
3. Should succeed!
4. Receive verification email
5. Click link to verify
6. Login works!

---

## 📝 **Current Status:**

- ✅ All code is correct and deployed
- ✅ Email is configured
- ✅ Pricing model is updated
- ✅ All pages working
- ⚠️ **Just need username column in database**

**Run the SQL and you're done!** 🚀

