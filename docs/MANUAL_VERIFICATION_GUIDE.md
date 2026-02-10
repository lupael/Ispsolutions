# Manual Verification Guide for Customer Action Button Permissions

## Overview
This guide provides step-by-step instructions to manually verify that the customer action button permissions are working correctly after the fix.

## Prerequisites
- Access to the application with multiple user roles (Admin, Operator, Sub-Operator)
- At least one test customer account in the system

## Test Scenarios

### Scenario 1: Admin Full Access Verification

**Login as**: Admin user (operator_level = 20)

**Steps**:
1. Navigate to the customer list page
2. Click on any customer to view their details
3. Observe all available action buttons

**Expected Results** (Admin should see ALL buttons):
- ✅ Back to List
- ✅ Edit
- ✅ Activate (if customer not active)
- ✅ Suspend (if customer active)
- ✅ Disconnect
- ✅ Change Package
- ✅ Speed Limit
- ✅ Time Limit
- ✅ Volume Limit
- ✅ MAC Binding
- ✅ Generate Bill
- ✅ Billing Profile
- ✅ Advance Payment
- ✅ Other Payment
- ✅ Send SMS
- ✅ Payment Link
- ✅ Create Ticket
- ✅ Internet History
- ✅ Change Operator
- ✅ Check Usage
- ✅ Suspend Date
- ✅ Hotspot Recharge
- ✅ View Tickets
- ✅ View Logs
- ✅ Delete Customer

**Test Actions**:
Click on each button to verify they all work without permission errors.

---

### Scenario 2: Operator Limited Access Verification

**Login as**: Operator user (operator_level = 30)

**Steps**:
1. Navigate to the customer list page
2. Click on a customer that was created by this operator or their sub-operators
3. Observe available action buttons

**Expected Results** (Operator should see ONLY these 14 buttons):
- ✅ Back to List
- ✅ Edit (if has edit_customers permission)
- ✅ Activate (if has activate_customers permission)
- ✅ Suspend (if has suspend_customers permission)
- ✅ Change Package (if has change_package permission)
- ✅ MAC Binding (if has remove_mac_bind permission)
- ✅ Advance Payment (if has record_payments permission)
- ✅ Other Payment (if has record_payments permission)
- ✅ Send SMS (if has send_sms permission)
- ✅ Payment Link (if has send_payment_link permission)
- ✅ Create Ticket (always visible)
- ✅ Internet History (always visible)
- ✅ Check Usage (always visible)
- ✅ View Tickets (always visible)
- ✅ View Logs (always visible)

**Should NOT See** (Admin-only actions):
- ❌ Disconnect
- ❌ Speed Limit
- ❌ Time Limit
- ❌ Volume Limit
- ❌ Generate Bill
- ❌ Billing Profile
- ❌ Change Operator
- ❌ Suspend Date
- ❌ Hotspot Recharge
- ❌ Delete Customer

**Test Actions**:
1. Verify that Admin-only buttons are NOT visible
2. Click on allowed buttons to verify they work correctly
3. Try to access Admin-only routes directly via URL (should get 403 Forbidden):
   - `/panel/admin/customers/{id}/disconnect` → Should return 403
   - `/panel/customers/speed-limit/show/{id}` → Should return 403
   - `/panel/admin/customers/bills/create/{id}` → Should return 403

---

### Scenario 3: Sub-Operator Limited Access Verification

**Login as**: Sub-Operator user (operator_level = 40)

**Steps**:
1. Navigate to the customer list page
2. Click on a customer that was created by this sub-operator
3. Observe available action buttons

**Expected Results**: Same as Operator (Scenario 2)

Sub-Operator should see ONLY the 14 allowed buttons (with proper permissions) and should NOT see any Admin-only buttons.

**Test Actions**: Same as Operator scenario

---

### Scenario 4: Permission-Based Access Control

**Login as**: Operator or Sub-Operator user

**Test Cases**:

#### 4.1 Without edit_customers permission
- ❌ Edit button should NOT be visible

#### 4.2 Without activate_customers permission
- ❌ Activate button should NOT be visible

#### 4.3 Without suspend_customers permission
- ❌ Suspend button should NOT be visible

#### 4.4 Without change_package permission
- ❌ Change Package button should NOT be visible

#### 4.5 Without remove_mac_bind permission
- ❌ MAC Binding button should NOT be visible

#### 4.6 Without record_payments permission
- ❌ Advance Payment button should NOT be visible
- ❌ Other Payment button should NOT be visible

#### 4.7 Without send_sms permission
- ❌ Send SMS button should NOT be visible

#### 4.8 Without send_payment_link permission
- ❌ Payment Link button should NOT be visible

#### 4.9 Always Visible (no permission required)
- ✅ Create Ticket button should ALWAYS be visible
- ✅ Internet History button should ALWAYS be visible
- ✅ Check Usage button should ALWAYS be visible
- ✅ View Tickets button should ALWAYS be visible
- ✅ View Logs button should ALWAYS be visible

---

## Direct URL Access Tests

Test direct URL access to verify authorization at the controller level:

