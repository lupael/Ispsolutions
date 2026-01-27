# Admin Full Access Fix - Customer Actions

## Problem
Admin users (operator_level = 20) could not see most customer action buttons because the CustomerPolicy required explicit permissions for every action, even for high-level admin roles.

## Solution
Modified CustomerPolicy to automatically grant ALL customer action permissions to:
- **Developer** (operator_level = 0)
- **Super Admin** (operator_level = 10)
- **Admin** (operator_level = 20)

These roles no longer need explicit permission grants to perform customer actions.

## Changes Made

### 1. CustomerPolicy.php
Updated all policy methods to check operator_level first:

```php
public function suspend(User $user, User $customer): bool
{
    // Developer, Super Admin, and Admin have automatic access
    if ($user->operator_level <= 20) {
        return $this->view($user, $customer);
    }
    
    return $this->update($user, $customer) && $user->hasPermission('suspend_customers');
}
```

This pattern was applied to all action methods:
- ✅ update() - Edit customer
- ✅ delete() - Delete customer
- ✅ suspend() - Suspend customer
- ✅ activate() - Activate customer
- ✅ disconnect() - Disconnect session
- ✅ changePackage() - Change service package
- ✅ editSpeedLimit() - Modify bandwidth limits
- ✅ activateFup() - Enable fair usage policy
- ✅ removeMacBind() - Remove MAC binding
- ✅ generateBill() - Generate invoices
- ✅ editBillingProfile() - Edit billing settings
- ✅ sendSms() - Send SMS notifications
- ✅ sendLink() - Send payment links
- ✅ advancePayment() - Record advance payments
- ✅ changeOperator() - Transfer customer (Admin needs permission)
- ✅ editSuspendDate() - Edit suspend date
- ✅ dailyRecharge() - Daily billing recharge
- ✅ hotspotRecharge() - Hotspot voucher recharge

### 2. show.blade.php
Removed unnecessary authorization checks for View Tickets and View Logs buttons:

**Before:**
```blade
@if(auth()->user()->can('viewAny', \App\Models\Ticket::class))
    <a href="...">View Tickets</a>
@endif

@can('view-audit-logs')
    <a href="...">View Logs</a>
@endcan
```

**After:**
```blade
<a href="{{ route('panel.tickets.index', ['customer_id' => $customer->id]) }}">
    View Tickets
</a>

<a href="{{ route('panel.admin.logs.activity', ['customer_id' => $customer->id]) }}">
    View Logs
</a>
```

These buttons are now always visible. Access control is handled by route middleware and controller authorization.

## Verification

### For Admin Users
Login as an Admin user and navigate to any Customer Details page. You should now see **ALL 21 action buttons**:

#### Always Visible (6 buttons)
1. ✅ Edit
2. ✅ Create Ticket
3. ✅ Internet History
4. ✅ Check Usage
5. ✅ View Tickets
6. ✅ View Logs

#### Status-Dependent (15 buttons)
7. ✅ Activate (if customer not active)
8. ✅ Suspend (if customer is active)
9. ✅ Disconnect
10. ✅ Generate Bill
11. ✅ Advance Payment
12. ✅ Other Payment
13. ✅ Change Package
14. ✅ Speed Limit
15. ✅ Time Limit
16. ✅ Volume Limit
17. ✅ MAC Binding
18. ✅ Send SMS
19. ✅ Send Payment Link
20. ✅ Change Operator
21. ✅ Delete Customer

### For Operator/Sub-Operator Users
These roles still require explicit permission grants from Admin:

**Limited Actions (always available):**
- Edit (if granted edit_customers permission)
- Create Ticket
- Internet History
- Check Usage
- View Tickets
- View Logs

**Permission-Based Actions:**
- Activate (if granted activate_customers permission)
- Suspend (if granted suspend_customers permission)
- Advance Payment (if granted record_payments permission)
- Other Payment (if granted record_payments permission)
- Change Package (if granted change_package permission)
- MAC Binding (if granted remove_mac_bind permission)
- Send SMS (if granted send_sms permission AND has SMS balance)
- Send Payment Link (if granted send_payment_link permission)

## Testing Checklist

### As Admin User
- [ ] Login as Admin (operator_level = 20)
- [ ] Navigate to Customers → All Customers
- [ ] Click on any customer to view Customer Details
- [ ] Verify all 21 action buttons are visible
- [ ] Click on each button to verify functionality
- [ ] Confirm no "Access Denied" or permission errors

### As Operator User
- [ ] Login as Operator (operator_level = 30)
- [ ] Navigate to Customers → All Customers
- [ ] Click on your customer to view Customer Details
- [ ] Verify only permitted actions are visible
- [ ] Verify unauthorized actions are not visible
- [ ] Confirm permission-gated actions work correctly

## Operator Level Reference

```
OPERATOR_LEVEL_DEVELOPER = 0     → Full access, no restrictions
OPERATOR_LEVEL_SUPER_ADMIN = 10  → Full access, no restrictions
OPERATOR_LEVEL_ADMIN = 20        → Full access, no restrictions
OPERATOR_LEVEL_OPERATOR = 30     → Permission-based access
OPERATOR_LEVEL_SUB_OPERATOR = 40 → Permission-based access
OPERATOR_LEVEL_MANAGER = 50      → Permission-based access
OPERATOR_LEVEL_ACCOUNTANT = 70   → Permission-based access
OPERATOR_LEVEL_STAFF = 80        → Permission-based access
OPERATOR_LEVEL_CUSTOMER = 100    → Limited customer portal access
```

## Migration Notes

### Existing Admin Users
No migration needed. Existing Admin users will automatically gain access to all customer actions without requiring any database changes or permission grants.

### Existing Operator/Sub-Operator Users
No changes. These roles continue to require explicit permission grants as before.

### Permission System
The permission system remains intact for Operator and Sub-Operator roles. The change only affects Developer, Super Admin, and Admin roles to bypass permission checks.

## Security Considerations

### Tenant Isolation
All policy methods still enforce tenant isolation:
```php
if ($user->tenant_id && $customer->tenant_id !== $user->tenant_id) {
    return false;
}
```

Admin users can only access customers within their own tenant (multi-tenant SaaS).

### Hierarchy Checks
For Operator/Sub-Operator roles, hierarchy checks remain in place:
- Can only access customers they created
- Can only access customers in their subordinate hierarchy
- Cannot access customers managed by other operators

## Rollback Procedure

If issues arise, revert the commit:
```bash
git revert 3b79761
```

This will restore the previous permission-checking behavior for Admin users.
