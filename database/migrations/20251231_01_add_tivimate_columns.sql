-- Add TiviMate credentials to users table
ALTER TABLE users ADD COLUMN IF NOT EXISTS tivimate_server VARCHAR(255);
ALTER TABLE users ADD COLUMN IF NOT EXISTS tivimate_username VARCHAR(255);
ALTER TABLE users ADD COLUMN IF NOT EXISTS tivimate_password VARCHAR(255);
ALTER TABLE users ADD COLUMN IF NOT EXISTS tivimate_expires_at TIMESTAMP;
ALTER TABLE users ADD COLUMN IF NOT EXISTS tivimate_active BOOLEAN DEFAULT false;
