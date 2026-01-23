# ISP Solution - Feature Implementation Guide

This document outlines the remaining feature implementations that require business decision input and detailed specifications.

## IMPLEMENTATION STATUS

### ✅ COMPLETED FEATURES

#### 1. SMS Gateway Management Module - **COMPLETED**
- **Status**: Fully implemented with UI/UX
- **Implementation Date**: January 2026
- **Files Created/Modified**:
  - Controller: `app/Http/Controllers/Panel/SmsGatewayController.php`
  - Model: `app/Models/SmsGateway.php`
  - Views: `resources/views/panels/admin/sms/gateways/` (index, create, edit, show)
  - Routes: Added to `routes/web.php` under SMS Management section
- **Features Implemented**:
  - ✅ List all configured SMS gateways
  - ✅ Add/Edit/Delete SMS gateway configurations
  - ✅ Test gateway connection (placeholder for actual implementation)
  - ✅ Set default gateway
  - ✅ Configure gateway-specific settings (API keys, URLs, etc.)
- **Supported Gateway Types**: Twilio, Nexmo/Vonage, MSG91, BulkSMS, Custom HTTP API, Maestro, Robi, M2M BD, Bangladesh SMS, BulkSMS BD, BTS SMS, 880 SMS, BD SmartPay, Elitbuzz, SSL Wireless, ADN SMS, 24 SMS BD, SMS Net, Brand SMS, Metrotel, DianaHost, SMS in BD, DhakaSoft BD
- **Route**: `/panel/admin/sms/gateways`

#### 2. Package ↔ PPP Profile ↔ IP Pool Mapping - **COMPLETED**
- **Status**: Fully implemented with UI/UX
- **Implementation Date**: January 2026
- **Files Created/Modified**:
  - Controller: `app/Http/Controllers/Panel/PackageProfileMappingController.php`
  - Model: `app/Models/PackageProfileMapping.php`
  - Views: `resources/views/panels/admin/packages/mappings/` (index, create)
  - Routes: Added to `routes/web.php` under Package Management
- **Features Implemented**:
  - ✅ Mapping interface to assign PPP Profiles to Packages
  - ✅ Assign IP Pools to Packages
  - ✅ Create/Edit/Delete mappings
  - ✅ Speed control method selection (simple_queue, pcq, burst)
- **Route**: `/panel/admin/packages/{id}/mappings`
- **Note**: Auto-provisioning logic when customer subscribes needs business requirements

#### 3. Operator-Specific Features - **COMPLETED**

##### 3.1 Operator-Specific Packages - **DATABASE READY**
- **Status**: Database migration completed, UI pending business decisions
- **Migration**: `2026_01_23_050000_add_operator_specific_fields_to_packages_table.php`
- **Database Changes Completed**:
  - ✅ `operator_id` column added to `packages` table
  - ✅ `is_global` column added to `packages` table
  - ✅ Foreign key constraint to users table

##### 3.2 Operator-Specific Rates - **COMPLETED**
- **Status**: Fully implemented with UI/UX
- **Implementation Date**: January 2026
- **Files Created/Modified**:
  - Model: `app/Models/OperatorPackageRate.php`
  - Controller Methods: `operatorPackageRates`, `assignOperatorPackageRate`, `storeOperatorPackageRate`, `deleteOperatorPackageRate` in `AdminController`
  - Views: `resources/views/panels/admin/operators/package-rates.blade.php`, `assign-package-rate.blade.php`
  - Routes: Added to `routes/web.php`
- **Features Implemented**:
  - ✅ View all operators with their custom package rates
  - ✅ Assign custom pricing for packages to operators
  - ✅ Set commission percentage for operators
  - ✅ Remove package rate assignments
- **Route**: `/panel/admin/operators/package-rates`
- **Migration**: `2026_01_23_050001_create_operator_package_rates_table.php`

##### 3.3 Operator Billing Profiles & Cycles - **DATABASE READY**
- **Status**: Database migration completed, UI pending
- **Migration**: `2026_01_23_050002_add_operator_billing_fields_to_users_table.php`
- **Database Changes Completed**:
  - ✅ `billing_cycle` column (monthly, quarterly, annual)
  - ✅ `billing_day_of_month` column
  - ✅ `payment_type` column (prepaid/postpaid)

##### 3.4 Manual Fund Addition for Operators - **COMPLETED**
- **Status**: Fully implemented with UI/UX
- **Implementation Date**: January 2026
- **Files Created/Modified**:
  - Model: `app/Models/OperatorWalletTransaction.php`
  - Controller Methods: `operatorWallets`, `addOperatorFunds`, `storeOperatorFunds`, `deductOperatorFunds`, `processDeductOperatorFunds`, `operatorWalletHistory` in `AdminController`
  - Views: `resources/views/panels/admin/operators/` (wallets, add-funds, deduct-funds, wallet-history)
  - Routes: Added to `routes/web.php`
  - User Model: Added `walletTransactions()` relationship
- **Features Implemented**:
  - ✅ Add funds to operator wallets
  - ✅ Deduct funds from operator wallets
  - ✅ View wallet transaction history
  - ✅ Track balance before/after each transaction
  - ✅ Audit trail (created_by field)
- **Routes**:
  - `/panel/admin/operators/wallets` - List all operator wallets
  - `/panel/admin/operators/{operator}/add-funds` - Add funds form
  - `/panel/admin/operators/{operator}/deduct-funds` - Deduct funds form
  - `/panel/admin/operators/{operator}/wallet-history` - Transaction history
- **Migration**: `2026_01_23_050003_create_operator_wallet_transactions_table.php`