### For Admin User (Should work):
- `/panel/admin/customers/{id}/disconnect` → ✅ Should work
- `/panel/customers/speed-limit/show/{id}` → ✅ Should work
- `/panel/admin/customers/bills/create/{id}` → ✅ Should work
- `/panel/admin/customers/billing-profile/edit/{id}` → ✅ Should work
- `/panel/admin/customers/change-operator/edit/{id}` → ✅ Should work

### For Operator/Sub-Operator (Should fail with 403):
- `/panel/admin/customers/{id}/disconnect` → ❌ 403 Forbidden
- `/panel/customers/speed-limit/show/{id}` → ❌ 403 Forbidden
- `/panel/admin/customers/bills/create/{id}` → ❌ 403 Forbidden
- `/panel/admin/customers/billing-profile/edit/{id}` → ❌ 403 Forbidden
- `/panel/admin/customers/change-operator/edit/{id}` → ❌ 403 Forbidden

### For Operator/Sub-Operator with Permissions (Should work):
- `/panel/admin/customers/edit/{id}` → ✅ Should work (with edit_customers permission)
- `/panel/admin/customers/change-package/edit/{id}` → ✅ Should work (with change_package permission)
- `/panel/admin/customers/advance-payment/create/{id}` → ✅ Should work (with record_payments permission)

---

## Testing Checklist

Use this checklist to track your testing progress:

### Admin User Testing
- [ ] Logged in as Admin
- [ ] All 25+ action buttons visible on customer details page
- [ ] All buttons are clickable and functional
- [ ] No permission errors when accessing any action

### Operator User Testing
- [ ] Logged in as Operator
- [ ] Only 14 allowed buttons visible (with proper permissions)
- [ ] Admin-only buttons (10 actions) are NOT visible
- [ ] Allowed buttons work correctly
- [ ] Direct URL access to Admin-only routes returns 403

### Sub-Operator User Testing
- [ ] Logged in as Sub-Operator
- [ ] Only 14 allowed buttons visible (with proper permissions)
- [ ] Admin-only buttons (10 actions) are NOT visible
- [ ] Allowed buttons work correctly
- [ ] Direct URL access to Admin-only routes returns 403

### Permission-Based Testing
- [ ] Operator without permissions cannot see permission-required buttons
- [ ] Operator with permissions can see and use permission-required buttons
- [ ] Always-visible buttons (Create Ticket, etc.) are visible regardless of permissions

---

## Expected Outcomes Summary

| Action | Admin | Operator (with perms) | Sub-Operator (with perms) | Notes |
|--------|-------|----------------------|--------------------------|-------|
| Edit | ✅ | ✅ | ✅ | Requires edit_customers permission |
| Activate | ✅ | ✅ | ✅ | Requires activate_customers permission |
| Suspend | ✅ | ✅ | ✅ | Requires suspend_customers permission |
| Disconnect | ✅ | ❌ | ❌ | **ADMIN ONLY** |
| Change Package | ✅ | ✅ | ✅ | Requires change_package permission |
| Speed Limit | ✅ | ❌ | ❌ | **ADMIN ONLY** |
| Time Limit | ✅ | ❌ | ❌ | **ADMIN ONLY** |
| Volume Limit | ✅ | ❌ | ❌ | **ADMIN ONLY** |
| MAC Binding | ✅ | ✅ | ✅ | Requires remove_mac_bind permission |
| Generate Bill | ✅ | ❌ | ❌ | **ADMIN ONLY** |
| Billing Profile | ✅ | ❌ | ❌ | **ADMIN ONLY** |
| Advance Payment | ✅ | ✅ | ✅ | Requires record_payments permission |
| Other Payment | ✅ | ✅ | ✅ | Requires record_payments permission |
| Send SMS | ✅ | ✅ | ✅ | Requires send_sms permission |
| Payment Link | ✅ | ✅ | ✅ | Requires send_payment_link permission |
| Create Ticket | ✅ | ✅ | ✅ | Always allowed |
| Internet History | ✅ | ✅ | ✅ | Always allowed |
| Change Operator | ✅ | ❌ | ❌ | **ADMIN ONLY** |
| Check Usage | ✅ | ✅ | ✅ | Always allowed |
| Suspend Date | ✅ | ❌ | ❌ | **ADMIN ONLY** |
| Hotspot Recharge | ✅ | ❌ | ❌ | **ADMIN ONLY** |
| View Tickets | ✅ | ✅ | ✅ | Always allowed |
| View Logs | ✅ | ✅ | ✅ | Always allowed |
| Delete Customer | ✅ | ❌ | ❌ | **ADMIN ONLY** |

---

## Troubleshooting

### If buttons are not appearing correctly:
1. Clear browser cache and refresh the page
2. Ensure user permissions are properly set in the database
3. Check `operator_level` value in users table
4. Verify the user role assignments in `role_user` table

### If 403 errors occur unexpectedly:
1. Check if the user's `operator_level` is correct
2. Verify the customer is in the user's hierarchy (created_by relationship)
3. Check tenant_id matches between user and customer

### If Admin cannot access actions:
1. Verify `operator_level` is 20 or less
2. Check tenant isolation (tenant_id should match)
3. Review application logs for policy evaluation errors

---

## Reporting Issues

If you find any discrepancies during testing, please report:
1. User role and operator_level
2. Action that failed/succeeded unexpectedly
3. Expected vs actual behavior
4. Screenshot of the customer details page
5. Browser console errors (if any)
