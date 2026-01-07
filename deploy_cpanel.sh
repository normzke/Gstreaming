#!/bin/bash
# BingeTV Production Deployment Commands for cPanel Terminal
# Run these commands in your cPanel Terminal

echo "=========================================="
echo "BingeTV Paystack Integration Deployment"
echo "=========================================="
echo ""

# 1. Navigate to application directory
echo "Step 1: Navigating to application directory..."
cd /home1/fieldte5/bingetv.co.ke
echo "Current directory: $(pwd)"
echo ""

# 2. Run database migration for Paystack
echo "Step 2: Running Paystack database migration..."
PGPASSWORD='Normas@4340' psql -U fieldte5_bingetv1 -d fieldte5_bingetv -h /var/run/postgresql << 'EOF'
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
EOF

echo ""
echo "Step 3: Verifying database tables..."
PGPASSWORD='Normas@4340' psql -U fieldte5_bingetv1 -d fieldte5_bingetv -h /var/run/postgresql -c "\dt paystack*"
echo ""

# 4. Verify Paystack config records
echo "Step 4: Checking Paystack configuration..."
PGPASSWORD='Normas@4340' psql -U fieldte5_bingetv1 -d fieldte5_bingetv -h /var/run/postgresql -c "SELECT config_key, description FROM paystack_config;"
echo ""

# 5. Check file permissions
echo "Step 5: Checking file permissions..."
ls -lh admin/paystack-config.php
ls -lh admin/payments.php
ls -lh user/subscriptions/subscribe.php
echo ""

# 6. Verify admin navigation
echo "Step 6: Verifying admin navigation includes Paystack Config..."
grep -n "Paystack Config" admin/includes/header.php
echo ""

# 7. Verify user navigation
echo "Step 7: Verifying user navigation includes Pay Online..."
grep -n "Pay Online" user/includes/header.php
echo ""

# 8. Test admin Paystack config page accessibility
echo "Step 8: Checking if Paystack config page exists..."
if [ -f "admin/paystack-config.php" ]; then
    echo "✓ admin/paystack-config.php exists ($(stat -f%z admin/paystack-config.php) bytes)"
else
    echo "✗ admin/paystack-config.php NOT FOUND"
fi
echo ""

# 9. Clear any PHP cache (if applicable)
echo "Step 9: Clearing PHP cache..."
# Note: Adjust this based on your cPanel PHP configuration
if [ -d "cache" ]; then
    rm -rf cache/*
    echo "✓ Cache cleared"
else
    echo "No cache directory found"
fi
echo ""

echo "=========================================="
echo "Deployment Complete!"
echo "=========================================="
echo ""
echo "Next Steps:"
echo "1. Visit https://bingetv.co.ke/admin"
echo "2. Navigate to Settings > Paystack Config"
echo "3. Enter your Paystack API credentials"
echo "4. Test the connection"
echo "5. Enable Paystack payments"
echo ""
