# ISP Solution - Feature Implementation Guide

This document outlines the remaining feature implementations that require business decision input and detailed specifications.

## 1. SMS Gateway Management Module

### Current Status
- SMS gateway configuration exists in database (`sms_gateways` table)
- SMS logs and templates are implemented
- Missing: UI/UX for SMS gateway setup under SMS Management menu

### Implementation Requirements
1. **SMS Gateway Setup Page**
   - Route: `/panel/admin/sms/gateways`
   - Features needed:
     - List all configured SMS gateways
     - Add/Edit/Delete SMS gateway configurations
     - Test gateway connection
     - Set default gateway
     - Configure gateway-specific settings (API keys, URLs, etc.)

2. **Supported Gateway Types**
   - Twilio
   - Nexmo/Vonage
   - Custom HTTP API
   - Local GSM modem

3. **Files to Create/Modify**
   - Controller: `app/Http/Controllers/Panel/SmsGatewayController.php`
   - Views: `resources/views/panels/admin/sms/gateways/`
   - Routes: Add to `routes/web.php` under SMS Management section

## 2. Package ↔ PPP Profile ↔ IP Pool Mapping

### Current Status
- `PackageProfileMapping` model exists
- Packages, Profiles, and IP Pools are separate entities
- Missing: Comprehensive mapping interface and automation

### Implementation Requirements
1. **Mapping Interface**
   - Route: `/panel/admin/packages/{id}/mappings`
   - Features needed:
     - Assign PPP Profiles to Packages
     - Assign IP Pools to Packages
     - Auto-provision settings when customer subscribes
     - Bulk mapping for multiple packages

2. **Database Changes**
   - Consider adding `ip_pool_id` to `package_profile_mappings` table
   - Add validation to ensure mappings are consistent

3. **Automation Logic**
   - When a customer is assigned a package:
     - Automatically assign them to the appropriate PPP profile
     - Allocate an IP from the associated IP pool
     - Create network user with correct credentials

## 3. Operator-Specific Features

### 3.1 Operator-Specific Packages
**Requirements:**
- Operators should have their own package catalog
- Packages can be global (visible to all operators) or operator-specific
- Sub-operators can only see packages assigned to their parent operator

**Database Changes:**
```sql
ALTER TABLE packages ADD COLUMN operator_id BIGINT UNSIGNED NULL;
ALTER TABLE packages ADD COLUMN is_global BOOLEAN DEFAULT true;
ALTER TABLE packages ADD FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE CASCADE;
```

### 3.2 Operator-Specific Rates
**Requirements:**
- Different operators can have different pricing for the same service
- Commission structure for operators
- Discount levels based on operator tier

**Database Changes:**
```sql
CREATE TABLE operator_package_rates (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    operator_id BIGINT UNSIGNED NOT NULL,
    package_id BIGINT UNSIGNED NOT NULL,
    custom_price DECIMAL(10,2) NOT NULL,
    commission_percentage DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    UNIQUE KEY unique_operator_package (operator_id, package_id)
);
```

### 3.3 Operator Billing Profiles & Cycles
**Requirements:**
- Operators can have different billing cycles (monthly, quarterly, annual)
- Custom billing dates (e.g., operator A bills on 1st, operator B on 15th)
- Support for prepaid and postpaid models per operator

**Database Changes:**
```sql
ALTER TABLE users ADD COLUMN billing_cycle VARCHAR(50) DEFAULT 'monthly';
ALTER TABLE users ADD COLUMN billing_day_of_month INT DEFAULT 1;
ALTER TABLE users ADD COLUMN payment_type ENUM('prepaid', 'postpaid') DEFAULT 'postpaid';
```

### 3.4 Manual Fund Addition for Operators
**Requirements:**
- Admins can add/deduct funds from operator wallets
- Fund transaction history
- Low balance notifications

**Database Changes:**
```sql
CREATE TABLE operator_wallet_transactions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    operator_id BIGINT UNSIGNED NOT NULL,
    transaction_type ENUM('credit', 'debit') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    balance_before DECIMAL(10,2) NOT NULL,
    balance_after DECIMAL(10,2) NOT NULL,
    description TEXT,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

ALTER TABLE users ADD COLUMN wallet_balance DECIMAL(10,2) DEFAULT 0;
```

### 3.5 SMS Fee Assignment per Operator
**Requirements:**
- Different SMS costs for different operators
- Bulk SMS package options
- SMS balance tracking

**Database Changes:**
```sql
CREATE TABLE operator_sms_rates (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    operator_id BIGINT UNSIGNED NOT NULL,
    rate_per_sms DECIMAL(10,4) NOT NULL,
    bulk_rate_threshold INT DEFAULT 100,
    bulk_rate_per_sms DECIMAL(10,4) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE CASCADE
);

ALTER TABLE users ADD COLUMN sms_balance INT DEFAULT 0;
```

### 3.6 Admin Login-as-Operator Functionality
**Requirements:**
- Super-admins can impersonate operators
- Session tracking for audit purposes
- Easy switch back to admin account

**Implementation:**
```php
// Add to AdminController
public function loginAsOperator(Request $request, $operatorId)
{
    $operator = User::findOrFail($operatorId);
    
    // Store original admin ID in session
    session(['impersonate_by' => auth()->id()]);
    session(['impersonate_at' => now()]);
    
    // Log audit
    AuditLog::create([
        'admin_id' => auth()->id(),
        'operator_id' => $operatorId,
        'action' => 'login_as_operator',
        'ip_address' => $request->ip(),
    ]);
    
    // Login as operator
    auth()->loginUsingId($operatorId);
    
    return redirect()->route('panel.operator.dashboard')
        ->with('success', 'You are now logged in as ' . $operator->name);
}

public function stopImpersonating()
{
    $adminId = session('impersonate_by');
    
    session()->forget(['impersonate_by', 'impersonate_at']);
    
    auth()->loginUsingId($adminId);
    
    return redirect()->route('panel.admin.dashboard')
        ->with('success', 'You are now logged back in as admin');
}
```

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

**Investigation Steps:**
1. Check JavaScript console for errors
2. Verify form submission handlers
3. Check CSRF token presence
4. Verify route definitions match form actions
5. Check controller method implementations

## 5. Implementation Priority

### High Priority (Core Functionality)
1. Fix non-working buttons
2. Package ↔ PPP Profile ↔ IP Pool mapping
3. SMS Gateway management UI

### Medium Priority (Business Features)
1. Operator-specific packages and rates
2. Manual fund addition for operators
3. Operator billing profiles

### Low Priority (Nice to Have)
1. Admin login-as-operator
2. SMS fee assignment per operator
3. UI/UX menu cleanup

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
2. Audit log all fund transactions
3. Implement transaction locks for wallet operations
4. Rate-limit SMS sending to prevent abuse
5. Session timeout for impersonation feature
