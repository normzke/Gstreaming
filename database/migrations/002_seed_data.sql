-- Migration 002: Seed Data
-- Inserts initial data for the GStreaming platform

-- Insert default admin user
INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@BingeTV.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'super_admin')
ON CONFLICT (username) DO NOTHING;

-- Insert default M-PESA configuration
INSERT INTO mpesa_config (config_key, config_value, description, is_encrypted) VALUES
('consumer_key', '', 'M-PESA Consumer Key', true),
('consumer_secret', '', 'M-PESA Consumer Secret', true),
('shortcode', '', 'M-PESA Business Shortcode', false),
('passkey', '', 'M-PESA Passkey', true),
('callback_url', 'http://localhost:4000/GStreaming/api/mpesa/callback.php', 'M-PESA Callback URL', false),
('initiator_name', '', 'M-PESA Initiator Name', false),
('security_credential', '', 'M-PESA Security Credential (Encrypted Password)', true),
('environment', 'sandbox', 'M-PESA Environment (sandbox/production)', false),
('test_phone', '254708374149', 'Test phone number for sandbox', false)
ON CONFLICT (config_key) DO NOTHING;

-- Insert sample packages
INSERT INTO packages (name, description, price, duration_days, channels, quality, devices, features, sort_order) VALUES
('Basic Plan', 'Perfect for individuals with basic streaming needs', 500.00, 30, 50, 'HD', 1, ARRAY['HD Quality', '1 Device', '50+ Channels', '24/7 Support'], 1),
('Standard Plan', 'Great for families with multiple devices', 1000.00, 30, 100, 'HD', 3, ARRAY['HD Quality', '3 Devices', '100+ Channels', '24/7 Support', 'Premium Channels'], 2),
('Premium Plan', 'Ultimate streaming experience with all features', 1500.00, 30, 200, '4K', 5, ARRAY['4K Quality', '5 Devices', '200+ Channels', '24/7 Support', 'Premium Channels', 'Sports Package'], 3),
('Family Plan', 'Perfect for large families with unlimited access', 2000.00, 30, 300, '4K', 10, ARRAY['4K Quality', '10 Devices', '300+ Channels', '24/7 Support', 'Premium Channels', 'Sports Package', 'Kids Package'], 4),
('VIP Plan', 'Exclusive access to all premium content', 3000.00, 30, 500, '4K', 15, ARRAY['4K Quality', '15 Devices', '500+ Channels', '24/7 Support', 'Premium Channels', 'Sports Package', 'Kids Package', 'VIP Channels'], 5)
ON CONFLICT DO NOTHING;

-- Insert sample channels
INSERT INTO channels (name, description, category, language, country, logo_url, is_active, sort_order) VALUES
-- News Channels
('BBC News', 'British Broadcasting Corporation News', 'News', 'English', 'UK', '/assets/images/channels/bbc-news.png', true, 1),
('CNN', 'Cable News Network', 'News', 'English', 'USA', '/assets/images/channels/cnn.png', true, 2),
('Al Jazeera', 'Al Jazeera English', 'News', 'English', 'Qatar', '/assets/images/channels/al-jazeera.png', true, 3),
('Sky News', 'Sky News International', 'News', 'English', 'UK', '/assets/images/channels/sky-news.png', true, 4),
('France 24', 'France 24 English', 'News', 'English', 'France', '/assets/images/channels/france24.png', true, 5),

-- Sports Channels
('ESPN', 'Entertainment and Sports Programming Network', 'Sports', 'English', 'USA', '/assets/images/channels/espn.png', true, 10),
('Sky Sports', 'Sky Sports Network', 'Sports', 'English', 'UK', '/assets/images/channels/sky-sports.png', true, 11),
('SuperSport', 'SuperSport Network', 'Sports', 'English', 'South Africa', '/assets/images/channels/supersport.png', true, 12),
('beIN Sports', 'beIN Sports Network', 'Sports', 'English', 'Qatar', '/assets/images/channels/bein-sports.png', true, 13),
('Eurosport', 'Eurosport Network', 'Sports', 'English', 'Europe', '/assets/images/channels/eurosport.png', true, 14),

-- Entertainment Channels
('HBO', 'Home Box Office', 'Entertainment', 'English', 'USA', '/assets/images/channels/hbo.png', true, 20),
('Showtime', 'Showtime Networks', 'Entertainment', 'English', 'USA', '/assets/images/channels/showtime.png', true, 21),
('Starz', 'Starz Entertainment', 'Entertainment', 'English', 'USA', '/assets/images/channels/starz.png', true, 22),
('AMC', 'American Movie Classics', 'Entertainment', 'English', 'USA', '/assets/images/channels/amc.png', true, 23),
('FX', 'FX Network', 'Entertainment', 'English', 'USA', '/assets/images/channels/fx.png', true, 24),

