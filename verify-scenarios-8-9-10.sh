#!/bin/bash
# Verification script for Hotspot Scenarios 8, 9, 10 Implementation

echo "=========================================="
echo "Hotspot Scenarios 8, 9, 10 Verification"
echo "=========================================="
echo ""

# Check if files exist
echo "1. Checking files..."
files=(
    "app/Models/HotspotLoginLog.php"
    "app/Services/HotspotScenarioDetectionService.php"
    "app/Http/Controllers/HotspotLoginController.php"
    "database/migrations/2026_01_24_151707_create_hotspot_login_logs_table.php"
    "database/migrations/2026_01_24_151935_create_operator_registry_table.php"
    "resources/views/hotspot-login/link-dashboard.blade.php"
    "HOTSPOT_SCENARIOS_8_9_10_GUIDE.md"
    "HOTSPOT_SCENARIOS_8_9_10_SUMMARY.md"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "✓ $file"
    else
        echo "✗ $file (MISSING)"
    fi
done

echo ""
echo "2. Checking PHP syntax..."
php -l app/Models/HotspotLoginLog.php 2>&1 | grep -q "No syntax errors" && echo "✓ HotspotLoginLog.php" || echo "✗ HotspotLoginLog.php"
php -l app/Services/HotspotScenarioDetectionService.php 2>&1 | grep -q "No syntax errors" && echo "✓ HotspotScenarioDetectionService.php" || echo "✗ HotspotScenarioDetectionService.php"
php -l app/Http/Controllers/HotspotLoginController.php 2>&1 | grep -q "No syntax errors" && echo "✓ HotspotLoginController.php" || echo "✗ HotspotLoginController.php"

echo ""
echo "3. Checking routes..."
php artisan route:list --path=hotspot 2>&1 | grep -q "generate-link" && echo "✓ generate-link route" || echo "✗ generate-link route"
php artisan route:list --path=hotspot 2>&1 | grep -q "link-login" && echo "✓ link-login route" || echo "✗ link-login route"
php artisan route:list --path=hotspot 2>&1 | grep -q "federated" && echo "✓ federated route" || echo "✗ federated route"
php artisan route:list --path=hotspot 2>&1 | grep -q "link-dashboard" && echo "✓ link-dashboard route" || echo "✗ link-dashboard route"

echo ""
echo "4. Checking methods in HotspotScenarioDetectionService..."
grep -q "generateLinkLogin" app/Services/HotspotScenarioDetectionService.php && echo "✓ generateLinkLogin()" || echo "✗ generateLinkLogin()"
grep -q "verifyLinkLogin" app/Services/HotspotScenarioDetectionService.php && echo "✓ verifyLinkLogin()" || echo "✗ verifyLinkLogin()"
grep -q "handleLogout" app/Services/HotspotScenarioDetectionService.php && echo "✓ handleLogout()" || echo "✗ handleLogout()"
grep -q "updateRadacctOnLogout" app/Services/HotspotScenarioDetectionService.php && echo "✓ updateRadacctOnLogout()" || echo "✗ updateRadacctOnLogout()"
grep -q "crossRadiusLookup" app/Services/HotspotScenarioDetectionService.php && echo "✓ crossRadiusLookup()" || echo "✗ crossRadiusLookup()"
grep -q "findHomeOperator" app/Services/HotspotScenarioDetectionService.php && echo "✓ findHomeOperator()" || echo "✗ findHomeOperator()"

echo ""
echo "5. Checking methods in HotspotLoginController..."
grep -q "generateLinkLogin" app/Http/Controllers/HotspotLoginController.php && echo "✓ generateLinkLogin()" || echo "✗ generateLinkLogin()"
grep -q "processLinkLogin" app/Http/Controllers/HotspotLoginController.php && echo "✓ processLinkLogin()" || echo "✗ processLinkLogin()"
grep -q "showLinkDashboard" app/Http/Controllers/HotspotLoginController.php && echo "✓ showLinkDashboard()" || echo "✗ showLinkDashboard()"
grep -q "federatedLogin" app/Http/Controllers/HotspotLoginController.php && echo "✓ federatedLogin()" || echo "✗ federatedLogin()"
grep -q "sendDeviceChangeSms" app/Http/Controllers/HotspotLoginController.php && echo "✓ sendDeviceChangeSms()" || echo "✗ sendDeviceChangeSms()"
grep -q "sendSuspensionSms" app/Http/Controllers/HotspotLoginController.php && echo "✓ sendSuspensionSms()" || echo "✗ sendSuspensionSms()"
grep -q "sendLoginSuccessSms" app/Http/Controllers/HotspotLoginController.php && echo "✓ sendLoginSuccessSms()" || echo "✗ sendLoginSuccessSms()"

echo ""
echo "6. Checking migration status..."
php artisan migrate:status 2>&1 | grep -q "create_hotspot_login_logs_table" && echo "✓ hotspot_login_logs migration exists" || echo "✗ hotspot_login_logs migration"
php artisan migrate:status 2>&1 | grep -q "create_operator_registry_table" && echo "✓ operator_registry migration exists" || echo "✗ operator_registry migration"

echo ""
echo "=========================================="
echo "Verification Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Run: php artisan migrate"
echo "2. Test link generation: POST /hotspot/generate-link"
echo "3. Test logout tracking: POST /hotspot/logout"
echo "4. Test federated login: POST /hotspot/login/federated"
echo "5. Review documentation: HOTSPOT_SCENARIOS_8_9_10_GUIDE.md"
echo ""
