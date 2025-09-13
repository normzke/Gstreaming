-- Migration 003: Test Data
-- Inserts test data for development and testing purposes

-- Insert test users
INSERT INTO users (username, email, phone, password_hash, first_name, last_name, email_verified, phone_verified, is_active) VALUES
('testuser1', 'testuser1@example.com', '254712345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test', 'User', true, true, true),
('testuser2', 'testuser2@example.com', '254712345679', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test', 'User2', true, true, true),
('testuser3', 'testuser3@example.com', '254712345680', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test', 'User3', true, true, true),
('inactiveuser', 'inactive@example.com', '254712345681', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Inactive', 'User', false, false, false)
ON CONFLICT (email) DO NOTHING;

-- Insert test subscriptions
INSERT INTO user_subscriptions (user_id, package_id, status, start_date, end_date, auto_renewal) VALUES
-- Active subscription for testuser1
((SELECT id FROM users WHERE username = 'testuser1'), 1, 'active', NOW(), NOW() + INTERVAL '30 days', true),
-- Expired subscription for testuser2
((SELECT id FROM users WHERE username = 'testuser2'), 2, 'expired', NOW() - INTERVAL '60 days', NOW() - INTERVAL '30 days', false),
-- Pending subscription for testuser3
((SELECT id FROM users WHERE username = 'testuser3'), 3, 'pending', NULL, NULL, false)
ON CONFLICT DO NOTHING;

-- Insert test payments
INSERT INTO payments (user_id, subscription_id, amount, payment_method, phone_number, status, transaction_date) VALUES
-- Completed payment for testuser1
((SELECT id FROM users WHERE username = 'testuser1'), 
 (SELECT id FROM user_subscriptions WHERE user_id = (SELECT id FROM users WHERE username = 'testuser1') LIMIT 1),
 500.00, 'mpesa', '254712345678', 'completed', NOW() - INTERVAL '5 days'),

-- Failed payment for testuser3
((SELECT id FROM users WHERE username = 'testuser3'), 
 (SELECT id FROM user_subscriptions WHERE user_id = (SELECT id FROM users WHERE username = 'testuser3') LIMIT 1),
 1500.00, 'mpesa', '254712345680', 'failed', NOW() - INTERVAL '2 days'),

-- Pending payment for testuser3
((SELECT id FROM users WHERE username = 'testuser3'), 
 (SELECT id FROM user_subscriptions WHERE user_id = (SELECT id FROM users WHERE username = 'testuser3') LIMIT 1),
 1500.00, 'mpesa', '254712345680', 'pending', NOW() - INTERVAL '1 hour')
ON CONFLICT DO NOTHING;

-- Insert test streaming access for active subscription
INSERT INTO user_streaming_access (user_id, subscription_id, streaming_url, username, password, is_active) VALUES
((SELECT id FROM users WHERE username = 'testuser1'), 
 (SELECT id FROM user_subscriptions WHERE user_id = (SELECT id FROM users WHERE username = 'testuser1') LIMIT 1),
 'http://localhost:4000/GStreaming/stream/testuser1_' || EXTRACT(EPOCH FROM NOW()),
 'testuser1_stream', 'stream_pass_123', true)
ON CONFLICT DO NOTHING;

-- Update user last login times for testing
UPDATE users SET last_login = NOW() - INTERVAL '1 day' WHERE username = 'testuser1';
UPDATE users SET last_login = NOW() - INTERVAL '5 days' WHERE username = 'testuser2';
UPDATE users SET last_login = NOW() - INTERVAL '30 days' WHERE username = 'testuser3';

-- Insert additional test channels for comprehensive testing
INSERT INTO channels (name, description, category, language, country, is_active, sort_order) VALUES
-- Additional News Channels
('Reuters TV', 'Reuters Television', 'News', 'English', 'UK', true, 60),
('Bloomberg TV', 'Bloomberg Television', 'News', 'English', 'USA', true, 61),
('CNBC', 'Consumer News and Business Channel', 'News', 'English', 'USA', true, 62),
('Fox News', 'Fox News Channel', 'News', 'English', 'USA', true, 63),
('MSNBC', 'Microsoft National Broadcasting Company', 'News', 'English', 'USA', true, 64),

-- Additional Sports Channels
('NFL Network', 'National Football League Network', 'Sports', 'English', 'USA', true, 70),
('NBA TV', 'National Basketball Association Television', 'Sports', 'English', 'USA', true, 71),
('MLB Network', 'Major League Baseball Network', 'Sports', 'English', 'USA', true, 72),
('NHL Network', 'National Hockey League Network', 'Sports', 'English', 'USA', true, 73),
('Golf Channel', 'Golf Channel', 'Sports', 'English', 'USA', true, 74),

-- Additional Entertainment Channels
('Comedy Central', 'Comedy Central', 'Entertainment', 'English', 'USA', true, 80),
('Discovery Channel', 'Discovery Channel', 'Documentary', 'English', 'USA', true, 81),
('National Geographic', 'National Geographic Channel', 'Documentary', 'English', 'USA', true, 82),
('History Channel', 'The History Channel', 'Documentary', 'English', 'USA', true, 83),
('Animal Planet', 'Animal Planet', 'Documentary', 'English', 'USA', true, 84),

-- Additional Kids Channels
('Nick Jr.', 'Nickelodeon Junior', 'Kids', 'English', 'USA', true, 90),
('Cartoonito', 'Cartoonito', 'Kids', 'English', 'USA', true, 91),
('BabyTV', 'BabyTV', 'Kids', 'English', 'Israel', true, 92),
('CBeebies', 'Children''s BBC', 'Kids', 'English', 'UK', true, 93),
('PBS Kids', 'Public Broadcasting Service Kids', 'Kids', 'English', 'USA', true, 94),

-- Additional African Channels
('KTN Home', 'Kenya Television Network Home', 'General', 'English', 'Kenya', true, 100),
('NTV Plus', 'Nation Television Plus', 'General', 'English', 'Kenya', true, 101),
('Kiss TV', 'Kiss Television', 'General', 'English', 'Kenya', true, 102),
('Kass TV', 'Kass Television', 'General', 'English', 'Kenya', true, 103),
('Inooro TV', 'Inooro Television', 'General', 'Kikuyu', 'Kenya', true, 104),

-- Additional Music Channels
('MTV Base', 'MTV Base Africa', 'Music', 'English', 'South Africa', true, 110),
('Trace Urban', 'Trace Urban', 'Music', 'English', 'France', true, 111),
('Club TV', 'Club Television', 'Music', 'English', 'South Africa', true, 112),
('SoundCity', 'SoundCity Africa', 'Music', 'English', 'Nigeria', true, 113),
('MTV Hits', 'MTV Hits', 'Music', 'English', 'USA', true, 114)
ON CONFLICT DO NOTHING;

-- Create additional package-channel relationships for comprehensive testing
-- Add more channels to existing packages
INSERT INTO package_channels (package_id, channel_id) 
SELECT 2, id FROM channels WHERE sort_order BETWEEN 60 AND 70 AND id NOT IN (SELECT channel_id FROM package_channels WHERE package_id = 2)
ON CONFLICT DO NOTHING;

INSERT INTO package_channels (package_id, channel_id) 
SELECT 3, id FROM channels WHERE sort_order BETWEEN 70 AND 80 AND id NOT IN (SELECT channel_id FROM package_channels WHERE package_id = 3)
ON CONFLICT DO NOTHING;

INSERT INTO package_channels (package_id, channel_id) 
SELECT 4, id FROM channels WHERE sort_order BETWEEN 80 AND 90 AND id NOT IN (SELECT channel_id FROM package_channels WHERE package_id = 4)
ON CONFLICT DO NOTHING;

INSERT INTO package_channels (package_id, channel_id) 
SELECT 5, id FROM channels WHERE sort_order BETWEEN 90 AND 114 AND id NOT IN (SELECT channel_id FROM package_channels WHERE package_id = 5)
ON CONFLICT DO NOTHING;

-- Insert test gallery items for comprehensive testing
INSERT INTO gallery_items (title, description, image_url, category, sort_order) VALUES
('Test Image 1', 'Test description for image 1', '/assets/images/test/test1.jpg', 'Test', 100),
('Test Image 2', 'Test description for image 2', '/assets/images/test/test2.jpg', 'Test', 101),
('Test Video 1', 'Test description for video 1', '/assets/images/test/test-video1.jpg', 'Test', 102),
('Test Video 2', 'Test description for video 2', '/assets/images/test/test-video2.jpg', 'Test', 103)
ON CONFLICT DO NOTHING;
