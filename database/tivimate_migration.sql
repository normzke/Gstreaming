-- BingeTV TiviMate Integration Migration
-- Add TiviMate credentials to users table

ALTER TABLE users ADD COLUMN IF NOT EXISTS tivimate_server VARCHAR(255) COMMENT 'TiviMate server URL';
ALTER TABLE users ADD COLUMN IF NOT EXISTS tivimate_username VARCHAR(100) COMMENT 'TiviMate username';
ALTER TABLE users ADD COLUMN IF NOT EXISTS tivimate_password VARCHAR(100) COMMENT 'TiviMate password';
ALTER TABLE users ADD COLUMN IF NOT EXISTS tivimate_expires_at DATE COMMENT 'TiviMate subscription expiration date';
ALTER TABLE users ADD COLUMN IF NOT EXISTS tivimate_active BOOLEAN DEFAULT 0 COMMENT 'TiviMate credentials active status';

-- Add index for faster lookups
CREATE INDEX IF NOT EXISTS idx_tivimate_active ON users(tivimate_active);
