-- Add email verification fields to users table
-- This migration adds email verification functionality

-- Add email verification columns if they don't exist
DO $$
BEGIN
    -- Add email_verification_token column
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns
                   WHERE table_name='users'
                   AND column_name='email_verification_token') THEN
        ALTER TABLE users ADD COLUMN email_verification_token VARCHAR(64);
    END IF;

    -- Add email_verification_expires column
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns
                   WHERE table_name='users'
                   AND column_name='email_verification_expires') THEN
        ALTER TABLE users ADD COLUMN email_verification_expires TIMESTAMP;
    END IF;

    -- Create index for email verification token
    IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE indexname = 'idx_users_email_verification_token') THEN
        CREATE INDEX idx_users_email_verification_token ON users(email_verification_token);
    END IF;

    -- Create index for email verification expires
    IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE indexname = 'idx_users_email_verification_expires') THEN
        CREATE INDEX idx_users_email_verification_expires ON users(email_verification_expires);
    END IF;
END $$;

-- Update existing users to have email_verified = true if they were already active
-- This ensures existing users can still login
UPDATE users SET email_verified = true WHERE is_active = true AND email_verified = false;
