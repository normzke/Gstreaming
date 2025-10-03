# Pending Tasks for BingeTV Deployment

## Current Status
- ✅ Local file reorganization completed
- ✅ User-facing files moved to `user/` directory structure
- ✅ Admin files organized in `admin/` directory
- ✅ Core functions moved to `lib/` directory
- ✅ API endpoints created in `api/` directory
- ✅ Redirect files created for backward compatibility
- ⏳ Asset mirroring from `public/` to `user/` (CSS/JS files)
- ⏳ Remote sync to Bluehost server
- ⏳ Final testing and verification

## Immediate Next Steps

### 1. Complete Asset Mirroring
Run these commands to copy remaining CSS/JS files from `public/` to `user/`:

```bash
# Create user asset directories
mkdir -p /Users/la/Downloads/GStreaming/user/css /Users/la/Downloads/GStreaming/user/js /Users/la/Downloads/GStreaming/user/images

# Copy remaining CSS files
cp /Users/la/Downloads/GStreaming/public/css/channels.css /Users/la/Downloads/GStreaming/user/css/
cp /Users/la/Downloads/GStreaming/public/css/dashboard.css /Users/la/Downloads/GStreaming/user/css/
cp /Users/la/Downloads/GStreaming/public/css/subscribe.css /Users/la/Downloads/GStreaming/user/css/

# Copy remaining JS files
cp /Users/la/Downloads/GStreaming/public/js/channels.js /Users/la/Downloads/GStreaming/user/js/
cp /Users/la/Downloads/GStreaming/public/js/dashboard.js /Users/la/Downloads/GStreaming/user/js/
cp /Users/la/Downloads/GStreaming/public/js/subscribe.js /Users/la/Downloads/GStreaming/user/js/
```

### 2. Sync to Remote Server
SSH to Bluehost and sync the organized structure:

```bash
# SSH to Bluehost
ssh fieldtechs@fieldtechs.co.ke

# Once connected, sync the user directory
rsync -avz --delete /Users/la/Downloads/GStreaming/user/ fieldtechs@fieldtechs.co.ke:public_html/bingetv/user/

# Also sync admin, lib, and api directories
rsync -avz --delete /Users/la/Downloads/GStreaming/admin/ fieldtechs@fieldtechs.co.ke:public_html/bingetv/admin/
rsync -avz --delete /Users/la/Downloads/GStreaming/lib/ fieldtechs@fieldtechs.co.ke:public_html/bingetv/lib/
rsync -avz --delete /Users/la/Downloads/GStreaming/api/ fieldtechs@fieldtechs.co.ke:public_html/bingetv/api/
```

### 3. Update Main Entry Points
Update the main `index.php` and other root files to point to the new `user/` structure:

```bash
# Update main index.php to redirect to user/index.php
# Update .htaccess to handle new routing
# Ensure all internal links point to user/ directory
```

### 4. Test Functionality
After sync, test these key areas:

- **User Registration/Login**: `https://bingetv.co.ke/user/register.php` and `https://bingetv.co.ke/user/login.php`
- **User Dashboard**: `https://bingetv.co.ke/user/dashboard/`
- **Channels Page**: `https://bingetv.co.ke/user/channels.php`
- **Gallery Page**: `https://bingetv.co.ke/user/gallery.php`
- **Subscription Flow**: `https://bingetv.co.ke/user/subscriptions/subscribe.php`
- **Payment Processing**: `https://bingetv.co.ke/user/payments/process.php`
- **Admin Panel**: `https://bingetv.co.ke/admin/login.php`

### 5. Database Verification
Ensure all database tables are properly set up and seeded:

```sql
-- Check if all required tables exist
\dt

-- Verify admin user exists
SELECT * FROM admin_users;

-- Check packages table
SELECT * FROM packages;

-- Verify channels table structure
\d channels;
```

## File Structure After Completion

```
/Users/la/Downloads/GStreaming/
├── user/                          # User-facing application
│   ├── index.php                 # Main entry point
│   ├── login.php                 # User login
│   ├── register.php              # User registration
│   ├── channels.php              # Channels listing
│   ├── gallery.php               # Video gallery
│   ├── logout.php                # User logout
│   ├── dashboard/
│   │   └── index.php             # User dashboard
│   ├── subscriptions/
│   │   └── subscribe.php         # Subscription management
│   ├── payments/
│   │   └── process.php           # Payment processing
│   ├── css/                      # User stylesheets
│   ├── js/                       # User JavaScript
│   └── images/                   # User images
├── admin/                        # Admin panel
│   ├── login.php
│   ├── dashboard.php
│   └── users.php
├── lib/                          # Core functions
│   ├── functions.php
│   └── mpesa_integration.php
├── api/                          # API endpoints
│   └── mpesa/
│       └── callback.php
├── config/                       # Configuration files
├── database/                     # Database files
└── public/                       # Legacy redirects (can be cleaned up)
```

## Key URLs to Test

- **Main Site**: https://bingetv.co.ke/
- **User Login**: https://bingetv.co.ke/user/login.php
- **User Register**: https://bingetv.co.ke/user/register.php
- **User Dashboard**: https://bingetv.co.ke/user/dashboard/
- **Channels**: https://bingetv.co.ke/user/channels.php
- **Gallery**: https://bingetv.co.ke/user/gallery.php
- **Subscribe**: https://bingetv.co.ke/user/subscriptions/subscribe.php
- **Admin Login**: https://bingetv.co.ke/admin/login.php

## Notes for Next Session

1. The local file reorganization is complete
2. All user-facing files are now properly organized in the `user/` directory
3. Admin files are in the `admin/` directory
4. Core functions are in the `lib/` directory
5. API endpoints are in the `api/` directory
6. The main remaining tasks are asset mirroring and remote sync
7. After sync, comprehensive testing is needed to ensure all functionality works
8. The site should be fully functional with the new organized structure

## SSH Details
- **Host**: fieldtechs@fieldtechs.co.ke
- **Remote Path**: public_html/bingetv/
- **Domain**: bingetv.co.ke
- **Database**: PostgreSQL (configured in config/config.php)
