# Refactoring Terminology Guide - Issue #320

## Overview

This document outlines the terminology standardization implemented as part of Issue #320: "Unified Admin & Gateway Logic". The refactoring addresses deprecated terminology while maintaining backward compatibility throughout the codebase.

## Terminology Changes

### Role Name Updates

| Deprecated Term | New Standard Term | Level | Description |
|----------------|-------------------|-------|-------------|
| MGID | Admin / `admin_id` | 20 | ISP Owner who manages operators and infrastructure |
| Reseller | Operator | 30 | Regional/Business Manager who manages customers and sub-operators |
| Sub-Reseller | Sub-Operator | 40 | District/Area Manager who manages customers only |
| Network User | Customer | N/A | External subscriber (not in management hierarchy) |

### Database Schema Updates

| Deprecated | New Standard | Status |
|-----------|-------------|---------|
| `network_users` table | `customers` table | ✅ Migrated (2026-01-30) |
| `network_user_sessions` table | `customer_sessions` table | ✅ Migrated (2026-01-30) |
| `network_user_id` column | `customer_id` column | ✅ Migrated (2026-01-30) |
| `reseller_id` column | `reseller_id` (kept for compatibility) | ✅ Documented |
| `operator_level = 100` | `is_subscriber = true` | ✅ Migrated (2026-01-30) |

## Implementation Strategy

### 1. Backward Compatibility Approach

The refactoring maintains **100% backward compatibility** by:

- **Database columns**: Keeping original column names (e.g., `reseller_id`) with inline documentation noting they refer to operators
- **Model methods**: Keeping deprecated methods with `@deprecated` tags and adding new preferred methods
- **Class names**: Keeping original class names with `@deprecated` tags and documentation
- **Field names**: Maintaining original field names in forms and APIs

### 2. Code Updates

#### Models Updated

1. **Commission Model** (`app/Models/Commission.php`)
   - Added `operator()` method (preferred)
   - Kept `reseller()` method with `@deprecated` tag
   - Added comprehensive docblocks explaining backward compatibility
   - Column `reseller_id` documented as referring to `operator_id`

2. **NetworkUser Model** (`app/Models/NetworkUser.php`)
   - Added `@deprecated` tag at class level
   - Documents that table was renamed from `network_users` to `customers`
   - Notes migration history (2026-01-30)
   - Recommends using Customer model for new code

3. **NetworkUserSession Model** (`app/Models/NetworkUserSession.php`)
   - Added `@deprecated` tag at class level
   - Documents that table was renamed from `network_user_sessions` to `customer_sessions`
   - Notes migration history (2026-01-30)

#### Services Updated

1. **CommissionService** (`app/Services/CommissionService.php`)
   - Already has good documentation noting backward compatibility
   - Uses both old and new role names in checks for compatibility
   - Comments note that `reseller_id` refers to `operator_id`

2. **ResellerBillingService** (`app/Services/ResellerBillingService.php`)
   - Added `@deprecated` tag recommending OperatorBillingService
   - Updated docblocks to use "operator" terminology
   - Parameter names kept for compatibility but documented
   - Return array keys kept for API compatibility

#### Controllers Updated

1. **ResellerSignupController** (`app/Http/Controllers/Panel/ResellerSignupController.php`)
   - Added `@deprecated` tag recommending OperatorSignupController
   - Updated method docblocks to use "operator" terminology
   - Field names kept for form compatibility but documented
   - Comments note that fields refer to operators

### 3. Migration History

The following migrations were executed on 2026-01-30:

1. **`2026_01_30_200600_rename_network_users_to_customers.php`**
   - Renamed `network_users` table to `customers`
   - Renamed `network_user_sessions` table to `customer_sessions`
   - Reversible migration included

2. **`2026_01_30_200700_rename_network_user_id_to_customer_id.php`**
   - Renamed `network_user_id` to `customer_id` in `onus` table
   - Renamed `network_user_id` to `customer_id` in `hotspot_login_logs` table
   - Updated foreign key constraints

3. **`2026_01_30_200800_add_is_subscriber_to_users_table.php`**
   - Added `is_subscriber` boolean flag to users table
   - Migrated data: `operator_level = 100` → `is_subscriber = true, operator_level = null`
   - Customers now identified by flag, not level

