-- Add username column to users table
-- Copy and paste EACH command separately in phpPgAdmin

-- Step 1: Add the column
ALTER TABLE users ADD COLUMN username VARCHAR(50);

-- Step 2: Add unique constraint
ALTER TABLE users ADD CONSTRAINT users_username_unique UNIQUE (username);

-- Step 3: Update existing users with username (based on email)
UPDATE users 
SET username = LOWER(REGEXP_REPLACE(SPLIT_PART(email, '@', 1), '[^a-zA-Z0-9]', '', 'g'))
WHERE username IS NULL;

-- Step 4: Make username required
ALTER TABLE users ALTER COLUMN username SET NOT NULL;

-- Step 5: Add index for performance
CREATE INDEX idx_users_username ON users(username);

-- Step 6: Verify (this will show the username column info)
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'users' AND column_name = 'username';
