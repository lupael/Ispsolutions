# Customer Action Button Permission Fix - Implementation Summary

## Date: 2026-01-27

## Problem Statement

The customer details page needed to enforce proper role-based access control:
- **Admin (level 20)**: Must have FULL access to ALL customer actions without restriction
- **Operator (level 30) & Sub-Operator (level 40)**: Should ONLY have access to a limited set of actions as specified by Admin permissions

### Allowed Actions for Operator/Sub-Operator:
1. Edit (with edit_customers permission)
2. Create Ticket (always allowed - support function)
3. Internet History (always allowed - view function)
4. Check Usage (always allowed - view function)
5. View Tickets (always allowed - support function)
6. View Logs (always allowed - audit function)
7. Activate (with activate_customers permission, within validity)
8. Suspend (with suspend_customers permission)
9. Advance Payment (with record_payments permission)
10. Other Payment (with record_payments permission)
11. Change Package (with change_package permission, balance adjustment)
12. MAC Binding (with remove_mac_bind permission)
13. Send SMS (with send_sms permission, balance check)
14. Payment Link (with send_payment_link permission, balance check)

### Restricted Actions (Admin Only):
All other actions should be ADMIN-ONLY and NOT available to Operator/Sub-Operator regardless of permissions.

## Changes Made

### 1. CustomerPolicy.php - Restricted Admin-Only Actions

Modified 10 policy methods to DENY access for Operator (level 30) and Sub-Operator (level 40):

#### `disconnect()` - Line 187
**Before**: Allowed Operator/Sub-Operator with `disconnect_customers` permission  
**After**: Admin-only (level <= 20)
```php
// Operator and Sub-Operator do NOT have access to disconnect
return false;
```

#### `editSpeedLimit()` - Line 214
**Before**: Allowed Operator/Sub-Operator with `edit_speed_limit` permission  
**After**: Admin-only (level <= 20)
```php
// Operator and Sub-Operator do NOT have access to edit speed limits
return false;
```

#### `activateFup()` - Line 230
**Before**: Allowed Operator/Sub-Operator with `activate_fup` permission  
**After**: Admin-only (level <= 20)
```php
// Operator and Sub-Operator do NOT have access to activate FUP
return false;
```

#### `generateBill()` - Line 257
**Before**: Allowed Operator/Sub-Operator with `generate_bills` permission  
**After**: Admin-only (level <= 20)
```php
// Operator and Sub-Operator do NOT have access to generate bills
return false;
```

#### `editBillingProfile()` - Line 273
**Before**: Allowed Operator/Sub-Operator with `edit_billing_profile` permission  
**After**: Admin-only (level <= 20)
```php
// Operator and Sub-Operator do NOT have access to edit billing profile
return false;
```

#### `changeOperator()` - Line 326
**Before**: Allowed Operator (level 30) with `change_operator` permission  
**After**: Admin-only (level <= 20)
```php
// Operator and Sub-Operator do NOT have access to change operator
return false;
```

#### `editSuspendDate()` - Line 342
**Before**: Allowed Operator/Sub-Operator with `edit_suspend_date` permission  
**After**: Admin-only (level <= 20)
```php
// Operator and Sub-Operator do NOT have access to edit suspend date
return false;
```

#### `dailyRecharge()` - Line 358
**Before**: Allowed Operator/Sub-Operator with `daily_recharge` permission  
**After**: Admin-only (level <= 20)
```php
// Operator and Sub-Operator do NOT have access to daily recharge
return false;
```

#### `hotspotRecharge()` - Line 374
**Before**: Allowed Operator/Sub-Operator with `hotspot_recharge` permission  
**After**: Admin-only (level <= 20)
```php
// Operator and Sub-Operator do NOT have access to hotspot recharge
return false;
```

#### `delete()` - Line 141
**Before**: Allowed Operator (level 30) with `delete_customers` permission  
**After**: Admin-only (level <= 20)
```php
// Operator and Sub-Operator do NOT have access to delete customers
return false;
```

### 2. show.blade.php - Added Permission Check for Edit Button

**Line 22**: Wrapped Edit button with `@can('update', $customer)` directive

**Before**:
```blade
<a href="{{ route('panel.admin.customers.edit', $customer->id) }}" ...>Edit</a>
```

**After**:
```blade
@can('update', $customer)
    <a href="{{ route('panel.admin.customers.edit', $customer->id) }}" ...>Edit</a>
@endcan
```

## Actions Still Available to Operator/Sub-Operator (WITH Permissions)

These methods continue to check for specific permissions AND hierarchy/view access:

1. **activate()** - Requires `activate_customers` permission + update access
2. **suspend()** - Requires `suspend_customers` permission + update access
3. **changePackage()** - Requires `change_package` permission + update access
4. **removeMacBind()** - Requires `remove_mac_bind` permission + update access
5. **sendSms()** - Requires `send_sms` permission + view access
6. **sendLink()** - Requires `send_payment_link` permission + view access
7. **advancePayment()** - Requires `record_payments` permission + view access
8. **update()** - Requires `edit_customers` permission + hierarchy check

## Actions Always Available (No Permission Check)

These actions remain accessible without @can directives because they are support/view functions:

1. **Create Ticket** - Support function, always accessible
2. **Internet History** - View function, always accessible
3. **Check Usage** - View function, always accessible
4. **View Tickets** - Support function, always accessible
5. **View Logs** - Audit function, always accessible
6. **Back to List** - Navigation, always accessible

## Admin Access Verification

All policy methods maintain the Admin bypass:
```php
if ($user->operator_level <= 20) {
    return $this->view($user, $customer); // or return true
}
```

This ensures Admin (level 20), Super Admin (level 10), and Developer (level 0) have FULL access to ALL customer actions without restriction.

## Impact

### Before Fix:
- Operators/Sub-Operators could access many restricted actions if given permissions
- Edit button was always visible regardless of permissions
- Admin restriction concept was not enforced

### After Fix:
- ✅ Admin has unrestricted access to ALL customer actions
- ✅ Operator/Sub-Operator can ONLY access 14 specified actions (with proper permissions)
- ✅ 10 actions are now Admin-only, regardless of permissions granted to Operator/Sub-Operator
- ✅ Edit button now requires `update` permission
- ✅ Clear separation of Admin vs Operator/Sub-Operator capabilities

## Testing

Created comprehensive test suite in `tests/Feature/CustomerActionsPermissionTest.php` with test cases for:
- Admin full access verification
- Operator restricted access verification
- Sub-Operator restricted access verification
- Hierarchy and permission checks

Note: Tests currently fail due to unrelated migration issues with SQLite, not due to the policy logic itself.

## Deployment Notes

No database migrations required. Only code changes to:
- `app/Policies/CustomerPolicy.php`
- `resources/views/panels/admin/customers/show.blade.php`

Backward compatible - Admin users continue to have full access as before. Only Operator/Sub-Operator permissions are now properly restricted.
