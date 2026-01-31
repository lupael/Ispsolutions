# Reference ISP System - Implementation TODO List

> **Created:** 2026-01-28  
> **Based on:** Newfolder.zip analysis (300+ reference files)  
> **Status:** Phase 1 (HIGH Priority) - 95% Complete  
> **Priority:** Features identified but NOT breaking existing rules

---

## 📋 Executive Summary

This document outlines implementation tasks derived from analyzing a reference ISP billing system. The analysis reviewed 300+ PHP files covering controllers, models, policies, and configuration files.

**Key Principle:** Learn from reference system concepts while maintaining our superior code quality, testing, and architecture.

---

## 🎯 Role Mapping Reference

| Reference System | Our Platform | Level | Equivalent |
|------------------|--------------|-------|------------|
| `group_admin` | `admin` | 20 | ISP Owner / Admin |
| `operator` / `reseller` | `operator` | 30 | Area/Zone Manager |
| `sub_operator` / `sub_reseller` | `sub_operator` | 40 | Local Manager |
| `developer` | `developer` | 0 | System Developer |

---

## 🏗️ Architecture Insights

### Reference System Patterns Observed

1. **Multi-Node Database Architecture**
   - Central DB: Master data (operators, billing profiles, packages)
   - Node DB: Customer data, RADIUS tables (per operator/tenant)
   - Dynamic connection switching based on authenticated user
   
2. **Aggressive Caching Strategy**
   - 5-minute cache for expensive queries (customer counts, billing profiles)
   - Cache warming on model updates
   - Redis-based caching for computed attributes

3. **Computed Attributes (20+ per model)**
   - `overall_status`: Combined payment + service status
   - `customer_count`: Cached package usage statistics
   - `remaining_validity`: Localized validity messages
   - `readable_rate_unit`: Human-friendly speed display

4. **Policy-Based Authorization**
   - Separate policy classes for each major model
   - Granular permission checks at resource level
   - Role-based data isolation

---

## 📊 Feature Comparison Matrix

| Category | Feature | Reference | Current | Priority | Status |
|----------|---------|-----------|---------|----------|--------|
| **Payments** | SMS Payments | ✅ | ✅ | HIGH | ✅ Complete (95%) |
| **Payments** | Auto-Debit | ✅ | ✅ | HIGH | ✅ Complete (95%) |
| **Payments** | Subscription Payments | ✅ | ✅ | HIGH | ✅ Complete (95%) |
| **Payments** | Bkash Tokenization | ✅ Advanced | ✅ Advanced | MEDIUM | ✅ Complete (90%) |
| **Billing** | Daily Billing | ✅ | ✅ | HIGH | ✅ Complete |
| **Billing** | Grace Period Calc | ✅ Advanced | ⚠️ Basic | MEDIUM | 🟡 Enhance |
| **Billing** | Date Formatting | ✅ Advanced | ⚠️ Basic | MEDIUM | 🟡 Enhance |
| **Billing** | Minimum Validity | ✅ | ❌ | LOW | 🔴 Missing |
| **Packages** | Package Hierarchy | ✅ Parent/Child | ⚠️ Basic | MEDIUM | 🟡 Enhance |
| **Packages** | Price Validation | ✅ Min $1 | ❌ | LOW | 🔴 Missing |
| **Packages** | Cached Customer Count | ✅ | ❌ | MEDIUM | 🔴 Missing |
| **Packages** | Validity Conversions | ✅ All Units | ⚠️ Partial | LOW | 🟡 Enhance |
| **Customers** | Overall Status | ✅ Combined | ⚠️ Separate | MEDIUM | 🟡 Enhance |
| **Customers** | Parent/Child Accounts | ✅ | ❌ | LOW | 🔴 Missing |
| **Customers** | Bulk Operations | ✅ | ✅ | HIGH | ✅ Complete |
| **Network** | Auto Pool Import | ✅ | ❌ | MEDIUM | 🔴 Missing |
| **Network** | RADIUS Attr Mgmt | ✅ Granular | ⚠️ Basic | MEDIUM | 🟡 Enhance |
| **Network** | PPPoE Profile Sync | ✅ | ✅ | HIGH | ✅ Complete |
| **I18n** | Multi-Language | ✅ Bengali | ❌ | LOW | 🔴 Missing |
| **I18n** | Localized Dates | ✅ | ⚠️ Basic | LOW | 🟡 Enhance |
| **DB** | PostgreSQL Support | ✅ | ❌ | LOW | 🔴 Missing |
| **DB** | Per-Operator RADIUS | ✅ | ❌ | LOW | 🔴 Missing |

---

## 🚀 Implementation Phases

### ✅ Phase 0: What We Already Do Better

**DO NOT implement these - our system is superior:**

1. ✅ **RADIUS Implementation** - We have complete radcheck/radreply tables
2. ✅ **Device Monitoring** - Advanced performance metrics, aggregation jobs
3. ✅ **Router Integration** - Superior MikroTik API integration with queue management
4. ✅ **Code Quality** - Type hints, PHPDoc, PHPStan, comprehensive testing
5. ✅ **Documentation** - Extensive guides, API docs, role specifications
6. ✅ **Testing** - PHPUnit tests, feature tests, integration tests
7. ✅ **Billing Features** - Comprehensive invoice system with PDF generation
8. ✅ **Payment Gateways** - Multi-gateway support (Stripe, Bkash, Nagad, SSLCommerz, etc.)