-- Kids Channels
('Cartoon Network', 'Cartoon Network', 'Kids', 'English', 'USA', '/assets/images/channels/cartoon-network.png', true, 30),
('Disney Channel', 'Disney Channel', 'Kids', 'English', 'USA', '/assets/images/channels/disney-channel.png', true, 31),
('Nickelodeon', 'Nickelodeon', 'Kids', 'English', 'USA', '/assets/images/channels/nickelodeon.png', true, 32),
('Boomerang', 'Boomerang', 'Kids', 'English', 'USA', '/assets/images/channels/boomerang.png', true, 33),
('Disney Junior', 'Disney Junior', 'Kids', 'English', 'USA', '/assets/images/channels/disney-junior.png', true, 34),

-- African Channels
('NTV Kenya', 'Nation Television Kenya', 'News', 'English', 'Kenya', '/assets/images/channels/ntv-kenya.png', true, 40),
('KBC', 'Kenya Broadcasting Corporation', 'General', 'English', 'Kenya', '/assets/images/channels/kbc.png', true, 41),
('Citizen TV', 'Citizen Television', 'General', 'English', 'Kenya', '/assets/images/channels/citizen-tv.png', true, 42),
('KTN News', 'Kenya Television Network News', 'News', 'English', 'Kenya', '/assets/images/channels/ktn-news.png', true, 43),
('K24', 'K24 Television', 'News', 'English', 'Kenya', '/assets/images/channels/k24.png', true, 44),

-- Music Channels
('MTV', 'Music Television', 'Music', 'English', 'USA', '/assets/images/channels/mtv.png', true, 50),
('VH1', 'Video Hits One', 'Music', 'English', 'USA', '/assets/images/channels/vh1.png', true, 51),
('BET', 'Black Entertainment Television', 'Music', 'English', 'USA', '/assets/images/channels/bet.png', true, 52),
('Trace TV', 'Trace Television', 'Music', 'English', 'France', '/assets/images/channels/trace-tv.png', true, 53),
('Channel O', 'Channel O Africa', 'Music', 'English', 'South Africa', '/assets/images/channels/channel-o.png', true, 54)
ON CONFLICT DO NOTHING;

-- Create package-channel relationships
-- Basic Plan (50 channels)
INSERT INTO package_channels (package_id, channel_id) 
SELECT 1, id FROM channels WHERE sort_order <= 50
ON CONFLICT DO NOTHING;

-- Standard Plan (100 channels)
INSERT INTO package_channels (package_id, channel_id) 
SELECT 2, id FROM channels WHERE sort_order <= 100
ON CONFLICT DO NOTHING;

-- Premium Plan (200 channels)
INSERT INTO package_channels (package_id, channel_id) 
SELECT 3, id FROM channels WHERE sort_order <= 200
ON CONFLICT DO NOTHING;

-- Family Plan (300 channels)
INSERT INTO package_channels (package_id, channel_id) 
SELECT 4, id FROM channels WHERE sort_order <= 300
ON CONFLICT DO NOTHING;

-- VIP Plan (500 channels)
INSERT INTO package_channels (package_id, channel_id) 
SELECT 5, id FROM channels WHERE sort_order <= 500
ON CONFLICT DO NOTHING;

-- Insert sample gallery items
INSERT INTO gallery_items (title, description, image_url, video_url, category, sort_order) VALUES
('GStreaming Hero Video', 'Welcome to GStreaming - Your Ultimate Streaming Platform', '/assets/images/gallery/hero-video.jpg', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'Hero', 1),
('Premium Quality Streaming', 'Experience crystal clear 4K streaming on all your devices', '/assets/images/gallery/premium-quality.jpg', NULL, 'Features', 2),
('Multi-Device Support', 'Stream on Smart TVs, Firestick, Roku, and mobile devices', '/assets/images/gallery/multi-device.jpg', NULL, 'Features', 3),
('24/7 Customer Support', 'Get help anytime with our dedicated support team', '/assets/images/gallery/support.jpg', NULL, 'Features', 4),
('Sports Package', 'Watch all your favorite sports events live', '/assets/images/gallery/sports.jpg', NULL, 'Packages', 5),
('Kids Package', 'Safe and entertaining content for children', '/assets/images/gallery/kids.jpg', NULL, 'Packages', 6),
('News Channels', 'Stay updated with the latest news from around the world', '/assets/images/gallery/news.jpg', NULL, 'Packages', 7),
('Entertainment Channels', 'Enjoy movies, series, and entertainment shows', '/assets/images/gallery/entertainment.jpg', NULL, 'Packages', 8)
ON CONFLICT DO NOTHING;
