# cPanel Terminal Commands - Quick Reference

## Option 1: Run Complete Deployment Script

```bash
cd /home1/fieldte5/bingetv.co.ke
bash deploy_cpanel.sh
```

## Option 2: Run Commands Individually

### 1. Navigate to Application Directory
```bash
cd /home1/fieldte5/bingetv.co.ke
pwd
```

### 2. Run Database Migration
```bash
PGPASSWORD='Normas@4340' psql -U fieldte5_bingetv1 -d fieldte5_bingetv -h /var/run/postgresql -f database/migrations/add_paystack_config.sql
```

### 3. Verify Tables Created
```bash
PGPASSWORD='Normas@4340' psql -U fieldte5_bingetv1 -d fieldte5_bingetv -h /var/run/postgresql -c "\dt paystack*"
```

### 4. Check Paystack Configuration
```bash
PGPASSWORD='Normas@4340' psql -U fieldte5_bingetv1 -d fieldte5_bingetv -h /var/run/postgresql -c "SELECT config_key, description FROM paystack_config;"
```

### 5. Verify Files Exist
```bash
ls -lh admin/paystack-config.php
ls -lh admin/payments.php
ls -lh user/subscriptions/subscribe.php
```

### 6. Check Navigation Updates
```bash
grep -n "Paystack Config" admin/includes/header.php
grep -n "Pay Online" user/includes/header.php
```

### 7. Test Admin Page Access
```bash
curl -I https://bingetv.co.ke/admin/paystack-config
```

## Troubleshooting

### If Database Connection Fails
```bash
# Check PostgreSQL is running
ps aux | grep postgres

# Test connection
PGPASSWORD='Normas@4340' psql -U fieldte5_bingetv1 -d fieldte5_bingetv -h /var/run/postgresql -c "SELECT version();"
```

### If Files Are Missing
```bash
# Check if files were uploaded
find /home1/fieldte5/bingetv.co.ke -name "paystack-config.php"
find /home1/fieldte5/bingetv.co.ke -name "subscribe.js" -mtime -1
```

### Clear PHP Cache (if needed)
```bash
cd /home1/fieldte5/bingetv.co.ke
rm -rf cache/* tmp/*
```

## Verification URLs

After running commands, test these URLs:

1. **Admin Paystack Config**: https://bingetv.co.ke/admin/paystack-config
2. **User Subscriptions**: https://bingetv.co.ke/user/subscriptions#packages
3. **Admin Payments**: https://bingetv.co.ke/admin/payments
4. **User Dashboard**: https://bingetv.co.ke/user/dashboard