---

### ✅ Phase 1: HIGH PRIORITY - Payment & Billing Enhancements (95% Complete)

#### 1.1 SMS Payment Integration ✅ (95% Complete)
**Reference Files:** `AdvanceSmsPaymentController.php`, `SmsPaymentController.php`, `bKashTokenizedSmsPaymentController.php`

**Features to Implement:**
- [x] SMS balance tracking per operator
- [x] SMS payment gateway integration
- [x] SMS payment history and reconciliation
- [x] SMS credit purchase workflow
- [x] SMS payment notifications
- [x] Advance SMS payment (prepaid SMS credits)

**UI Requirements:**
- [x] SMS balance widget in operator dashboard
- [x] SMS payment history page
- [x] SMS credit purchase form
- [x] Low balance alerts and notifications
- [x] SMS payment receipt generation

**Database Changes:**
```sql
-- New tables needed
CREATE TABLE sms_payments (
    id BIGINT UNSIGNED PRIMARY KEY,
    operator_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed'),
    created_at TIMESTAMP,
    FOREIGN KEY (operator_id) REFERENCES users(id)
);

CREATE TABLE sms_balance_history (
    id BIGINT UNSIGNED PRIMARY KEY,
    operator_id BIGINT UNSIGNED NOT NULL,
    transaction_type ENUM('purchase', 'usage', 'refund'),
    amount INT NOT NULL,
    balance_after INT NOT NULL,
    created_at TIMESTAMP,
    FOREIGN KEY (operator_id) REFERENCES users(id)
);
```

**Models to Create:**
- `app/Models/SmsPayment.php`
- `app/Models/SmsBalanceHistory.php`
- `app/Models/SmsBill.php`

**Controllers to Create:**
- `app/Http/Controllers/Admin/SmsPaymentController.php`
- `app/Http/Controllers/Operator/SmsBalanceController.php`

**Middleware:**
- `EnsureSmsPayment` - Ensure operator has SMS credits

**Testing:**
- [x] Unit tests for SMS balance calculations
- [x] Feature tests for SMS payment workflow
- [x] Integration tests with payment gateways

---

#### 1.2 Auto-Debit System ✅ (95% Complete)
**Reference Files:** `AutoDebitController.php`, `AutomaticallySuspendCustomers.php`

**Features to Implement:**
- [x] Auto-debit configuration per customer
- [x] Scheduled auto-debit jobs (daily/monthly)
- [x] Auto-debit retry logic (3 attempts)
- [x] Auto-debit success/failure notifications
- [x] Auto-suspend on failed auto-debit
- [x] Auto-debit history tracking

**UI Requirements:**
- [x] Auto-debit enable/disable toggle on customer edit
- [x] Auto-debit history page
- [x] Failed auto-debit report
- [x] Auto-debit settings page (retry attempts, grace period)
- [x] Bulk enable/disable auto-debit

**Database Changes:**
```sql
-- Add columns to customers table
ALTER TABLE users ADD COLUMN auto_debit_enabled BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN auto_debit_payment_method VARCHAR(50);
ALTER TABLE users ADD COLUMN auto_debit_last_attempt TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN auto_debit_retry_count INT DEFAULT 0;

-- New table for auto-debit history
CREATE TABLE auto_debit_history (
    id BIGINT UNSIGNED PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    bill_id BIGINT UNSIGNED,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('success', 'failed', 'pending'),
    failure_reason TEXT,
    retry_count INT DEFAULT 0,
    created_at TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (bill_id) REFERENCES subscription_bills(id)
);
```

**Jobs to Create:**
- `app/Jobs/ProcessAutoDebitJob.php` - Process auto-debit for a customer
- `app/Jobs/AutoDebitRetryJob.php` - Retry failed auto-debits
- `app/Jobs/AutoSuspendFailedAutoDebitJob.php` - Suspend customers after failed attempts

**Commands to Create:**
- `app/Console/Commands/ProcessDailyAutoDebitsCommand.php`

**Testing:**
- [x] Unit tests for auto-debit logic
- [x] Feature tests for auto-debit workflow
- [x] Job tests for retry logic

---

#### 1.3 Subscription Payment Processing ✅ (95% Complete)
**Reference Files:** `bKashTokenizedSubscriptionPaymentController.php`, `SubscriptionFeeCalculator.php`

**Features to Implement:**
- [x] Subscription payment workflow separate from customer payments
- [x] Operator subscription billing (platform subscription fees)
- [x] Subscription payment gateway integration
- [x] Subscription payment reminders
- [x] Subscription payment history
- [x] Subscription renewal automation

**UI Requirements:**
- [x] Operator subscription payment page
- [x] Subscription payment history
- [x] Subscription plan selection
- [x] Payment method selection for subscriptions
- [x] Subscription invoice viewing/download

