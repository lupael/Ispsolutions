#!/bin/bash
#
# Verification Script for REFERENCE_SYSTEM_QUICK_GUIDE.md Implementation
# This script verifies all 4 HIGH priority features are implemented
#
# Usage: ./verify-reference-implementation.sh
#

set -e

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║   REFERENCE SYSTEM IMPLEMENTATION VERIFICATION                 ║"
echo "║   Checking all 4 HIGH Priority Features                        ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counters
TOTAL_CHECKS=0
PASSED_CHECKS=0

check_file() {
    TOTAL_CHECKS=$((TOTAL_CHECKS + 1))
    if [ -f "$1" ]; then
        echo -e "${GREEN}✓${NC} $2"
        PASSED_CHECKS=$((PASSED_CHECKS + 1))
        return 0
    else
        echo -e "${RED}✗${NC} $2 (Missing: $1)"
        return 1
    fi
}

check_directory() {
    TOTAL_CHECKS=$((TOTAL_CHECKS + 1))
    if [ -d "$1" ]; then
        echo -e "${GREEN}✓${NC} $2"
        PASSED_CHECKS=$((PASSED_CHECKS + 1))
        return 0
    else
        echo -e "${RED}✗${NC} $2 (Missing: $1)"
        return 1
    fi
}

check_string_in_file() {
    TOTAL_CHECKS=$((TOTAL_CHECKS + 1))
    if grep -q "$2" "$1" 2>/dev/null; then
        echo -e "${GREEN}✓${NC} $3"
        PASSED_CHECKS=$((PASSED_CHECKS + 1))
        return 0
    else
        echo -e "${RED}✗${NC} $3 (Not found in $1)"
        return 1
    fi
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1️⃣  SMS PAYMENT INTEGRATION"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

check_file "app/Models/SmsPayment.php" "Model: SmsPayment"
check_file "app/Http/Controllers/Panel/SmsPaymentController.php" "Controller: SmsPaymentController"
check_file "app/Services/SmsBalanceService.php" "Service: SmsBalanceService"
check_file "app/Notifications/SmsBalanceLowNotification.php" "Notification: Low Balance"
check_file "app/Notifications/SmsPaymentSuccessNotification.php" "Notification: Payment Success"
check_file "app/Notifications/SmsPaymentFailedNotification.php" "Notification: Payment Failed"
check_file "app/Console/Commands/CheckSmsBalanceCommand.php" "Command: Check SMS Balance"
check_file "resources/views/panels/shared/widgets/sms-balance.blade.php" "Widget: SMS Balance"
check_file "resources/views/panels/operator/sms-payments/index.blade.php" "View: SMS Payments Index"
check_file "resources/views/panels/operator/sms-payments/create.blade.php" "View: SMS Payments Create"
check_file "tests/Feature/SmsPaymentTest.php" "Test: SMS Payment Feature"
check_file "SMS_PAYMENT_USER_GUIDE.md" "Documentation: SMS Payment User Guide"
check_string_in_file "routes/console.php" "sms:check-balance" "Schedule: SMS Balance Check"
check_string_in_file "routes/api.php" "sms-payments" "Routes: SMS Payment API"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2️⃣  AUTO-DEBIT SYSTEM"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

check_file "app/Models/AutoDebitHistory.php" "Model: AutoDebitHistory"
check_file "app/Http/Controllers/Panel/AutoDebitController.php" "Controller: AutoDebitController"
check_file "app/Jobs/ProcessAutoDebitJob.php" "Job: ProcessAutoDebitJob"
check_file "app/Console/Commands/ProcessAutoDebitPayments.php" "Command: Process Auto-Debit"
check_file "app/Notifications/AutoDebitSuccessNotification.php" "Notification: Success"
check_file "app/Notifications/AutoDebitFailedNotification.php" "Notification: Failed"
check_file "resources/views/panels/customer/auto-debit/index.blade.php" "View: Auto-Debit Settings"
check_file "tests/Feature/AutoDebitTest.php" "Test: Auto-Debit Feature"
check_file "tests/Unit/Models/AutoDebitHistoryTest.php" "Test: AutoDebitHistory Unit"
check_file "AUTO_DEBIT_USER_GUIDE.md" "Documentation: Auto-Debit User Guide"
check_string_in_file "routes/console.php" "auto-debit:process" "Schedule: Auto-Debit Processing"
check_string_in_file "routes/api.php" "auto-debit" "Routes: Auto-Debit API"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "3️⃣  SUBSCRIPTION PAYMENTS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

check_file "app/Models/OperatorSubscription.php" "Model: OperatorSubscription"
check_file "app/Models/SubscriptionPayment.php" "Model: SubscriptionPayment"
check_file "app/Http/Controllers/Panel/OperatorSubscriptionController.php" "Controller: Subscription"
check_file "app/Http/Controllers/Panel/SubscriptionPaymentController.php" "Controller: Payment"
check_file "app/Services/SubscriptionBillingService.php" "Service: Billing"
check_file "app/Console/Commands/GenerateOperatorSubscriptionBills.php" "Command: Generate Bills"
check_file "app/Console/Commands/SendSubscriptionRemindersCommand.php" "Command: Send Reminders"
check_file "app/Notifications/SubscriptionPaymentDueNotification.php" "Notification: Payment Due"
check_file "app/Notifications/SubscriptionPaymentSuccessNotification.php" "Notification: Payment Success"
check_file "app/Notifications/SubscriptionRenewalReminderNotification.php" "Notification: Renewal Reminder"
check_file "resources/views/panels/operator/subscriptions/index.blade.php" "View: Subscriptions Index"
check_file "resources/views/pdf/subscription-bill.blade.php" "PDF: Invoice Template"
check_file "resources/views/emails/subscription-renewal.blade.php" "Email: Renewal Reminder"
check_file "SUBSCRIPTION_MANAGEMENT_USER_GUIDE.md" "Documentation: Subscription User Guide"
check_string_in_file "routes/console.php" "subscription:generate-bills" "Schedule: Generate Bills"
check_string_in_file "routes/console.php" "subscription:send-reminders" "Schedule: Send Reminders"
check_string_in_file "routes/api.php" "subscription-payments" "Routes: Subscription Payment API"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "4️⃣  BKASH TOKENIZATION"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

check_file "app/Models/BkashAgreement.php" "Model: BkashAgreement"
check_file "app/Models/BkashToken.php" "Model: BkashToken"
check_file "app/Services/BkashTokenizationService.php" "Service: Tokenization"
check_file "app/Http/Controllers/Panel/BkashAgreementController.php" "Controller: Agreement"
check_file "resources/views/panels/payment-methods/index.blade.php" "View: Payment Methods Index"
check_file "resources/views/panels/payment-methods/create.blade.php" "View: Add Payment Method"
check_file "resources/views/panels/payment-methods/callback.blade.php" "View: Callback Handler"
check_string_in_file "routes/api.php" "bkash-agreements" "Routes: Bkash Agreement API"
check_string_in_file "routes/web.php" "bkash-agreements.callback" "Routes: Callback Handler"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊  DATABASE MIGRATIONS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Check for migration files
if ls database/migrations/*sms_payments*.php 1> /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Migration: SMS Payments"
    PASSED_CHECKS=$((PASSED_CHECKS + 1))
else
    echo -e "${RED}✗${NC} Migration: SMS Payments"
fi
TOTAL_CHECKS=$((TOTAL_CHECKS + 1))

if ls database/migrations/*sms_balance_history*.php 1> /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Migration: SMS Balance History"
    PASSED_CHECKS=$((PASSED_CHECKS + 1))
else
    echo -e "${RED}✗${NC} Migration: SMS Balance History"
fi
TOTAL_CHECKS=$((TOTAL_CHECKS + 1))

if ls database/migrations/*auto_debit_history*.php 1> /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Migration: Auto-Debit History"
    PASSED_CHECKS=$((PASSED_CHECKS + 1))
else
    echo -e "${RED}✗${NC} Migration: Auto-Debit History"
fi
TOTAL_CHECKS=$((TOTAL_CHECKS + 1))

if ls database/migrations/*auto_debit_fields*.php 1> /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Migration: Auto-Debit User Fields"
    PASSED_CHECKS=$((PASSED_CHECKS + 1))
else
    echo -e "${RED}✗${NC} Migration: Auto-Debit User Fields"
fi
TOTAL_CHECKS=$((TOTAL_CHECKS + 1))

if ls database/migrations/*operator_subscriptions*.php 1> /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Migration: Operator Subscriptions"
    PASSED_CHECKS=$((PASSED_CHECKS + 1))
else
    echo -e "${RED}✗${NC} Migration: Operator Subscriptions"
fi
TOTAL_CHECKS=$((TOTAL_CHECKS + 1))

if ls database/migrations/*subscription_payments*.php 1> /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Migration: Subscription Payments"
    PASSED_CHECKS=$((PASSED_CHECKS + 1))
else
    echo -e "${RED}✗${NC} Migration: Subscription Payments"
fi
TOTAL_CHECKS=$((TOTAL_CHECKS + 1))

if ls database/migrations/*bkash_agreements*.php 1> /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Migration: Bkash Agreements"
    PASSED_CHECKS=$((PASSED_CHECKS + 1))
else
    echo -e "${RED}✗${NC} Migration: Bkash Agreements"
fi
TOTAL_CHECKS=$((TOTAL_CHECKS + 1))

if ls database/migrations/*bkash_tokens*.php 1> /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Migration: Bkash Tokens"
    PASSED_CHECKS=$((PASSED_CHECKS + 1))
else
    echo -e "${RED}✗${NC} Migration: Bkash Tokens"
fi
TOTAL_CHECKS=$((TOTAL_CHECKS + 1))

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📚  DOCUMENTATION"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

check_file "REFERENCE_SYSTEM_QUICK_GUIDE.md" "Quick Reference Guide"
check_file "REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md" "Implementation TODO"
check_file "REFERENCE_SYSTEM_IMPLEMENTATION_COMPLETE.md" "Implementation Complete Summary"
check_file "SMS_PAYMENT_USER_GUIDE.md" "SMS Payment User Guide"
check_file "AUTO_DEBIT_USER_GUIDE.md" "Auto-Debit User Guide"
check_file "SUBSCRIPTION_MANAGEMENT_USER_GUIDE.md" "Subscription User Guide"

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                     VERIFICATION SUMMARY                       ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

PERCENTAGE=$((PASSED_CHECKS * 100 / TOTAL_CHECKS))

echo -e "Total Checks:  ${YELLOW}$TOTAL_CHECKS${NC}"
echo -e "Passed:        ${GREEN}$PASSED_CHECKS${NC}"
echo -e "Failed:        ${RED}$((TOTAL_CHECKS - PASSED_CHECKS))${NC}"
echo -e "Success Rate:  ${YELLOW}$PERCENTAGE%${NC}"
echo ""

if [ $PERCENTAGE -ge 90 ]; then
    echo -e "${GREEN}╔════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║  ✅ IMPLEMENTATION COMPLETE - READY FOR TESTING               ║${NC}"
    echo -e "${GREEN}╚════════════════════════════════════════════════════════════════╝${NC}"
    exit 0
elif [ $PERCENTAGE -ge 70 ]; then
    echo -e "${YELLOW}╔════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${YELLOW}║  ⚠️  IMPLEMENTATION MOSTLY COMPLETE - SOME WORK REMAINING     ║${NC}"
    echo -e "${YELLOW}╚════════════════════════════════════════════════════════════════╝${NC}"
    exit 0
else
    echo -e "${RED}╔════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${RED}║  ❌ IMPLEMENTATION INCOMPLETE - SIGNIFICANT WORK NEEDED        ║${NC}"
    echo -e "${RED}╚════════════════════════════════════════════════════════════════╝${NC}"
    exit 1
fi
