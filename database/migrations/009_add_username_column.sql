-- Migration 009: Add username column to users table
-- Date: 2025-10-08
-- Purpose: Add unique username field for user authentication

-- Add username column if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS username VARCHAR(50);

-- Add unique constraint on username
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint 
        WHERE conname = 'users_username_key'
    ) THEN
        ALTER TABLE users ADD CONSTRAINT users_username_key UNIQUE (username);
    END IF;
END $$;

-- Update existing users to have username (use email prefix as username)
UPDATE users 
SET username = LOWER(REGEXP_REPLACE(SPLIT_PART(email, '@', 1), '[^a-zA-Z0-9]', '', 'g'))
WHERE username IS NULL;

-- Make username NOT NULL after populating
ALTER TABLE users ALTER COLUMN username SET NOT NULL;

-- Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);

-- Verification query
SELECT 'Migration 009 completed successfully' as status;

