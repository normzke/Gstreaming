-- Check all existing tables in the database
-- Run this in phpPgAdmin to see what tables you have

SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'public' 
  AND table_type = 'BASE TABLE'
ORDER BY table_name;

-- Expected tables for full functionality:
-- 1. users (with username column!)
-- 2. packages
-- 3. channels
-- 4. user_subscriptions
-- 5. payments
-- 6. gallery_items
-- 7. package_channels
-- 8. user_streaming_access
-- 9. remember_tokens
-- 10. admin_users

-- If any are missing, run the corresponding migration from database/migrations/

