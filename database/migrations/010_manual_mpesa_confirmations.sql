-- Migration 010: Manual M-Pesa Confirmation System
-- Date: 2025-10-08
-- Purpose: Allow users to submit M-Pesa confirmation messages for admin approval

CREATE TABLE IF NOT EXISTS manual_payment_submissions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    payment_id INTEGER REFERENCES payments(id) ON DELETE SET NULL,
    package_id INTEGER REFERENCES packages(id),
    amount DECIMAL(10,2) NOT NULL,
    mpesa_code VARCHAR(50),
    mpesa_message TEXT NOT NULL,
    phone_number VARCHAR(20),
    submitted_at TIMESTAMP DEFAULT NOW(),
    status VARCHAR(20) DEFAULT 'pending',
    admin_id INTEGER REFERENCES admin_users(id),
    admin_notes TEXT,
    reviewed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    CONSTRAINT check_status CHECK (status IN ('pending', 'approved', 'rejected', 'duplicate'))
);

-- Index for faster queries
CREATE INDEX IF NOT EXISTS idx_manual_payments_status ON manual_payment_submissions(status);
CREATE INDEX IF NOT EXISTS idx_manual_payments_user ON manual_payment_submissions(user_id);
CREATE INDEX IF NOT EXISTS idx_manual_payments_submitted ON manual_payment_submissions(submitted_at);

-- Add column to payments table to track if it's manual
ALTER TABLE payments ADD COLUMN IF NOT EXISTS is_manual_confirmation BOOLEAN DEFAULT false;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS manual_submission_id INTEGER REFERENCES manual_payment_submissions(id);

COMMENT ON TABLE manual_payment_submissions IS 'Stores user-submitted M-Pesa confirmation messages for manual admin approval';

