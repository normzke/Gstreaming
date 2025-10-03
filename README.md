BingeTV Platform
================

Overview
--------
BingeTV is a Kenyan TV streaming platform offering subscription packages, M-PESA integration, an admin portal, and a user portal. The project was restructured from the original GStreaming repository to a cleaner public/user separation and updated admin/user layouts.

Key Features
------------
- Multi-portal architecture: public marketing pages, user portal, admin portal
- PostgreSQL database (legacy-compatible with 9.2)
- M-PESA Lipa na M-PESA integration (key-value config, transactions table)
- Orders-first flow with external streaming URL provisioning after admin confirmation
- SEO on public pages; consistent futuristic design
- Remember-me tokens, session management, activity logs, audit logs

Directories
-----------
- public/: marketing site and landing pages
- user/: authenticated user portal (subscriptions, channels, gallery, support)
- admin/: admin portal (dashboard, packages, channels, users, payments, analytics, settings)
- api/: API endpoints (e.g., M-PESA callbacks)
- config/: application and database configuration
- database/: migrations and schema
- lib/: shared libraries (seo, functions, services)
- scripts/: maintenance and deployment helpers

Setup
-----
1. Configure PostgreSQL credentials in `config/config.php` and `config/database.php`.
2. Create missing tables if needed via the provided SQL in `database/migrations`.
3. Configure M-PESA settings in `admin/mpesa-config.php` after login.

Branding Note
-------------
This repository originated as GStreaming and has been rebranded to BingeTV. File paths and UI copy reflect the BingeTV name.

License
-------
Private - All rights reserved.