**Database Changes:**
```sql
-- New table for operator subscriptions
CREATE TABLE operator_subscriptions (
    id BIGINT UNSIGNED PRIMARY KEY,
    operator_id BIGINT UNSIGNED NOT NULL,
    subscription_plan_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled'),
    created_at TIMESTAMP,
    FOREIGN KEY (operator_id) REFERENCES users(id)
);

CREATE TABLE subscription_plans (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    billing_cycle ENUM('monthly', 'quarterly', 'yearly'),
    features JSON,
    created_at TIMESTAMP
);

CREATE TABLE subscription_payments (
    id BIGINT UNSIGNED PRIMARY KEY,
    operator_subscription_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed'),
    created_at TIMESTAMP,
    FOREIGN KEY (operator_subscription_id) REFERENCES operator_subscriptions(id)
);
```

**Models to Create:**
- `app/Models/OperatorSubscription.php`
- `app/Models/SubscriptionPlan.php` (Note: Different from customer SubscriptionPlan)
- `app/Models/SubscriptionPayment.php`

**Controllers to Create:**
- `app/Http/Controllers/Operator/SubscriptionPaymentController.php`
- `app/Http/Controllers/Admin/SubscriptionPlanController.php`

**Jobs to Create:**
- `app/Jobs/ProcessSubscriptionRenewalJob.php`
- `app/Jobs/NotifySubscriptionExpiryJob.php`

**Testing:**
- [x] Unit tests for subscription calculations
- [x] Feature tests for subscription payment workflow
- [x] Integration tests with payment gateways

---

#### 1.4 Bkash Tokenization Enhancement ✅ (90% Complete)
**Reference Files:** `bKashTokenizedAbstractController.php`, `bKashTokenizedCustomerPaymentController.php`

**Features to Implement:**
- [x] Bkash agreement creation and management
- [x] Token storage per customer/operator
- [x] Token-based automatic payments
- [x] Token revocation handling
- [x] Agreement status tracking

**UI Requirements:**
- [x] Bkash agreement creation form
- [x] Saved payment methods display
- [x] Token management page
- [ ] One-click payment with saved token
- [ ] Agreement revocation option

**Database Changes:**
```sql
CREATE TABLE bkash_agreements (
    id BIGINT UNSIGNED PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    user_type ENUM('customer', 'operator'),
    agreement_id VARCHAR(100) NOT NULL,
    payment_id VARCHAR(100),
    agreement_status ENUM('pending', 'active', 'cancelled'),
    created_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE bkash_tokens (
    id BIGINT UNSIGNED PRIMARY KEY,
    agreement_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP,
    created_at TIMESTAMP,
    FOREIGN KEY (agreement_id) REFERENCES bkash_agreements(id)
);
```

**Models to Create:**
- `app/Models/BkashAgreement.php`
- `app/Models/BkashToken.php`

**Services to Enhance:**
- `app/Services/PaymentGateways/BkashService.php` - Add tokenization methods

**Controllers to Create:**
- `app/Http/Controllers/Customer/BkashAgreementController.php`
- `app/Http/Controllers/Operator/BkashAgreementController.php`

**Testing:**
- [x] Unit tests for token management
- [x] Feature tests for agreement workflow
- [x] Mock Bkash API responses

---

### 🟡 Phase 2: MEDIUM PRIORITY - Performance & UX Enhancements

#### 2.1 Advanced Caching Implementation
**Reference Pattern:** 5-minute cache for expensive queries

**Features to Implement:**
- [ ] Cache customer counts per package (5 min TTL)
- [ ] Cache billing profile details (10 min TTL)
- [ ] Cache operator statistics (1 min TTL)
- [ ] Cache device status (30 sec TTL)
- [ ] Implement cache warming strategy
- [ ] Add cache invalidation on updates

**Implementation Approach:**
```php
// Example: Add to SubscriptionPlan model
public function customerCount(): Attribute
{
    return Attribute::make(
        get: function ($value, $attributes) {
            $key = "subscription_plan_{$attributes['id']}_customer_count";
            return Cache::remember($key, now()->addMinutes(5), function () use ($attributes) {
                return DB::table('users')
                    ->where('subscription_plan_id', $attributes['id'])
                    ->where('operator_level', 100)
                    ->count();
            });
        },
    );
}
```

**Files to Modify:**
- `app/Models/SubscriptionPlan.php` - Add cached customer count
- `app/Models/BillingProfile.php` - Add cached operator count
- `app/Models/Package.php` - Add cached usage statistics
- `app/Services/CustomerCacheService.php` - Enhance with more caching strategies

**Testing:**
- [ ] Cache hit/miss tests
- [ ] Cache invalidation tests
- [ ] Performance benchmarks (before/after)

---

#### 2.2 Enhanced Date Formatting
**Reference Pattern:** "21st day of each month", "1st/2nd/3rd day"

**Features to Implement:**
- [ ] Ordinal date formatting (1st, 2nd, 3rd, 21st, etc.)
- [ ] Natural language due dates
- [ ] Enhanced grace period display
- [ ] Remaining validity in human-readable format
- [ ] Bengali/local language date support

**UI Requirements:**
- [ ] Display "Payment due on 21st of each month" instead of "21"
- [ ] Show "Grace period: 3 days after due date"
- [ ] Display "Expires in 5 days" instead of date
- [ ] Localized date formats based on user preference

