# Customer Details Page - Complete Action List

## Overview
The Customer Details page (`/panel/admin/customers/{id}`) provides comprehensive customer management capabilities. This document lists all available actions and their permission requirements.

## Total Actions: 25+

### Always Visible Actions (No Permission Required)
These actions are available to all authenticated users who can view the customer:

1. **Back to List** - Returns to customer list page
2. **Create Ticket** - Opens ticket creation form for this customer
3. **Internet History** - Views customer's internet usage history
4. **Check Usage** - Real-time usage check (AJAX)
5. **View Tickets** - Lists all tickets for this customer
6. **View Logs** - Shows activity logs for this customer

### Permission-Based Actions (19 actions with @can checks)

#### Customer Management
7. **Edit** - Edit customer details (@can('update', $customer))
8. **Delete** - Permanently delete customer (@can('delete', $customer))

#### Status Management
9. **Activate** - Activate customer account (@can('activate', $customer))
10. **Suspend** - Suspend customer account (@can('suspend', $customer))
11. **Disconnect** - Disconnect active session (@can('disconnect', $customer))

#### Package & Network Management
12. **Change Package** - Modify customer's service package (@can('changePackage', $customer))
13. **Speed Limit** - Adjust speed restrictions (@can('editSpeedLimit', $customer))
14. **Time Limit** - Set time-based restrictions (@can('editSpeedLimit', $customer))
15. **Volume Limit** - Configure data volume limits (@can('editSpeedLimit', $customer))
16. **MAC Binding** - Manage MAC address binding (@can('removeMacBind', $customer))

#### Billing & Payments
17. **Generate Bill** - Create new invoice (@can('generateBill', $customer))
18. **Billing Profile** - Edit billing configuration (@can('editBillingProfile', $customer))
19. **Advance Payment** - Record advance payment (@can('advancePayment', $customer))
20. **Other Payment** - Record miscellaneous payment (@can('advancePayment', $customer))

#### Communication
21. **Send SMS** - Send SMS to customer (@can('sendSms', $customer))
22. **Payment Link** - Send payment link via SMS/email (@can('sendLink', $customer))

#### Advanced Operations
23. **Change Operator** - Transfer customer to different operator (@can('changeOperator', $customer))
24. **Suspend Date** - Edit suspension date (@can('editSuspendDate', $customer))
25. **Hotspot Recharge** - Recharge hotspot account (@can('hotspotRecharge', $customer))

## Permission Hierarchy

### Admin (operator_level <= 20)
✅ **ALL 25 ACTIONS** - No explicit permission checks needed
- Developer (level 0)
- Super Admin (level 10)
- Admin (level 20)

Admin users automatically pass all @can checks due to the CustomerPolicy implementation that returns `true` for `operator_level <= 20` on all methods.

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

- [ ] Verify Admin sees all 25 actions
- [ ] Verify Operator sees only permitted actions
- [ ] Verify Sub-Operator sees limited actions
- [ ] Test each action executes correctly
- [ ] Verify tenant isolation works properly
- [ ] Test inline editing save functionality
- [ ] Verify confirmation prompts on destructive actions

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
