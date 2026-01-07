-- Add Paystack Configuration Table
CREATE TABLE IF NOT EXISTS paystack_config (
    id SERIAL PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT,
    description TEXT,
    is_encrypted BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add Paystack Transactions Table
CREATE TABLE IF NOT EXISTS paystack_transactions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    payment_id INTEGER REFERENCES payments(id),
    reference VARCHAR(100) UNIQUE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'KES',
    email VARCHAR(255) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    gateway_response JSONB,
    paid_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default Paystack configuration
INSERT INTO paystack_config (config_key, config_value, description, is_encrypted) VALUES
('public_key', '', 'Paystack Public Key (for frontend)', false),
('secret_key', '', 'Paystack Secret Key (for backend)', true),
('webhook_secret', '', 'Paystack Webhook Secret', true),
('environment', 'test', 'Paystack Environment (test/live)', false),
('is_active', '0', 'Enable/Disable Paystack Payments', false)
ON CONFLICT (config_key) DO NOTHING;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_paystack_transactions_reference ON paystack_transactions(reference);
CREATE INDEX IF NOT EXISTS idx_paystack_transactions_user_id ON paystack_transactions(user_id);
CREATE INDEX IF NOT EXISTS idx_paystack_transactions_status ON paystack_transactions(status);
CREATE INDEX IF NOT EXISTS idx_paystack_config_key ON paystack_config(config_key);