**Implementation Approach:**
```php
// Add to BillingProfile model
public function formattedPaymentDate(): Attribute
{
    return Attribute::make(
        get: function ($value, $attributes) {
            $day = $attributes['payment_date'];
            $suffix = match(true) {
                $day % 10 === 1 && $day !== 11 => 'st',
                $day % 10 === 2 && $day !== 12 => 'nd',
                $day % 10 === 3 && $day !== 13 => 'rd',
                default => 'th'
            };
            return "{$day}{$suffix} day of each month";
        },
    );
}
```

**Files to Modify:**
- `app/Models/BillingProfile.php` - Add formatted date attributes
- `app/Models/SubscriptionBill.php` - Add remaining days in human format
- `resources/views/admin/billing-profiles/*.blade.php` - Use formatted dates

**Testing:**
- [ ] Unit tests for date formatting
- [ ] Feature tests for different day numbers (1, 2, 3, 11, 12, 13, 21, 22, 23, etc.)

---

#### 2.3 Customer Overall Status
**Reference Pattern:** Combined status like "PAID_ACTIVE", "BILLED_SUSPENDED"

**Features to Implement:**
- [ ] Add `overall_status` computed attribute
- [ ] Combine payment_status + service_status
- [ ] Color-coded status badges
- [ ] Status-based filtering
- [ ] Status history tracking

**UI Requirements:**
- [ ] Status badge with appropriate colors:
  - 🟢 PAID_ACTIVE (green)
  - 🟡 BILLED_ACTIVE (yellow)
  - 🟠 PAID_SUSPENDED (orange)
  - 🔴 BILLED_SUSPENDED (red)
  - ⚫ DISABLED (gray)
- [ ] Quick filter buttons for each status
- [ ] Status change reason modal

**Implementation Approach:**
```php
// Add to Customer/User model
public function overallStatus(): Attribute
{
    return Attribute::make(
        get: function ($value, $attributes) {
            $paymentStatus = $attributes['payment_status'] ?? 'unpaid';
            $serviceStatus = $attributes['service_status'] ?? 'inactive';
            
            if ($serviceStatus === 'disabled') {
                return 'DISABLED';
            }
            
            $payment = strtoupper($paymentStatus === 'paid' ? 'paid' : 'billed');
            $service = strtoupper($serviceStatus);
            
            return "{$payment}_{$service}";
        },
    );
}
```

**Database Changes:**
```sql
-- Optional: Store as actual column for faster queries
ALTER TABLE users ADD COLUMN overall_status VARCHAR(50) GENERATED ALWAYS AS (
    CASE 
        WHEN service_status = 'disabled' THEN 'DISABLED'
        ELSE CONCAT(
            CASE WHEN payment_status = 'paid' THEN 'PAID' ELSE 'BILLED' END,
            '_',
            UPPER(service_status)
        )
    END
) VIRTUAL;

-- Add index for filtering
CREATE INDEX idx_users_overall_status ON users(overall_status);
```

**Files to Modify:**
- `app/Models/User.php` or create `app/Models/Customer.php` - Add overall_status attribute
- `app/Http/Controllers/Admin/CustomerController.php` - Add status filter
- `resources/views/admin/customers/index.blade.php` - Add status badges and filters

**Testing:**
- [ ] Unit tests for all status combinations
- [ ] Feature tests for status-based filtering

---

#### 2.4 Package Hierarchy Enhancement
**Reference Pattern:** Parent/child package relationships

**Features to Implement:**
- [ ] Parent package selection
- [ ] Package inheritance (inherit FUP, limits from parent)
- [ ] Package upgrade paths
- [ ] Package downgrade restrictions
- [ ] Visual package tree display

**UI Requirements:**
- [ ] Package tree view with parent-child relationships
- [ ] Parent package selector in package create/edit form
- [ ] Upgrade path configuration
- [ ] Visual package comparison chart
- [ ] Package recommendation system

**Database Changes:**
```sql
ALTER TABLE subscription_plans ADD COLUMN parent_id BIGINT UNSIGNED NULL;
ALTER TABLE subscription_plans ADD COLUMN hierarchy_level INT DEFAULT 0;
ALTER TABLE subscription_plans ADD COLUMN inherit_from_parent BOOLEAN DEFAULT FALSE;
ALTER TABLE subscription_plans ADD FOREIGN KEY (parent_id) REFERENCES subscription_plans(id) ON DELETE SET NULL;

CREATE INDEX idx_subscription_plans_parent ON subscription_plans(parent_id);
```

**Files to Modify:**
- `app/Models/SubscriptionPlan.php` - Add parent/child relationships
- `app/Http/Controllers/Admin/SubscriptionPlanController.php` - Handle hierarchy
- `database/migrations/*_add_hierarchy_to_subscription_plans.php` - Migration

**Testing:**
- [ ] Unit tests for package inheritance
- [ ] Feature tests for package upgrade paths
- [ ] Edge case tests (circular references, deep nesting)

---

#### 2.5 Automatic MikroTik Pool Import
**Reference Files:** `MikroTikDbSyncController.php`, `Mikmon2RadiusCommand.php`

