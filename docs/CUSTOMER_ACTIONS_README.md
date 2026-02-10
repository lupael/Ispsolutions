# Customer Details Page Actions - Quick Reference

This is a quick reference guide for the customer details page actions implementation.

## 📚 Documentation Files

1. **[CUSTOMER_DETAILS_ACTIONS_GUIDE.md](CUSTOMER_DETAILS_ACTIONS_GUIDE.md)** - Complete implementation guide with code examples
2. **[CUSTOMER_ACTIONS_TODO.md](CUSTOMER_ACTIONS_TODO.md)** - Implementation status and task tracking
3. **[DOCUMENTATION_INDEX.md](DOCUMENTATION_INDEX.md)** - Updated index with new documentation

## ✅ What's Implemented

### Controllers
- ✅ **CustomerDisconnectController** - Disconnect customer sessions (PPPoE/Hotspot)
- ✅ **CustomerPackageChangeController** - Change customer package with proration

### Policy Methods
All policy methods added to `app/Policies/CustomerPolicy.php`:
- ✅ disconnect()
- ✅ changePackage()
- ✅ editSpeedLimit()
- ✅ activateFup()
- ✅ removeMacBind()
- ✅ generateBill()
- ✅ editBillingProfile()
- ✅ sendSms()
- ✅ sendLink()
- ✅ advancePayment()
- ✅ changeOperator()
- ✅ editSuspendDate()
- ✅ dailyRecharge()
- ✅ hotspotRecharge()

### Routes
Added to `routes/web.php`:
```php
Route::post('/customers/{id}/disconnect', [CustomerDisconnectController::class, 'disconnect']);
Route::get('/customers/{id}/change-package', [CustomerPackageChangeController::class, 'edit']);
Route::put('/customers/{id}/change-package', [CustomerPackageChangeController::class, 'update']);
```

### UI Components
- ✅ Enhanced customer details page (`resources/views/panels/admin/customers/show.blade.php`)
  - Added action buttons with authorization checks
  - Added JavaScript handlers for AJAX actions
  - Added loading states and notifications
- ✅ Package change form (`resources/views/panels/admin/customers/change-package.blade.php`)
  - Package selection
  - Effective date
  - Proration options
  - Reason field

## 🎯 Action Buttons Available

On the customer details page, operators will see buttons based on their permissions:

1. **Edit** - Edit customer information
2. **Activate** - Enable customer network access (if suspended/inactive)
3. **Suspend** - Disable customer network access (if active)
4. **Disconnect** - Force disconnect active session
5. **Change Package** - Upgrade/downgrade service package
6. **Check Usage** - View real-time usage (placeholder)
7. **Create Ticket** - Create support ticket
8. **Recharge** - Add payment/recharge account (placeholder)

## 🔧 How Actions Work

### Activate/Suspend (Existing)
```
User clicks button → Confirmation dialog → AJAX POST request → 
Update NetworkUser status → Clear cache → Return JSON → Show notification → Reload page
```

### Disconnect (New)
```
User clicks button → Confirmation dialog → AJAX POST request →
Query active sessions → Connect to MikroTik → Remove PPP/Hotspot session →
Log action → Return JSON → Show notification → Reload page
```

### Change Package (New)
```
User clicks button → Navigate to form → Select new package → Set options →
Submit form → Calculate proration → Create PackageChangeRequest →
Update NetworkUser → Generate invoice → Update RADIUS attributes →
Disconnect session → Redirect to customer details → Show success
```

## 📋 Implementation Checklist

From the IspBills reference system, here's what we're implementing:

### Status: Complete ✅
- [x] Documentation
- [x] Disconnect action
- [x] Change Package action
- [x] Policy methods
- [x] Routes
- [x] UI enhancements
- [x] JavaScript handlers

