#!/bin/bash

# ISP Solution - Fix Verification Script
# This script helps verify that all the fixes have been properly applied

echo "=============================================="
echo "ISP Solution - Fix Verification Script"
echo "=============================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Track overall status
ERRORS=0

echo "1. Checking Migration Files..."
MIGRATIONS=(
    "database/migrations/2026_01_23_042741_add_missing_columns_to_payments_table.php"
    "database/migrations/2026_01_23_042742_add_missing_columns_to_network_users_table.php"
    "database/migrations/2026_01_23_042743_add_missing_columns_to_mikrotik_routers_table.php"
)

for migration in "${MIGRATIONS[@]}"; do
    if [ -f "$migration" ]; then
        echo -e "${GREEN}✓${NC} Found: $migration"
    else
        echo -e "${RED}✗${NC} Missing: $migration"
        ERRORS=$((ERRORS+1))
    fi
done
echo ""

echo "2. Checking Model Updates..."
MODELS=(
    "app/Models/Payment.php"
    "app/Models/NetworkUser.php"
    "app/Models/MikrotikRouter.php"
)

for model in "${MODELS[@]}"; do
    if [ -f "$model" ]; then
        echo -e "${GREEN}✓${NC} Found: $model"
    else
        echo -e "${RED}✗${NC} Missing: $model"
        ERRORS=$((ERRORS+1))
    fi
done
echo ""

echo "3. Checking for service_packages references (should return 0 results)..."
SERVICE_PACKAGES_COUNT=$(grep -r "service_packages" app/ --include="*.php" | grep -v ".git" | wc -l)
if [ "$SERVICE_PACKAGES_COUNT" -eq 0 ]; then
    echo -e "${GREEN}✓${NC} No service_packages references found (correct)"
else
    echo -e "${YELLOW}⚠${NC} Found $SERVICE_PACKAGES_COUNT references to service_packages"
    echo "   This may be expected in comments or documentation"
fi
echo ""

echo "4. Checking Route Definitions..."
if grep -q "Route::put('/operators/{id}/special-permissions'" routes/web.php; then
    echo -e "${GREEN}✓${NC} PUT route for special-permissions exists"
else
    echo -e "${RED}✗${NC} PUT route for special-permissions not found"
    ERRORS=$((ERRORS+1))
fi
echo ""

echo "5. Checking Controller Methods..."
if grep -q "updateOperatorSpecialPermissions" app/Http/Controllers/Panel/AdminController.php; then
    echo -e "${GREEN}✓${NC} updateOperatorSpecialPermissions method exists"
else
    echo -e "${RED}✗${NC} updateOperatorSpecialPermissions method not found"
    ERRORS=$((ERRORS+1))
fi
echo ""

echo "6. Checking Documentation..."
DOCS=(
    "FEATURE_IMPLEMENTATION_GUIDE.md"
    "FIX_SUMMARY.md"
)

for doc in "${DOCS[@]}"; do
    if [ -f "$doc" ]; then
        echo -e "${GREEN}✓${NC} Found: $doc"
    else
        echo -e "${RED}✗${NC} Missing: $doc"
        ERRORS=$((ERRORS+1))
    fi
done
echo ""

echo "7. Checking Export Routes..."
EXPORT_ROUTES=(
    "reports.transactions.export"
    "reports.payable.export"
    "reports.receivable.export"
    "reports.income-expense.export"
    "reports.expenses.export"
    "reports.vat-collections.export"
)

for route in "${EXPORT_ROUTES[@]}"; do
    if grep -q "$route" routes/web.php; then
        echo -e "${GREEN}✓${NC} Route exists: panel.admin.$route"
    else
        echo -e "${RED}✗${NC} Route missing: panel.admin.$route"
        ERRORS=$((ERRORS+1))
    fi
done
echo ""

echo "=============================================="
echo "Verification Summary"
echo "=============================================="

if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}✓ All checks passed!${NC}"
    echo ""
    echo "Next Steps:"
    echo "1. Run migrations: php artisan migrate"
    echo "2. Clear cache: php artisan cache:clear"
    echo "3. Test the application"
    echo ""
    exit 0
else
    echo -e "${RED}✗ Found $ERRORS error(s)${NC}"
    echo ""
    echo "Please review the errors above and ensure all fixes are properly applied."
    echo ""
    exit 1
fi