**Features to Implement:**
- [ ] Import IP pools from MikroTik to database
- [ ] Import PPPoE profiles from MikroTik
- [ ] Import PPP secrets (users) from MikroTik
- [ ] Sync MikroTik queues to database
- [ ] Schedule automatic sync jobs
- [ ] Conflict resolution (database vs router)

**UI Requirements:**
- [ ] "Import from MikroTik" button on IP pool page
- [ ] Import progress indicator
- [ ] Import conflict resolution interface
- [ ] Import history and logs
- [ ] Selective import (choose what to import)

**Implementation Approach:**
```php
// New command
class ImportMikroTikPoolsCommand extends Command
{
    protected $signature = 'mikrotik:import-pools {router_id}';
    
    public function handle()
    {
        $router = MikroTikRouter::findOrFail($this->argument('router_id'));
        $client = $router->getConnection();
        
        // Fetch pools from router
        $pools = $client->query('/ip/pool/print')->read();
        
        foreach ($pools as $pool) {
            MikroTikIpPool::updateOrCreate(
                ['router_id' => $router->id, 'name' => $pool['name']],
                [
                    'ranges' => $pool['ranges'],
                    'next_pool' => $pool['next-pool'] ?? null,
                ]
            );
        }
        
        $this->info('Import completed!');
    }
}
```

**Controllers to Create:**
- `app/Http/Controllers/Admin/MikroTikSyncController.php`

**Jobs to Create:**
- `app/Jobs/ImportMikroTikDataJob.php`
- `app/Jobs/SyncMikroTikPoolsJob.php`

**Commands to Create:**
- `app/Console/Commands/ImportMikroTikPoolsCommand.php`
- `app/Console/Commands/ImportMikroTikProfilesCommand.php`
- `app/Console/Commands/ImportMikroTikSecretsCommand.php`

**Testing:**
- [ ] Unit tests with mock MikroTik API responses
- [ ] Feature tests for import workflow
- [ ] Conflict resolution tests

---

#### 2.6 RADIUS Attributes Management UI
**Reference Files:** `PPPoECustomersRadAttributesController.php`, `HotspotCustomersRadAttributesController.php`

**Features to Implement:**
- [ ] UI for managing custom RADIUS attributes per customer
- [ ] Attribute templates for common scenarios
- [ ] Bulk attribute assignment
- [ ] Attribute validation
- [ ] Attribute history tracking

**UI Requirements:**
- [ ] RADIUS attributes tab in customer details
- [ ] Add/edit/delete attribute rows
- [ ] Attribute type validation (Reply vs Check)
- [ ] Attribute template selector
- [ ] Copy attributes from another customer

**Database Changes:**
```sql
-- Add to radcheck/radreply tables if not exists
ALTER TABLE radcheck ADD COLUMN created_by BIGINT UNSIGNED NULL;
ALTER TABLE radreply ADD COLUMN created_by BIGINT UNSIGNED NULL;
ALTER TABLE radcheck ADD FOREIGN KEY (created_by) REFERENCES users(id);
ALTER TABLE radreply ADD FOREIGN KEY (created_by) REFERENCES users(id);

CREATE TABLE radius_attribute_templates (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    attribute_type ENUM('check', 'reply'),
    attributes JSON NOT NULL,
    created_at TIMESTAMP
);
```

**Files to Create:**
- `app/Models/RadiusAttributeTemplate.php`
- `app/Http/Controllers/Admin/RadiusAttributeController.php`
- `app/Http/Controllers/Admin/RadiusAttributeTemplateController.php`

**Views to Create:**
- `resources/views/admin/radius-attributes/index.blade.php`
- `resources/views/admin/radius-attributes/create.blade.php`
- `resources/views/admin/radius-attributes/templates.blade.php`

**Testing:**
- [ ] Unit tests for attribute validation
- [ ] Feature tests for CRUD operations
- [ ] Integration tests with RADIUS server

---

### 🔵 Phase 3: LOW PRIORITY - Nice-to-Have Features

#### 3.1 Multi-Language Support (i18n)
**Reference Pattern:** Bengali language support

**Features to Implement:**
- [ ] Laravel localization setup
- [ ] Language switcher in UI
- [ ] Translate all UI strings
- [ ] Language-specific date formats
- [ ] RTL support for Arabic/Urdu
- [ ] Multi-language email templates

**UI Requirements:**
- [ ] Language selector in top navigation
- [ ] User language preference in profile
- [ ] Admin language management page
- [ ] Translation management interface

**Files to Create:**
- `lang/en/*.php` - English translations
- `lang/bn/*.php` - Bengali translations
- `lang/ar/*.php` - Arabic translations
- `config/languages.php` - Supported languages configuration

**Middleware to Create:**
- `app/Http/Middleware/SetLocale.php` - Set user's preferred language

**Testing:**
- [ ] Translation completeness tests
- [ ] RTL layout tests
- [ ] Language switching tests

---

#### 3.2 Package Price Validation
**Reference Pattern:** Minimum price of $1, fallback to default

**Features to Implement:**
- [ ] Minimum price validation rule
- [ ] Maximum price limits per role
- [ ] Price range recommendations
- [ ] Warning for suspiciously low prices
- [ ] Price history tracking