### Status: Partial 🟡
- Activate (basic implementation exists, needs RADIUS integration)
- Suspend (basic implementation exists, needs RADIUS integration)
- Edit Speed Limit (controller exists, needs enhancement)
- Edit Time Limit (controller exists, needs enhancement)
- Edit Volume Limit (controller exists, needs enhancement)
- Remove MAC Bind (controller exists, needs enhancement)

### Status: Planned ⚪
- Activate FUP
- Generate Bill
- Edit Billing Profile
- Send SMS
- Send Payment Link
- Advance Payment
- Other Payment
- Internet History Export
- Change Operator
- Check Usage (real-time)
- Hotspot Recharge

## 🧪 Testing

### What to Test

1. **Authorization**
   - Test that buttons only appear for authorized users
   - Test that unauthorized API calls are rejected

2. **Disconnect Action**
   - Test with active PPPoE session
   - Test with active Hotspot session
   - Test with no active session
   - Test with unreachable router

3. **Change Package**
   - Test package upgrade
   - Test package downgrade
   - Test proration calculation
   - Test invoice generation
   - Test RADIUS attribute updates
   - Test with same package (should reject)

4. **UI/UX**
   - Test button states (enabled/disabled)
   - Test loading indicators
   - Test success notifications
   - Test error handling
   - Test on different screen sizes

### How to Test

1. **Setup Test Environment**
   ```bash
   # Create test users with different roles
   php artisan tinker
   >>> $admin = User::where('operator_level', 20)->first();
   >>> $customer = User::where('operator_level', 80)->first();
   ```

2. **Test Disconnect**
   ```bash
   # Via browser: Navigate to customer details and click Disconnect
   # Via API:
   curl -X POST http://localhost/panel/admin/customers/{id}/disconnect \
     -H "Authorization: Bearer {token}" \
     -H "Accept: application/json"
   ```

3. **Test Change Package**
   - Navigate to customer details
   - Click "Change Package"
   - Select new package
   - Submit form
   - Verify package changed
   - Verify invoice created (if proration > 0)
   - Verify customer disconnected

## 🚀 Next Steps

### Priority 1 (This Week)
1. Enhance existing Activate/Suspend with RADIUS integration
2. Implement Activate FUP
3. Implement Generate Bill

### Priority 2 (Next Week)
4. Implement Send SMS
5. Implement Send Payment Link
6. Enhance existing speed/time/volume limit controllers
7. Enhance Remove MAC Bind

### Priority 3 (Following Weeks)
8. Implement remaining actions
9. Add comprehensive tests
10. Add screenshots to documentation
11. Create video demonstrations

## 📖 Reference

### IspBills System Reference
The implementation is based on the IspBills system customer details page:
- File: `resources/views/admins/components/customer-details.blade.php`
- Repository: https://github.com/sohag1426/IspBills

### Key Concepts Adapted
1. **Action Triggers**: JS-triggered server actions with `callUsersActionURL()`
2. **Authorization**: `@can()` policy gates for all actions
3. **RADIUS Integration**: Update radcheck/radreply for PPPoE changes
4. **MikroTik Integration**: Disconnect via PPP/Hotspot API
5. **Proration**: Calculate billing adjustments for package changes
6. **Audit Logging**: Record all important actions

### Differences from IspBills
1. We use NetworkUser model instead of Customer model
2. We use Laravel's built-in authorization instead of custom middleware
3. We use database transactions for data integrity
4. We use type declarations for better code quality
5. We follow Laravel conventions more strictly

## 📞 Support

For questions or issues:
1. Check [TROUBLESHOOTING_GUIDE.md](TROUBLESHOOTING_GUIDE.md)
2. Review [CUSTOMER_DETAILS_ACTIONS_GUIDE.md](CUSTOMER_DETAILS_ACTIONS_GUIDE.md)
3. Check [CUSTOMER_ACTIONS_TODO.md](CUSTOMER_ACTIONS_TODO.md) for implementation status
4. Create an issue on GitHub

---

**Last Updated:** 2026-01-26  
**Status:** Phase 1 Complete, Phase 2 In Progress
