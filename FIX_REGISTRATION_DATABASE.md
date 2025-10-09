# Fix Registration - Add Username Column

## Problem Found:
**The `username` column doesn't exist in the users table!**

Error: `SQLSTATE[42703]: Undefined column: 7 ERROR: column "username" does not exist`

---

## Solution: Add Username Column to Database

### Option 1: Via cPanel (Easiest)

1. **Login to Bluehost cPanel**
2. **Go to phpPgAdmin** (or PostgreSQL section)
3. **Select database:** `fieldte5_bingetv`
4. **Click on SQL tab**
5. **Run this SQL:**
   ```sql
   ALTER TABLE users ADD COLUMN username VARCHAR(50) UNIQUE;
   ```
6. **Click Execute**
7. **Verify:** Check users table structure - username column should appear

### Option 2: Via SSH Command Line

```bash
ssh bluehost
cd /home1/fieldte5/bingetv.co.ke

# Run PostgreSQL command
psql -h /var/run/postgresql -U fieldte5_bingetv1 -d fieldte5_bingetv -c "ALTER TABLE users ADD COLUMN IF NOT EXISTS username VARCHAR(50) UNIQUE;"

# Verify
psql -h /var/run/postgresql -U fieldte5_bingetv1 -d fieldte5_bingetv -c "\d users"
```

### Option 3: Upload and Run Migration Script

1. **I've created:** `add_username_column.sql`
2. **Upload it to server**
3. **Run via phpPgAdmin** or psql command

---

## After Adding Column:

### Test Registration Again:
1. Go to: https://bingetv.co.ke/register.php
2. Fill form with:
   - Username: `testuser`
   - First Name: `Test`
   - Last Name: `User`
   - Email: Your email
   - Phone: `+254700000000`
   - Password: `TestPass123`
3. Submit
4. Should work! ✅

---

## Temporary Workaround (If Can't Add Column Now):

### Make Registration Work Without Username:

Edit `/home1/fieldte5/bingetv.co.ke/public/register.php`:

1. **Comment out username lines:**
   ```php
   // Line 29: // $username = trim($_POST['username'] ?? '');
   // Line 36: // || empty($username)
   // Lines 38-41: Comment out username validation
   // Line 56: Change to: WHERE email = ?
   // Line 58: Change to: [$email]
   // Line 69: Remove username from column list
   // Line 70: Remove username from VALUES
   // Line 72: Remove $username from execute array
   ```

2. **Or simpler:** Use email as username temporarily:
   ```php
   $username = $email; // Use email as username temporarily
   ```

---

## Recommended: Add the Column (Permanent Fix)

The username column is in the migration file but hasn't been run on the live database.

**Run this SQL on your database:**
```sql
ALTER TABLE users ADD COLUMN IF NOT EXISTS username VARCHAR(50) UNIQUE;
```

**After adding the column, registration will work perfectly!**

---

## ✅ Everything Else is Ready:

- ✅ Registration form with username field
- ✅ Pricing model implemented
- ✅ Email configured
- ✅ All pages working
- ✅ Clean URLs active

**Just need to add the username column to the database!**