**Implementation Approach:**
```php
// In SubscriptionPlan model
protected static function booted()
{
    static::saving(function ($plan) {
        if ($plan->price < 1) {
            throw new \Exception('Package price must be at least $1');
        }
    });
}

// Or in Form Request
public function rules()
{
    return [
        'price' => ['required', 'numeric', 'min:1', 'max:10000'],
    ];
}
```

**Files to Modify:**
- `app/Http/Requests/SubscriptionPlanRequest.php` - Add validation rules
- `app/Models/SubscriptionPlan.php` - Add model event validation
- `app/Models/Package.php` - Add validation

**Testing:**
- [ ] Validation tests for price limits
- [ ] Edge case tests (0, negative, very high)

---

#### 3.3 Parent/Child Customer Accounts
**Reference Pattern:** Reseller customer accounts

**Features to Implement:**
- [ ] Customer hierarchy (parent-child relationships)
- [ ] Billing roll-up (child bills aggregate to parent)
- [ ] Credit distribution from parent to children
- [ ] Parent dashboard showing child accounts
- [ ] Transfer child to another parent

**UI Requirements:**
- [ ] Child accounts tab in customer details
- [ ] Add child account form
- [ ] Parent account selector
- [ ] Consolidated billing view
- [ ] Credit distribution interface

**Database Changes:**
```sql
ALTER TABLE users ADD COLUMN parent_customer_id BIGINT UNSIGNED NULL;
ALTER TABLE users ADD COLUMN is_reseller BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD FOREIGN KEY (parent_customer_id) REFERENCES users(id) ON DELETE SET NULL;

CREATE INDEX idx_users_parent_customer ON users(parent_customer_id);
```

**Files to Modify:**
- `app/Models/User.php` - Add parent/child relationships
- `app/Http/Controllers/Admin/CustomerController.php` - Handle child accounts

**Testing:**
- [ ] Unit tests for hierarchy logic
- [ ] Feature tests for billing roll-up
- [ ] Edge case tests (circular references)

---

#### 3.4 Validity Unit Conversions
**Reference Pattern:** Convert between days/hours/minutes

**Features to Implement:**
- [ ] Convert validity between units (day ↔ hour ↔ minute)
- [ ] Display in multiple formats
- [ ] Auto-convert based on magnitude
- [ ] Validity calculator tool

**Implementation Approach:**
```php
// Add to SubscriptionPlan model
public function totalMinutes(): Attribute
{
    return Attribute::make(
        get: function ($value, $attributes) {
            return match($attributes['validity_unit']) {
                'day' => $attributes['validity'] * 24 * 60,
                'hour' => $attributes['validity'] * 60,
                'minute' => $attributes['validity'],
                default => 0,
            };
        },
    );
}

public function validityInDays(): Attribute
{
    return Attribute::make(
        get: fn() => round($this->total_minutes / (24 * 60), 2)
    );
}
```

**Files to Modify:**
- `app/Models/SubscriptionPlan.php` - Add conversion methods
- `app/Models/Package.php` - Add conversion methods

**Testing:**
- [ ] Unit tests for all conversions
- [ ] Edge case tests (fractional days, very large numbers)

---

#### 3.5 PostgreSQL Support for RADIUS
**Reference Pattern:** pgsql_* models for PostgreSQL

**Features to Implement:**
- [ ] PostgreSQL database driver support
- [ ] RADIUS table migrations for PostgreSQL
- [ ] Connection switcher (MySQL ↔ PostgreSQL)
- [ ] Data migration tools (MySQL → PostgreSQL)
- [ ] Performance comparison benchmarks

**Implementation Approach:**
```php
// Add to config/database.php
'radius_pgsql' => [
    'driver' => 'pgsql',
    'host' => env('RADIUS_PGSQL_HOST', '127.0.0.1'),
    'port' => env('RADIUS_PGSQL_PORT', '5432'),
    'database' => env('RADIUS_PGSQL_DATABASE', 'radius'),
    'username' => env('RADIUS_PGSQL_USERNAME', 'radius'),
    'password' => env('RADIUS_PGSQL_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
],
```

**Files to Create:**
- `database/migrations/*_create_radius_tables_postgresql.php`
- `app/Console/Commands/MigrateRadiusToPostgresCommand.php`

**Documentation to Update:**
- Add PostgreSQL setup guide
- Update RADIUS configuration docs
- Add performance comparison

**Testing:**
- [ ] Migration tests (MySQL → PostgreSQL)
- [ ] Feature tests on both databases
- [ ] Performance benchmarks

---

## 📝 Implementation Best Practices

### Code Quality Standards (DO NOT COMPROMISE)

1. ✅ **Maintain Type Hints** - All methods must have parameter and return type hints
2. ✅ **PHPDoc Blocks** - Required for all classes and public methods
3. ✅ **PHPStan Level 5** - All code must pass static analysis
4. ✅ **Unit Tests** - 80%+ coverage for business logic
5. ✅ **Feature Tests** - Critical user flows must have tests
6. ✅ **Form Requests** - Use for validation, not controller validation
7. ✅ **Service Classes** - Complex logic goes in services, not controllers
8. ✅ **Policies** - Authorization logic in policy classes
9. ✅ **Configuration** - No hardcoded values, use config files
10. ✅ **Constants** - Use constants for magic strings/numbers

