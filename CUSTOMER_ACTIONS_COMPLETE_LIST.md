# Customer Details Page - Complete Action List

## Overview
The Customer Details page (`/panel/admin/customers/{id}`) provides comprehensive customer management capabilities. This document lists all available actions and their permission requirements.

## Total Actions: 24+

### Always Visible Actions (7 actions - No Permission Required)
These actions are available to all authenticated users who can view the customer:

0. **Back to List** - Returns to customer list page (Navigation)
16. **Create Ticket** - Opens ticket creation form for this customer (Add Complaint)
17. **Internet History** - Views customer's internet usage history
19. **Check Usage** - Real-time usage check (AJAX)
22. **View Tickets** - Lists all tickets for this customer
23. **View Activity Logs** - Shows activity logs for this customer

### Permission-Based Actions (17 actions with @can checks)
These actions require specific permissions. For Admin users (operator_level <= 20), ALL permissions are automatically granted.

#### Customer Management Actions
1. **Edit** - Edit customer details (@can('update', $customer))
24. **Delete** - Permanently delete customer (@can('delete', $customer))

#### Status Management Actions (Conditional based on current status)
2. **Activate** - Activate customer account (@can('activate', $customer)) - Only shown if customer is NOT active
3. **Suspend** - Suspend customer account (@can('suspend', $customer)) - Only shown if customer IS active
4. **Disconnect** - Disconnect active session (@can('disconnect', $customer))

#### Package & Network Management Actions
5. **Change Package** - Modify customer's service package (@can('changePackage', $customer))
6. **Speed Limit** - Adjust speed restrictions (@can('editSpeedLimit', $customer))
7. **Time Limit** - Set time-based restrictions (@can('editSpeedLimit', $customer))
8. **Volume Limit** - Configure data volume limits (@can('editSpeedLimit', $customer))
9. **MAC Binding** - Manage MAC address binding (@can('removeMacBind', $customer))

#### Billing & Payment Actions
10. **Generate Bill** - Create new invoice (@can('generateBill', $customer))
11. **Edit Billing Profile** - Edit billing configuration (@can('editBillingProfile', $customer))
12. **Advance Payment** - Record advance payment / Recharge (@can('advancePayment', $customer))
13. **Other Payment** - Record miscellaneous payment (@can('advancePayment', $customer))

#### Communication Actions
14. **Send SMS** - Send SMS to customer (@can('sendSms', $customer))
15. **Send Payment Link** - Send payment link via SMS/email (@can('sendLink', $customer))

#### Advanced Operations
18. **Change Operator** - Transfer customer to different operator (@can('changeOperator', $customer))
20. **Edit Suspend Date** - Edit suspension date (@can('editSuspendDate', $customer))
21. **Hotspot Recharge** - Recharge hotspot account (@can('hotspotRecharge', $customer))

## Permission Hierarchy

### Admin (operator_level <= 20)
✅ **ALL 24 ACTIONS VISIBLE** (7 always + up to 17 permission-based)
- Developer (level 0)
- Super Admin (level 10)  
- Admin (level 20)

Admin users automatically pass all @can checks due to the CustomerPolicy implementation that returns `true` for `operator_level <= 20` on all methods.

**Note**: The Activate and Suspend buttons are status-dependent:
- Activate button only shows when customer status is NOT 'active'
- Suspend button only shows when customer status IS 'active'
This means an Admin will typically see 22-23 actions at a time, not all 24 simultaneously.

### Operator (operator_level = 30)
Requires explicit permissions for most actions. Can view customers in their hierarchy.

### Sub-Operator (operator_level = 40)
Limited permissions. Can only manage customers they created or were assigned to.

### Staff/Manager (operator_level 50-80)
Role-specific permissions apply based on their assigned responsibilities.

## Implementation Details

### CustomerPolicy Authorization
All permission-based actions use the CustomerPolicy which implements this pattern:

```php
public function someAction(User $user, User $customer): bool
{
    // Developer, Super Admin, and Admin have automatic access
    if ($user->operator_level <= 20) {
        return $this->view($user, $customer);
    }
    
    // For other roles, check explicit permission
    return $this->update($user, $customer) && $user->hasPermission('some_permission');
}
```

### File Locations
- **View**: `resources/views/panels/admin/customers/show.blade.php`
- **Policy**: `app/Policies/CustomerPolicy.php`
- **Controller**: `app/Http/Controllers/Panel/AdminController.php`
- **Inline Editing Component**: `resources/views/components/inline-editable-customer-details.blade.php`

## Testing Checklist

- [ ] Verify Admin sees all appropriate actions (22-23 actions depending on customer status)
- [ ] Verify actions 2 (Activate) and 3 (Suspend) are mutually exclusive based on status
- [ ] Verify 7 actions are always visible regardless of role
- [ ] Verify Operator sees only permitted actions
- [ ] Verify Sub-Operator sees limited actions
- [ ] Test each action executes correctly
- [ ] Verify tenant isolation works properly
- [ ] Test inline editing save functionality
- [ ] Verify confirmation prompts on destructive actions

## Why Admin Might See "Only 5 Actions"

If an Admin user reports seeing only 5-7 actions, check:

1. **Incorrect operator_level**: Verify the admin user has `operator_level <= 20` in the database
2. **Tenant Mismatch**: The customer's `tenant_id` might differ from the admin's `tenant_id`
3. **Status-based visibility**: Activate/Suspend buttons are conditional - only one shows at a time
4. **CustomerPolicy cache**: Clear application cache if policy changes were made
5. **Blade cache**: Clear view cache with `php artisan view:clear`

### Debug Commands
```bash
# Check user's operator level
php artisan tinker
>>> User::find(ADMIN_USER_ID)->operator_level

# Check customer's tenant_id
>>> User::find(CUSTOMER_ID)->tenant_id

# Clear caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

## Related Features

### Inline Editing
The customer details page includes an inline editing component that allows direct editing of:
- General Information (status, service type, name, email, phone, zone)
- Username & Password
- Customer Address
- Router & IP Details
- MAC Address
- Comments

Each section has individual save buttons that appear when changes are made (dirty state tracking).

### Bulk Actions
Available on customer list pages:
- Activate multiple customers
- Suspend multiple customers
- Change package (bulk)
- Change operator (bulk)
- Edit zone (bulk)
- Remove MAC bind (bulk)
- Send SMS (bulk)
- Generate bills (bulk)

### Customer List Pages
1. **All Customers** (`panel.admin.customers.index`)
2. **Online Customers** (`panel.admin.customers.online`)
3. **Offline Customers** (`panel.admin.customers.offline`)

All list pages support:
- Click customer name to open details in new window
- Checkbox selection for bulk actions
- Quick filters and search

## Recent Updates
- Added inline editing with dirty state tracking
- Implemented save confirmation prompts
- Enhanced bulk actions component
- Improved action button organization
- Added comprehensive permission checks for all actions
