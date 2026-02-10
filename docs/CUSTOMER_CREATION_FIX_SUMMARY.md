# Customer Creation Fix - Summary

## Problem Statement
When creating a new customer from the admin account, the customer doesn't appear in the "All customers list" at https://radius.ispbills.com/panel/admin/customers, but does appear in https://radius.ispbills.com/panel/admin/users.

## Root Cause Analysis

### Original Implementation Issue
The `customersStore()` method in `AdminController` (line 888-907) only created a `NetworkUser` entry:
```php
NetworkUser::create($validated);
```

### Why This Caused the Problem
1. The system has migrated to using the `User` model directly for customers
2. Customers are identified by `operator_level = 100` in the User model
3. The `NetworkUser` model is deprecated for backward compatibility only
4. The customers list (via `CustomerCacheService`) queries `NetworkUser` with a `user` relationship
5. Without a matching User record, customers don't appear in the customers list

## Solution Implemented

### Changes Overview
1. **AdminController.php** - Updated customer creation logic
2. **User.php** - Added customer contact fields to fillable array
3. **Migration** - Added database migration for customer contact fields
4. **Tests** - Created comprehensive test suite

### Detailed Changes

#### 1. AdminController::customersStore() (app/Http/Controllers/Panel/AdminController.php)

**Before:**
```php
public function customersStore(Request $request)
{
    $validated = $request->validate([...]);
    $validated['password'] = bcrypt($validated['password']);
    $validated['is_active'] = true;
    NetworkUser::create($validated);  // ❌ Only creates NetworkUser
    return redirect()->route('panel.admin.customers')
        ->with('success', 'Customer created successfully.');
}
```

**After:**
```php
public function customersStore(Request $request)
{
    $validated = $request->validate([
        'username' => 'required|string|min:3|max:255|unique:users,username|regex:/^[a-zA-Z0-9_-]+$/',
        'password' => 'required|string|min:8',
        'service_type' => 'required|in:pppoe,hotspot,cable-tv,static-ip,other',
        'package_id' => 'required|exists:packages,id',
        'status' => 'required|in:active,inactive,suspended',
        'customer_name' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255|unique:users,email',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:500',
        'ip_address' => 'nullable|ip',
        'mac_address' => 'nullable|string|max:17|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
        'notes' => 'nullable|string|max:1000',
    ]);

    try {
        DB::beginTransaction();

        // ✅ Create customer user with network credentials
        $customer = User::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $validated['customer_name'] ?? $validated['username'],
            'email' => $validated['email'] ?? $validated['username'] . '@local.customer',
            'username' => $validated['username'],
            'password' => bcrypt($validated['password']), // Hashed for app login
            'radius_password' => $validated['password'], // Plain text for RADIUS
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'operator_level' => 100, // Customer level
            'is_active' => true,
            'activated_at' => now(),
            'created_by' => auth()->id(),
            'service_package_id' => $validated['package_id'],
            'service_type' => $validated['service_type'],
            'status' => $validated['status'],
            'ip_address' => $validated['ip_address'] ?? null,
            'mac_address' => $validated['mac_address'] ?? null,
        ]);

        // ✅ Assign customer role
        $customer->assignRole('customer');

        // ✅ RADIUS provisioning happens automatically via UserObserver

        DB::commit();

        return redirect()->route('panel.admin.customers')
            ->with('success', 'Customer created successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to create customer: ' . $e->getMessage());

        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to create customer. Please try again.');
    }
}
```

#### 2. User Model (app/Models/User.php)

Added customer contact fields to the fillable array:
```php
protected $fillable = [
    // ... existing fields
    // Customer contact fields
    'phone',
    'mobile',
    'address',
    'city',
    'state',
    'postal_code',
    'country',
    // ... network fields
];
```

#### 3. Migration (database/migrations/2026_01_27_150000_add_customer_contact_fields_to_users_table.php)

Created a migration to add customer contact fields to the users table:
```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'phone')) {
            $table->string('phone', 20)->nullable()->after('email');
        }
        if (!Schema::hasColumn('users', 'mobile')) {
            $table->string('mobile', 20)->nullable()->after('phone');
        }
        if (!Schema::hasColumn('users', 'address')) {
            $table->text('address')->nullable()->after('mobile');
        }
        if (!Schema::hasColumn('users', 'city')) {
            $table->string('city', 100)->nullable()->after('address');
        }
        if (!Schema::hasColumn('users', 'state')) {
            $table->string('state', 100)->nullable()->after('city');
        }
        if (!Schema::hasColumn('users', 'postal_code')) {
            $table->string('postal_code', 20)->nullable()->after('state');
        }
        if (!Schema::hasColumn('users', 'country')) {
            $table->string('country', 100)->nullable()->after('postal_code');
        }
    });
}
```