### UI Development Guidelines

1. 🎨 **Tailwind CSS** - Use Tailwind utility classes, avoid custom CSS
2. 🎨 **Blade Components** - Create reusable components
3. 🎨 **Consistent Layout** - Follow existing panel layout patterns
4. 🎨 **Responsive Design** - Mobile-first approach
5. 🎨 **Accessibility** - ARIA labels, keyboard navigation
6. 🎨 **Loading States** - Show spinners during async operations
7. 🎨 **Error Messages** - User-friendly error displays
8. 🎨 **Success Feedback** - Toast notifications for actions
9. 🎨 **Icons** - Use Heroicons (already in project)
10. 🎨 **Color Coding** - Use consistent colors for status (green=success, red=error, etc.)

### Database Migration Guidelines

1. 🗃️ **Reversible Migrations** - Always include down() method
2. 🗃️ **Foreign Keys** - Use proper constraints with ON DELETE actions
3. 🗃️ **Indexes** - Add indexes for frequently queried columns
4. 🗃️ **Default Values** - Set sensible defaults
5. 🗃️ **NOT NULL** - Use NOT NULL where appropriate
6. 🗃️ **Timestamps** - Include created_at and updated_at
7. 🗃️ **Soft Deletes** - Use for user-facing data
8. 🗃️ **JSON Columns** - For flexible data structures
9. 🗃️ **Enums** - Use for fixed value sets
10. 🗃️ **Testing** - Test migrations up and down

### Security Guidelines

1. 🔒 **Authorization** - Check permissions in controllers and policies
2. 🔒 **Validation** - Validate all user input
3. 🔒 **SQL Injection** - Use query builder/Eloquent, not raw SQL
4. 🔒 **XSS Protection** - Escape output with {{ }} not {!! !!}
5. 🔒 **CSRF Protection** - Include @csrf in all forms
6. 🔒 **Mass Assignment** - Define $fillable or $guarded
7. 🔒 **Encryption** - Encrypt sensitive data (payment tokens, etc.)
8. 🔒 **API Keys** - Store in .env, never commit
9. 🔒 **Password Hashing** - Use Hash::make(), never store plain
10. 🔒 **Rate Limiting** - Apply to sensitive endpoints

---

## 🎯 Prioritization Matrix

| Priority | Effort | Impact | Start Date | Target Completion |
|----------|--------|--------|------------|-------------------|
| HIGH | High | High | Week 1 | 4-6 weeks |
| MEDIUM | Medium | High | Week 7 | 4-6 weeks |
| LOW | Low | Medium | Week 13 | 2-4 weeks |

### Quick Win Features (Low Effort, High Impact)
1. ✅ Enhanced date formatting (2 days)
2. ✅ Customer overall status (3 days)
3. ✅ Package price validation (1 day)
4. ✅ Cached customer counts (2 days)

### Strategic Features (High Effort, High Impact)
1. 🔴 SMS Payment Integration (2-3 weeks)
2. 🔴 Auto-Debit System (2-3 weeks)
3. 🔴 Subscription Payments (2 weeks)
4. 🟡 Bkash Tokenization (1-2 weeks)

---

## 📊 Success Metrics

### Performance Metrics
- [ ] Page load time < 2 seconds (95th percentile)
- [ ] Database query time < 100ms average
- [ ] Cache hit rate > 80%
- [ ] Background job processing < 5 minutes

### Business Metrics
- [ ] Customer satisfaction score > 4.5/5
- [ ] Support ticket reduction by 30%
- [ ] Payment success rate > 95%
- [ ] Auto-debit success rate > 90%

### Technical Metrics
- [ ] PHPStan level 5 compliance: 100%
- [ ] Test coverage > 80%
- [ ] Zero critical security vulnerabilities
- [ ] Documentation completeness > 95%

---

## 🚫 What NOT to Implement

### Features We Decided Against

1. ❌ **Node/Central Database Split**
   - **Reason:** Adds significant complexity without clear benefit for most ISPs
   - **Alternative:** Current single-tenant architecture is simpler and sufficient

2. ❌ **Per-Operator RADIUS Database**
   - **Reason:** Single RADIUS database works for 99% of use cases
   - **Alternative:** Use proper data scoping and indexes

3. ❌ **Custom ORM/Query Builder**
   - **Reason:** Laravel's Eloquent is superior
   - **Alternative:** Stick with Eloquent

4. ❌ **Simplify Device Monitoring**
   - **Reason:** Our current implementation is more advanced
   - **Alternative:** Keep existing advanced monitoring

5. ❌ **Remove RADIUS Features**
   - **Reason:** Our RADIUS implementation is more complete
   - **Alternative:** Keep and enhance existing features

---

## 📚 Documentation Requirements

### New Documentation to Create

1. 📖 **SMS Payment Integration Guide**
   - Setup instructions
   - Configuration options
   - Payment workflow diagrams
   - Troubleshooting guide

2. 📖 **Auto-Debit Implementation Guide**
   - Configuration options
   - Job scheduling setup
   - Retry logic explanation
   - Best practices

3. 📖 **Multi-Language Support Guide**
   - Translation workflow
   - Adding new languages
   - RTL support guidelines
   - Translation testing

