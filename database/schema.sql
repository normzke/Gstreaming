-- GStreaming Database Schema
-- PostgreSQL Database for Kenyan TV Streaming Platform

-- Users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    country VARCHAR(50) DEFAULT 'Kenya',
    is_active BOOLEAN DEFAULT true,
    email_verified BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Subscription packages table
CREATE TABLE packages (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'KES',
    duration_days INTEGER NOT NULL,
    max_devices INTEGER DEFAULT 1,
    features JSONB,
    is_active BOOLEAN DEFAULT true,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User subscriptions table
CREATE TABLE user_subscriptions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    package_id INTEGER REFERENCES packages(id),
    status VARCHAR(20) DEFAULT 'active', -- active, expired, cancelled
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP NOT NULL,
    auto_renewal BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Payment transactions table
CREATE TABLE payments (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    subscription_id INTEGER REFERENCES user_subscriptions(id),
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'KES',
    payment_method VARCHAR(20) NOT NULL, -- mpesa, bank, card
    mpesa_receipt_number VARCHAR(50),
    mpesa_transaction_id VARCHAR(50),
    mpesa_checkout_request_id VARCHAR(100),
    phone_number VARCHAR(20),
    status VARCHAR(20) DEFAULT 'pending', -- pending, completed, failed, cancelled
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Channels table
CREATE TABLE channels (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    logo_url VARCHAR(255),
    stream_url VARCHAR(500),
    category VARCHAR(50),
    country VARCHAR(50),
    language VARCHAR(50),
    is_hd BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Package channels relationship
CREATE TABLE package_channels (
    id SERIAL PRIMARY KEY,
    package_id INTEGER REFERENCES packages(id) ON DELETE CASCADE,
    channel_id INTEGER REFERENCES channels(id) ON DELETE CASCADE,
    UNIQUE(package_id, channel_id)
);

-- User devices table
CREATE TABLE user_devices (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    device_name VARCHAR(100) NOT NULL,
    device_type VARCHAR(50), -- smart_tv, firestick, roku, mobile, tablet
    device_id VARCHAR(255),
    mac_address VARCHAR(17),
    ip_address INET,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Notifications table
CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL, -- renewal, payment, support, general
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT false,
    sent_email BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Support tickets table
CREATE TABLE support_tickets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    ticket_number VARCHAR(20) UNIQUE NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'open', -- open, in_progress, resolved, closed
    priority VARCHAR(10) DEFAULT 'medium', -- low, medium, high, urgent
    assigned_to INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Support ticket messages table
CREATE TABLE support_messages (
    id SERIAL PRIMARY KEY,
    ticket_id INTEGER REFERENCES support_tickets(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id),
    message TEXT NOT NULL,
    is_admin BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin users table
CREATE TABLE admin_users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin', -- admin, super_admin
    is_active BOOLEAN DEFAULT true,
    last_login TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- M-PESA configuration table
CREATE TABLE mpesa_config (
    id SERIAL PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT,
    description TEXT,
    is_encrypted BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User streaming access table
CREATE TABLE user_streaming_access (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    subscription_id INTEGER REFERENCES user_subscriptions(id) ON DELETE CASCADE,
    streaming_url VARCHAR(500) NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Gallery table
CREATE TABLE gallery (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    video_url VARCHAR(500),
    type VARCHAR(20) NOT NULL, -- image, video
    category VARCHAR(50),
    is_featured BOOLEAN DEFAULT false,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Email templates table
CREATE TABLE email_templates (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    variables JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_phone ON users(phone);
CREATE INDEX idx_user_subscriptions_user_id ON user_subscriptions(user_id);
CREATE INDEX idx_user_subscriptions_status ON user_subscriptions(status);
CREATE INDEX idx_user_subscriptions_end_date ON user_subscriptions(end_date);
CREATE INDEX idx_payments_user_id ON payments(user_id);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_payments_mpesa_receipt ON payments(mpesa_receipt_number);
CREATE INDEX idx_user_devices_user_id ON user_devices(user_id);
CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_support_tickets_user_id ON support_tickets(user_id);
CREATE INDEX idx_support_tickets_status ON support_tickets(status);

-- Insert default packages (based on common Kenyan streaming packages)
INSERT INTO packages (name, description, price, duration_days, max_devices, features) VALUES
('Basic Plan', 'Access to local Kenyan channels and basic international channels', 500.00, 30, 1, '{"channels": 50, "quality": "SD", "support": "email"}'),
('Premium Plan', 'Full access to premium channels including sports and movies', 1200.00, 30, 2, '{"channels": 200, "quality": "HD", "support": "priority"}'),
('Family Plan', 'Perfect for families with multiple devices and premium content', 2000.00, 30, 5, '{"channels": 500, "quality": "HD", "support": "priority", "kids_content": true}'),
('VIP Plan', 'Ultimate package with all channels and exclusive content', 3500.00, 30, 10, '{"channels": 1000, "quality": "4K", "support": "24/7", "exclusive": true}');

-- Insert default email templates
INSERT INTO email_templates (name, subject, body, variables) VALUES
('welcome', 'Welcome to GStreaming - Your Streaming Journey Begins!', '<h2>Welcome to GStreaming!</h2><p>Dear {{first_name}},</p><p>Thank you for joining GStreaming. Your account has been created successfully.</p><p>Get ready to enjoy thousands of channels on your favorite devices!</p>', '["first_name", "email"]'),
('subscription_confirmed', 'Subscription Confirmed - Welcome to {{package_name}}!', '<h2>Subscription Confirmed!</h2><p>Dear {{first_name}},</p><p>Your subscription to {{package_name}} has been confirmed.</p><p>Subscription Details:</p><ul><li>Package: {{package_name}}</li><li>Price: {{price}} {{currency}}</li><li>Valid Until: {{end_date}}</li></ul>', '["first_name", "package_name", "price", "currency", "end_date"]'),
('payment_receipt', 'Payment Receipt - Transaction {{receipt_number}}', '<h2>Payment Receipt</h2><p>Dear {{first_name}},</p><p>Thank you for your payment. Here are your transaction details:</p><ul><li>Receipt Number: {{receipt_number}}</li><li>Amount: {{amount}} {{currency}}</li><li>Date: {{transaction_date}}</li><li>Method: {{payment_method}}</li></ul>', '["first_name", "receipt_number", "amount", "currency", "transaction_date", "payment_method"]'),
('renewal_reminder', 'Subscription Renewal Reminder', '<h2>Renewal Reminder</h2><p>Dear {{first_name}},</p><p>Your subscription will expire on {{expiry_date}}. Renew now to continue enjoying uninterrupted streaming!</p><p>Package: {{package_name}}</p><p>Renewal Amount: {{price}} {{currency}}</p>', '["first_name", "expiry_date", "package_name", "price", "currency"]');

-- Insert sample channels
INSERT INTO channels (name, description, logo_url, stream_url, category, country, language, is_hd, is_active, sort_order) VALUES
-- Kenyan Channels
('Citizen TV', 'Kenya''s leading news and entertainment channel', 'https://logos-world.net/wp-content/uploads/2021/03/Citizen-TV-Logo.png', '', 'News', 'Kenya', 'English', true, true, 1),
('KBC TV', 'Kenya Broadcasting Corporation - National broadcaster', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8e/KBC_TV_logo.svg/1200px-KBC_TV_logo.svg.png', '', 'News', 'Kenya', 'English', true, true, 2),
('NTV Kenya', 'Nation Television - News and current affairs', 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7a/NTV_Kenya_logo.svg/1200px-NTV_Kenya_logo.svg.png', '', 'News', 'Kenya', 'English', true, true, 3),
('K24 TV', '24-hour news and current affairs channel', 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/02/K24_TV_logo.svg/1200px-K24_TV_logo.svg.png', '', 'News', 'Kenya', 'English', true, true, 4),
('KTN News', 'Kenya Television Network - News and entertainment', 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9f/KTN_News_logo.svg/1200px-KTN_News_logo.svg.png', '', 'News', 'Kenya', 'English', true, true, 5),
('Kiss TV', 'Music and entertainment channel', 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4a/Kiss_TV_logo.svg/1200px-Kiss_TV_logo.svg.png', '', 'Entertainment', 'Kenya', 'English', false, true, 6),
('Maisha Magic', 'Local content and dramas', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8e/Maisha_Magic_logo.svg/1200px-Maisha_Magic_logo.svg.png', '', 'Entertainment', 'Kenya', 'Swahili', true, true, 7),

-- International News
('BBC World News', 'Global news and current affairs', 'https://logos-world.net/wp-content/uploads/2021/03/BBC-World-News-Logo.png', '', 'News', 'UK', 'English', true, true, 10),
('CNN International', 'Cable News Network - International news', 'https://logos-world.net/wp-content/uploads/2021/03/CNN-Logo.png', '', 'News', 'USA', 'English', true, true, 11),
('Al Jazeera English', 'Global news from Qatar', 'https://logos-world.net/wp-content/uploads/2021/03/Al-Jazeera-Logo.png', '', 'News', 'Qatar', 'English', true, true, 12),
('France 24', 'French international news channel', 'https://logos-world.net/wp-content/uploads/2021/03/France-24-Logo.png', '', 'News', 'France', 'English', true, true, 13),
('DW News', 'Deutsche Welle - German international broadcaster', 'https://logos-world.net/wp-content/uploads/2021/03/DW-Logo.png', '', 'News', 'Germany', 'English', true, true, 14),

-- Sports Channels
('SuperSport', 'African sports broadcasting', 'https://logos-world.net/wp-content/uploads/2021/03/SuperSport-Logo.png', '', 'Sports', 'South Africa', 'English', true, true, 20),
('ESPN', 'Entertainment and Sports Programming Network', 'https://logos-world.net/wp-content/uploads/2021/03/ESPN-Logo.png', '', 'Sports', 'USA', 'English', true, true, 21),
('Sky Sports', 'UK sports broadcasting', 'https://logos-world.net/wp-content/uploads/2021/03/Sky-Sports-Logo.png', '', 'Sports', 'UK', 'English', true, true, 22),
('BeIN Sports', 'International sports network', 'https://logos-world.net/wp-content/uploads/2021/03/BeIN-Sports-Logo.png', '', 'Sports', 'Qatar', 'English', true, true, 23),

-- Entertainment Channels
('MTV', 'Music Television', 'https://logos-world.net/wp-content/uploads/2021/03/MTV-Logo.png', '', 'Entertainment', 'USA', 'English', true, true, 30),
('VH1', 'Video Hits One - Music and pop culture', 'https://logos-world.net/wp-content/uploads/2021/03/VH1-Logo.png', '', 'Entertainment', 'USA', 'English', true, true, 31),
('Comedy Central', 'Comedy programming', 'https://logos-world.net/wp-content/uploads/2021/03/Comedy-Central-Logo.png', '', 'Entertainment', 'USA', 'English', true, true, 32),
('Discovery Channel', 'Educational and documentary programming', 'https://logos-world.net/wp-content/uploads/2021/03/Discovery-Channel-Logo.png', '', 'Documentary', 'USA', 'English', true, true, 33),
('National Geographic', 'Nature and science documentaries', 'https://logos-world.net/wp-content/uploads/2021/03/National-Geographic-Logo.png', '', 'Documentary', 'USA', 'English', true, true, 34),

-- Movie Channels
('HBO', 'Home Box Office - Premium movies and series', 'https://logos-world.net/wp-content/uploads/2021/03/HBO-Logo.png', '', 'Movies', 'USA', 'English', true, true, 40),
('Showtime', 'Premium movie and series network', 'https://logos-world.net/wp-content/uploads/2021/03/Showtime-Logo.png', '', 'Movies', 'USA', 'English', true, true, 41),
('Star Movies', 'International movie channel', 'https://logos-world.net/wp-content/uploads/2021/03/Star-Movies-Logo.png', '', 'Movies', 'India', 'English', true, true, 42),
('AXN', 'Action and adventure movies', 'https://logos-world.net/wp-content/uploads/2021/03/AXN-Logo.png', '', 'Movies', 'Japan', 'English', true, true, 43),

-- Kids Channels
('Cartoon Network', 'Children''s animated programming', 'https://logos-world.net/wp-content/uploads/2021/03/Cartoon-Network-Logo.png', '', 'Kids', 'USA', 'English', true, true, 50),
('Disney Channel', 'Disney children''s programming', 'https://logos-world.net/wp-content/uploads/2021/03/Disney-Channel-Logo.png', '', 'Kids', 'USA', 'English', true, true, 51),
('Nickelodeon', 'Children''s entertainment network', 'https://logos-world.net/wp-content/uploads/2021/03/Nickelodeon-Logo.png', '', 'Kids', 'USA', 'English', true, true, 52),
('Boomerang', 'Classic cartoon network', 'https://logos-world.net/wp-content/uploads/2021/03/Boomerang-Logo.png', '', 'Kids', 'USA', 'English', true, true, 53);

-- Insert default M-PESA configuration
INSERT INTO mpesa_config (config_key, config_value, description, is_encrypted) VALUES
('consumer_key', '', 'M-PESA Consumer Key', true),
('consumer_secret', '', 'M-PESA Consumer Secret', true),
('shortcode', '', 'M-PESA Business Shortcode', false),
('passkey', '', 'M-PESA Passkey', true),
('callback_url', 'http://localhost:4000/api/mpesa/callback.php', 'M-PESA Callback URL', false),
('initiator_name', '', 'M-PESA Initiator Name', false),
('security_credential', '', 'M-PESA Security Credential (Encrypted Password)', true),
('environment', 'sandbox', 'M-PESA Environment (sandbox/production)', false),
('test_phone', '254708374149', 'Test phone number for sandbox', false);

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, password_hash, email, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@BingeTV.com', 'System Administrator', 'super_admin');