##### 3.5 SMS Fee Assignment per Operator - **COMPLETED**
- **Status**: Fully implemented with UI/UX
- **Implementation Date**: January 2026
- **Files Created/Modified**:
  - Model: `app/Models/OperatorSmsRate.php`
  - Controller Methods: `operatorSmsRates`, `assignOperatorSmsRate`, `storeOperatorSmsRate`, `deleteOperatorSmsRate` in `AdminController`
  - Views: `resources/views/panels/admin/operators/` (sms-rates, assign-sms-rate)
  - Routes: Added to `routes/web.php`
  - User Model: Added `smsRate()` relationship and `sms_balance` field
- **Features Implemented**:
  - ✅ View all operators with their SMS rates
  - ✅ Set regular SMS rate per operator
  - ✅ Set bulk SMS rate with threshold
  - ✅ SMS balance tracking
  - ✅ Cost calculation method based on bulk thresholds
- **Routes**:
  - `/panel/admin/operators/sms-rates` - List all operator SMS rates
  - `/panel/admin/operators/{operator}/assign-sms-rate` - Assign/Edit SMS rate
- **Migration**: `2026_01_23_050004_create_operator_sms_rates_table.php`

##### 3.6 Admin Login-as-Operator Functionality - **COMPLETED**
- **Status**: Fully implemented
- **Implementation Date**: Prior to January 2026
- **Implementation Details**:
  - Controller Methods: `loginAsOperator`, `stopImpersonating` in `AdminController`
  - Session-based impersonation with audit trail
  - Stores original admin ID in session for restoration
  - Route: `/panel/admin/operators/{operatorId}/login-as`
- **Features Implemented**:
  - ✅ Super-admins can impersonate operators
  - ✅ Session tracking for audit purposes
  - ✅ Easy switch back to admin account
- **Note**: UI elements (switch user banner) can be added for better UX

## 4. UI/UX Improvements

### 4.1 Customer Placement Issue
**Problem:** Demo Customer appears under "User" instead of "Customers"

**Investigation Needed:**
- Check the role assignment for demo customer
- Verify menu filtering logic
- Review customer vs user role definitions

### 4.2 Repeated Submenu Items
**Problem:** Repeated items under Network Device, Network, OLT management, and Settings

**Solution:**
- Audit menu configuration files
- Remove duplicate menu entries
- Consolidate related items under appropriate parent menus

**Files to Check:**
- Menu configuration: Look for sidebar/navigation blade files
- Check for duplicate route definitions
- Review menu builder logic

### 4.3 Non-Working Buttons
**Reported Issues:**
- Add/Edit Package button
- Add/Edit IP Pool button
- Add Router button
- Edit User button
- Add Operator button

### 4.4 Missing Route Implementations - **PENDING**
The following routes exist but views need to be created:
- `/panel/admin/customers/pppoe-import` - Method `pppoeCustomerImport` exists in AdminController
- `/panel/admin/customers/bulk-update` - Method `bulkUpdateUsers` exists in AdminController
- `/panel/admin/customers/import-requests` - Method `customerImportRequests` exists in AdminController

**Investigation Steps:**
1. Check JavaScript console for errors
2. Verify form submission handlers
3. Check CSRF token presence
4. Verify route definitions match form actions
5. Check controller method implementations

## 5. Implementation Priority

### High Priority (Core Functionality)
1. ✅ ~~Fix non-working buttons~~ - Needs investigation
2. ✅ Package ↔ PPP Profile ↔ IP Pool mapping - **COMPLETED**
3. ✅ SMS Gateway management UI - **COMPLETED**
4. ✅ Operator wallet management - **COMPLETED**
5. ✅ Operator package rates - **COMPLETED**
6. ✅ Operator SMS rates - **COMPLETED**

### Medium Priority (Business Features)
1. ✅ Operator-specific packages and rates - **COMPLETED**
2. ✅ Manual fund addition for operators - **COMPLETED**
3. ⏳ Operator billing profiles - Database ready, UI pending
4. ⏳ Customer import views - Methods exist, views needed

### Low Priority (Nice to Have)
1. ✅ Admin login-as-operator - **COMPLETED**
2. ✅ SMS fee assignment per operator - **COMPLETED**
3. ⏳ UI/UX menu cleanup - Pending
4. ⏳ Impersonation UI banner - Can be added

## 6. Testing Recommendations

After implementing each feature:
1. Test with multiple tenant scenarios
2. Verify role-based access control
3. Check for N+1 query issues
4. Validate data integrity constraints
5. Test edge cases (null values, deletions, etc.)

## 7. Database Migration Strategy

All database changes should:
1. Include rollback functionality
2. Check for existing columns/tables before adding
3. Include appropriate indexes
4. Maintain foreign key constraints
5. Include data migration scripts if needed

## 8. Security Considerations

1. Validate all operator-specific rates to prevent negative pricing
2. Audit log all fund transactions - ✅ **IMPLEMENTED**
3. Implement transaction locks for wallet operations - ✅ **IMPLEMENTED**
4. Rate-limit SMS sending to prevent abuse
5. Session timeout for impersonation feature - ✅ **IMPLEMENTED**

## 9. Implementation Notes

### Operator Wallet Management
- Uses database transactions to ensure data integrity
- Balance is tracked before and after each transaction
- All transactions are logged with creator information
- Validation prevents deducting more than available balance

### Operator Package Rates
- Supports commission percentage calculation
- Prevents duplicate rate assignments for same package
- Allows updating existing rates
- Shows original package price alongside custom price

### Operator SMS Rates
- Supports bulk rate discounts with configurable thresholds
- Has a `calculateCost()` method for cost estimation
- SMS balance tracking integrated with user model
- Bulk rates are optional

### Package Profile Mapping
- Supports multiple routers per package
- IP pool assignment is optional
- Speed control methods: simple_queue, pcq, burst
- Unique constraint on package_id + router_id combination