4. 📖 **API Documentation Updates**
   - New endpoints for SMS payments
   - Auto-debit API endpoints
   - Subscription payment APIs
   - Webhook documentation

### Documentation to Update

1. 📝 **README.md** - Add new features
2. 📝 **FEATURE_IMPLEMENTATION_STATUS.md** - Update status
3. 📝 **API.md** - Add new endpoints
4. 📝 **ROLES_AND_PERMISSIONS.md** - New permissions
5. 📝 **INSTALLATION.md** - New setup steps

---

## 🔄 Migration Strategy

### For Production Deployments

1. **Phased Rollout**
   - Phase 1: Non-breaking enhancements (caching, formatting)
   - Phase 2: New features with feature flags (SMS payments, auto-debit)
   - Phase 3: Database changes with backward compatibility

2. **Feature Flags**
   ```php
   // Use Laravel Pennant for feature flags
   if (Feature::active('sms-payments')) {
       // SMS payment feature
   }
   ```

3. **Database Migrations**
   - Run during maintenance window
   - Test on staging first
   - Have rollback plan ready
   - Keep old columns during transition period

4. **Backward Compatibility**
   - Maintain API v1 while adding v2
   - Deprecate old features gradually
   - Provide migration guides

---

## 👥 Team Assignments (Recommended)

### Backend Team
- **Lead:** SMS Payment Integration, Auto-Debit System
- **Developer 1:** Subscription Payments, Bkash Tokenization
- **Developer 2:** Caching Implementation, RADIUS Attributes UI

### Frontend Team
- **Lead:** UI components for payment features
- **Developer 1:** Customer status badges, date formatting
- **Developer 2:** Multi-language support, accessibility

### DevOps Team
- **Lead:** Job scheduling, queue management
- **Engineer:** Performance monitoring, cache optimization

### QA Team
- **Lead:** Feature testing, integration testing
- **Tester 1:** Payment gateway testing
- **Tester 2:** Auto-debit testing, edge cases

---

## 📅 Timeline Estimate

### Week 1-4: High Priority Phase 1
- SMS Payment Integration
- Database setup and models
- Basic UI implementation
- Unit tests

### Week 5-8: High Priority Phase 2
- Auto-Debit System
- Job scheduling
- Retry logic
- Integration tests

### Week 9-12: High Priority Phase 3
- Subscription Payments
- Bkash Tokenization
- Complete testing
- Documentation

### Week 13-16: Medium Priority
- Caching implementation
- Date formatting enhancements
- Customer overall status
- Package hierarchy

### Week 17-20: Medium Priority Phase 2
- MikroTik auto-import
- RADIUS attributes UI
- Testing and bug fixes
- Documentation updates

### Week 21-24: Low Priority Features
- Multi-language support
- Price validation
- Parent/child accounts
- Validity conversions

---

## 🎓 Learning Resources

### Recommended Reading
1. Laravel Payment Gateway Integration Best Practices
2. Laravel Queue and Job Processing
3. Laravel Cache Strategies
4. FreeRADIUS Documentation
5. MikroTik API Documentation

### Internal References
1. `REFERENCE_ANALYSIS_SUMMARY.md` - Detailed comparison
2. `FEATURE_IMPLEMENTATION_STATUS.md` - Current features
3. `ROLES_AND_PERMISSIONS.md` - Authorization guide
4. `API.md` - API reference
5. `TESTING.md` - Testing guidelines

---

## 📞 Support & Questions

For questions or clarifications on any implementation:

1. Check existing documentation first
2. Review reference files in `/tmp/reference_system`
3. Consult with team lead
4. Create GitHub issue for tracking
5. Update this document with answers

---

## ✅ Sign-Off Checklist

Before marking any feature as complete:

- [ ] Code passes PHPStan level 5
- [ ] Unit tests written and passing (80%+ coverage)
- [ ] Feature tests written and passing
- [ ] Documentation updated
- [ ] API documentation updated (if applicable)
- [ ] Migration tested (up and down)
- [ ] UI responsive on mobile/tablet/desktop
- [ ] Accessibility tested (keyboard navigation, screen readers)
- [ ] Security review completed
- [ ] Performance benchmarks meet targets
- [ ] Code review approved by 2+ developers
- [ ] QA testing completed
- [ ] Staging deployment successful
- [ ] Release notes prepared

---

## 📝 Version History

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 1.0 | 2026-01-28 | Initial document created from reference system analysis | Copilot Agent |

---

## 🏁 Conclusion

This TODO list provides a comprehensive roadmap for implementing features inspired by the reference ISP system while maintaining the superior code quality, testing, and architecture of the current platform.

**Key Principles:**
1. ✅ Don't break existing features
2. ✅ Maintain code quality standards
3. ✅ Test everything thoroughly
4. ✅ Document all changes
5. ✅ Prioritize user experience

**Remember:** The reference system provides good ideas, but our implementation should be better - cleaner code, better tests, superior documentation, and excellent user experience.

---

**Next Steps:**
1. Review and approve this document
2. Create GitHub issues for Phase 1 tasks
3. Set up project board for tracking
4. Schedule kickoff meeting
5. Begin implementation!
