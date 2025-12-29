#!/bin/bash
# Check if TiviMate migration has been run on production

echo "🔍 Checking TiviMate Migration Status on Production..."
echo "======================================================"
echo ""

# Check local database first
echo "📋 Checking LOCAL database..."
mysql -u root -p -e "USE bingetv; DESCRIBE users;" 2>/dev/null | grep -i "tivimate"

if [ $? -eq 0 ]; then
    echo "✅ LOCAL: TiviMate columns found in users table"
else
    echo "❌ LOCAL: TiviMate columns NOT found in users table"
    echo "   Run: mysql -u root -p bingetv < database/tivimate_migration.sql"
fi

echo ""
echo "📋 Checking PRODUCTION database..."
echo "   Visit: https://bingetv.co.ke/admin/migrate.php"
echo "   This will show you the migration status on production"
echo ""
echo "🔧 To run migration on production:"
echo "   1. Login to admin panel"
echo "   2. Go to: https://bingetv.co.ke/admin/migrate.php"
echo "   3. Click 'Run Pending Migrations'"
echo ""