#### 4. Tests (tests/Feature/AdminCustomerCreationTest.php)

Created comprehensive test suite with 8 test cases:
- Customer appears in customers list after creation
- RADIUS password handling (plain text for RADIUS, hashed for app)
- Required field validation
- Unique username validation
- Username fallback for name field
- Auto-generated email for customers without email
- Tenant isolation
- Proper role assignment

## Key Features of the Fix

### 1. Proper Customer Creation
- Creates a complete User record with `operator_level = 100`
- Assigns the 'customer' role
- Includes all network credentials needed for service provisioning

### 2. Security
- Hashed password for application login
- Plain text `radius_password` for RADIUS authentication (required by RADIUS protocol)
- Proper tenant isolation
- Unique username and email validation

### 3. Data Integrity
- Database transactions to ensure atomicity
- Error handling with rollback on failure
- Proper field validation

### 4. Backward Compatibility
- Maintains compatibility with existing NetworkUser entries
- RADIUS provisioning via UserObserver works automatically
- No changes required to existing customer records

## Testing Instructions

### Automated Tests
Run the test suite:
```bash
php artisan test --filter AdminCustomerCreationTest
```

### Manual Testing
1. **Access Admin Panel**
   - Log in as admin
   - Navigate to https://radius.ispbills.com/panel/admin/customers

2. **Create New Customer**
   - Click "Add New Customer"
   - Fill in required fields:
     - Username (e.g., "testuser001")
     - Password (minimum 8 characters)
     - Service Type (e.g., "pppoe")
     - Package (select from dropdown)
     - Status (e.g., "active")
   - Optional fields:
     - Customer Name
     - Email
     - Phone
     - Address
     - IP Address
     - MAC Address

3. **Verify Customer Appears**
   - Check /panel/admin/customers - customer should appear in the list
   - Check /panel/admin/users - customer should also appear here
   - Click on the customer to view details

4. **Verify Customer Properties**
   - Customer should have operator_level = 100
   - Customer should have 'customer' role
   - Customer should belong to your tenant
   - Network credentials should be properly set

## Migration Instructions

### For Fresh Installations
The migration will run automatically with:
```bash
php artisan migrate
```

### For Existing Installations
1. **Backup your database first**
2. Run the migration:
   ```bash
   php artisan migrate
   ```
3. The migration checks for existing columns before adding them
4. No data loss should occur

## Security Considerations

### RADIUS Password Storage
- The `radius_password` field stores passwords in plain text
- This is **required by the RADIUS protocol** (Cleartext-Password attribute)
- Ensure database has appropriate access controls
- Consider database encryption at rest for additional security

### Input Validation
- Username: Letters, numbers, dashes, and underscores only
- Email: Valid email format, unique across users
- MAC Address: Valid MAC address format (XX:XX:XX:XX:XX:XX)
- IP Address: Valid IPv4 or IPv6 address
- All inputs are sanitized through Laravel's validation

### Access Control
- Only authenticated admins can create customers
- Customers are isolated by tenant_id
- Created_by field tracks which admin created the customer

## Troubleshooting

### Customer Still Doesn't Appear in List
1. Check if the customer was actually created:
   ```sql
   SELECT * FROM users WHERE username = 'customer_username';
   ```
2. Verify operator_level is 100
3. Check if customer has the 'customer' role:
   ```sql
   SELECT * FROM role_user WHERE user_id = <customer_id>;
   ```
4. Clear cache:
   ```bash
   php artisan cache:clear
   ```

### Validation Errors
- Ensure username is unique and contains only allowed characters
- Ensure email is unique (if provided)
- Check minimum password length (8 characters)
- Verify package_id exists in packages table

### Database Errors
- Run migrations: `php artisan migrate`
- Check database connection
- Verify user has proper permissions

## Files Modified

1. `app/Http/Controllers/Panel/AdminController.php`
2. `app/Models/User.php`
3. `database/migrations/2026_01_27_150000_add_customer_contact_fields_to_users_table.php` (new)
4. `tests/Feature/AdminCustomerCreationTest.php` (new)

## Summary

This fix ensures that customers created from the admin panel are properly stored as User records with the correct operator_level and role assignment, making them visible in both the customers list and users list. The implementation follows the same pattern as the CustomerWizardController, ensuring consistency across the application.