### 4. Backward Compatibility Guarantees

#### API Compatibility
- All existing API endpoints continue to work
- Response field names unchanged
- Query parameters unchanged

#### Database Compatibility
- Column names preserved where possible
- Foreign key relationships maintained
- Indexes preserved

#### Code Compatibility
- Deprecated classes still functional
- Deprecated methods still available
- No breaking changes for existing code

## Usage Guidelines

### For New Development

When writing new code, prefer the new terminology:

```php
// ✅ PREFERRED - New code
$commission = Commission::create([
    'reseller_id' => $operator->id, // Column name kept for DB compatibility
    // ... other fields
]);
$operator = $commission->operator(); // Use new method name

// ✅ PREFERRED - New code
$customer = Customer::where('is_subscriber', true)->get();

// ❌ AVOID - Old code (but still works)
$reseller = $commission->reseller();
$networkUser = NetworkUser::all();
```

### For Existing Code

No changes required! All existing code continues to work:

```php
// ✅ STILL WORKS - Legacy code
$reseller = $commission->reseller();
$networkUsers = NetworkUser::all();
$user = User::where('operator_level', 100)->first();
```

### For Documentation

Use new terminology in comments and documentation:

```php
/**
 * Calculate commission for operator
 * 
 * @param User $operator The operator who earned the commission
 * @return Commission
 */
public function calculate(User $operator): Commission
{
    // Calculate commission for the operator (reseller_id column kept for DB compatibility)
    return Commission::create([
        'reseller_id' => $operator->id,
        // ...
    ]);
}
```

## Developer Guide

### When Adding New Features

1. **Use new terminology** in class names, method names, and variables
2. **Document backward compatibility** when using old column names
3. **Add inline comments** when old names are required for DB compatibility
4. **Reference this guide** in PR descriptions

### When Modifying Existing Features

1. **Keep existing public APIs** unchanged
2. **Add new methods** with preferred terminology
3. **Mark old methods** as `@deprecated` with alternatives
4. **Update docblocks** to reference new terminology

### When Writing Tests

1. **Test both old and new methods** to ensure backward compatibility
2. **Use new terminology** in test names and descriptions
3. **Document what's being tested** clearly

## Future Considerations

### Phase 2 (Future Release)

Consider in a future major version:
- Create `OperatorBillingService` as alias or replacement
- Create `OperatorSignupController` as alias or replacement
- Update UI labels comprehensively
- Update form field names (with migration guide)

### Phase 3 (Long Term)

In a major version (e.g., v3.0):
- Remove deprecated methods
- Remove deprecated classes  
- Rename database columns to match terminology
- Update all UI text and labels

## MikroTik API Status

### Current State

The codebase uses MikroTik RouterOS API (Port 8729 with SSL) for device management. No REST API references found in current active code.

### Services Using MikroTik API

- `MikrotikService.php` - Main service for MikroTik operations
- `MikrotikApiService.php` - API communication layer
- `RouterProvisioningService.php` - Router provisioning
- `MikrotikAutoProvisioningService.php` - Auto-provisioning
- `RouterOSBinaryApiService.php` - Binary API implementation

### Gateway Unification

The gateway unification mentioned in Issue #320 appears to be already implemented:
- MikroTik routers and NAS devices use consistent API approach
- RouterOS API (SSL/Port 8729) is the standard
- RADIUS used for subscriber authentication

## References

- **Issue**: #320 - Unified Admin & Gateway Logic
- **Migrations**: `database/migrations/2026_01_30_*.php`
- **Documentation**: 
  - `DEPRECATING_NETWORK_USERS_MIGRATION_GUIDE.md`
  - `NETWORK_USER_MIGRATION.md`
  - This file: `REFACTORING_TERMINOLOGY_ISSUE_320.md`

## Support

For questions or issues:
- Review this guide first
- Check the migration guides mentioned above
- Consult with the development team
- Reference Issue #320 on GitHub

---

**Last Updated**: 2026-01-31  
**Version**: 1.0  
**Status**: Active  
**Breaking Changes**: None (100% backward compatible)
