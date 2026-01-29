#!/usr/bin/env bash

# RouterOS Dual API Support Installation and Testing Script
# This script helps you set up and test the dual API support for RouterOS v6 and v7

set -e

echo "=========================================="
echo "RouterOS Dual API Support Setup"
echo "=========================================="
echo ""

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    echo "❌ Error: composer.json not found. Please run this script from the project root."
    exit 1
fi

echo "Step 1: Installing Binary API Library"
echo "--------------------------------------"
echo "Running: composer require bencroker/routeros-api-php:^1.0"
echo ""

if composer require bencroker/routeros-api-php:^1.0; then
    echo "✅ Binary API library installed successfully"
else
    echo "❌ Failed to install library. Check your composer setup."
    exit 1
fi

echo ""
echo "Step 2: Running Database Migration"
echo "-----------------------------------"
echo "Adding api_type field to mikrotik_routers table..."
echo ""

if php artisan migrate --force; then
    echo "✅ Migration completed successfully"
else
    echo "❌ Migration failed. Check database connection."
    exit 1
fi

echo ""
echo "Step 3: Clearing Configuration Cache"
echo "-------------------------------------"

php artisan config:clear
php artisan cache:clear

echo "✅ Cache cleared"

echo ""
echo "=========================================="
echo "Setup Complete!"
echo "=========================================="
echo ""
echo "Next Steps:"
echo ""
echo "1. Ensure your RouterOS v6 routers have API service enabled:"
echo "   /ip service set api disabled=no port=8728"
echo ""
echo "2. For RouterOS v7 routers, enable either:"
echo "   a) Binary API (recommended): /ip service set api disabled=no port=8728"
echo "   b) REST API: /ip service set www disabled=no port=8777"
echo ""
echo "3. Test connection:"
echo "   php artisan tinker"
echo "   >>> \$router = App\Models\MikrotikRouter::find(1);"
echo "   >>> \$service = app(App\Services\MikrotikApiService::class);"
echo "   >>> \$profiles = \$service->getMktRows(\$router, '/ppp/profile');"
echo ""
echo "4. Check logs for API type detection:"
echo "   tail -f storage/logs/laravel.log"
echo ""
echo "Documentation: See ROUTEROS_DUAL_API_SUPPORT.md"
echo ""
