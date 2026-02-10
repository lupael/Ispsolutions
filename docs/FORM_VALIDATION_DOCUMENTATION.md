# Form Validation Documentation

## Overview
This document outlines the comprehensive form validation system implemented across the ISP Solution application.

## FormRequest Classes

### User Management

#### StoreUserRequest
**Purpose:** Validates user creation requests  
**Authorization:** superadmin, admin, manager  
**Validation Rules:**
- `name`: required, string, max:255
- `email`: required, email, unique:users,email
- `username`: required, string, max:255, unique:users,username
- `password`: required, string, min:8, confirmed
- `phone`: nullable, string, max:20
- `address`: nullable, string, max:500
- `role_id`: required, exists:roles,id
- `is_active`: boolean
- `parent_id`: nullable, exists:users,id

#### UpdateUserRequest
**Purpose:** Validates user update requests  
**Authorization:** superadmin, admin, manager  
**Validation Rules:** Same as StoreUserRequest, except:
- `email`: unique except current user
- `username`: unique except current user
- `password`: nullable (not required for updates)

### Customer Management

#### StoreCustomerRequest
**Purpose:** Validates customer creation  
**Authorization:** superadmin, admin, manager, staff, operator, sub-operator  
**Validation Rules:**
- `name`: required, string, max:255
- `email`: required, email, unique:users,email
- `username`: required, string, max:255, unique:users,username
- `password`: required, string, min:8, confirmed
- `phone`: required, string, max:20
- `address`: required, string, max:500
- `city`: nullable, string, max:100
- `state`: nullable, string, max:100
- `postal_code`: nullable, string, max:20
- `country`: nullable, string, max:100
- `package_id`: required, exists:service_packages,id
- `installation_date`: nullable, date
- `billing_cycle`: required, in:monthly,quarterly,semi-annual,annual
- `connection_type`: required, in:pppoe,hotspot,static_ip
- `is_active`: boolean
- `notes`: nullable, string, max:1000

#### UpdateCustomerRequest
**Purpose:** Validates customer updates  
**Authorization:** superadmin, admin, manager, staff, operator, sub-operator  
**Validation Rules:** Same as StoreCustomerRequest, except:
- `email`: unique except current customer
- `username`: unique except current customer
- `password`: nullable (not required for updates)

### Package Management

#### StorePackageRequest
**Purpose:** Validates service package creation  
**Authorization:** superadmin, admin, manager  
**Validation Rules:**
- `name`: required, string, max:255
- `bandwidth_up`: required, numeric, min:0
- `bandwidth_down`: required, numeric, min:0
- `bandwidth_unit`: required, in:kbps,mbps,gbps
- `price_monthly`: required, numeric, min:0
- `price_daily`: nullable, numeric, min:0
- `validity_days`: required, integer, min:1
- `validity_unit`: required, in:days,months
- `data_limit`: nullable, numeric, min:0
- `data_limit_unit`: nullable, in:mb,gb,tb
- `connection_type`: required, in:pppoe,hotspot,static_ip
- `is_active`: boolean
- `description`: nullable, string, max:1000
- `mikrotik_profile`: nullable, string, max:255

#### UpdatePackageRequest
**Purpose:** Validates package updates  
**Authorization:** superadmin, admin, manager  
**Validation Rules:** Same as StorePackageRequest

### Invoice Management

#### StoreInvoiceRequest
**Purpose:** Validates invoice creation  
**Authorization:** admin, super-admin  
**Validation Rules:**
- `user_id`: required, exists:users,id
- `package_id`: nullable, exists:service_packages,id
- `amount`: required, numeric, min:0
- `tax_amount`: nullable, numeric, min:0
- `billing_period_start`: nullable, date
- `billing_period_end`: nullable, date, after_or_equal:billing_period_start
- `due_date`: nullable, date
- `notes`: nullable, string, max:1000

#### UpdateInvoiceRequest
**Purpose:** Validates invoice updates  
**Authorization:** superadmin, admin, manager  
**Validation Rules:**
- `user_id`: required, exists:users,id
- `package_id`: nullable, exists:service_packages,id
- `amount`: required, numeric, min:0
- `tax_amount`: nullable, numeric, min:0
- `discount_amount`: nullable, numeric, min:0
- `total_amount`: required, numeric, min:0
- `billing_period_start`: nullable, date
- `billing_period_end`: nullable, date, after_or_equal:billing_period_start
- `due_date`: nullable, date
- `status`: required, in:pending,paid,overdue,cancelled,partial
- `notes`: nullable, string, max:1000

### Payment Management

#### StorePaymentRequest
**Purpose:** Validates payment processing  
**Authorization:** Based on user permissions  
**Validation Rules:**
- `user_id`: required, exists:users,id
- `invoice_id`: nullable, exists:invoices,id
- `amount`: required, numeric, min:0.01
- `payment_method`: required, in:cash,bank_transfer,online,card,mobile_money,cheque
- `payment_date`: required, date
- `transaction_id`: nullable, string, max:255
- `status`: required, in:pending,completed,failed,refunded
- `notes`: nullable, string, max:1000

#### UpdatePaymentRequest
**Purpose:** Validates payment updates  
**Authorization:** superadmin, admin, manager  
**Validation Rules:** Same as StorePaymentRequest

### Network User Management

#### StoreNetworkUserRequest
**Purpose:** Validates network user creation  
**Authorization:** superadmin, admin, manager, staff  
**Validation Rules:**
- `username`: required, string, max:255, unique:network_users,username
- `password`: required, string, min:6
- `package_id`: required, exists:service_packages,id
- `user_id`: required, exists:users,id
- `service_type`: required, in:pppoe,hotspot,static_ip
- `ip_address`: nullable, ip, unique:network_users,ip_address
- `mac_address`: nullable, string, max:17, unique:network_users,mac_address
- `mikrotik_router_id`: nullable, exists:mikrotik_routers,id
- `nas_id`: nullable, exists:nas,id
- `is_active`: boolean
- `auto_renew`: boolean
- `expiry_date`: nullable, date
- `notes`: nullable, string, max:1000

#### UpdateNetworkUserRequest
**Purpose:** Validates network user updates  
**Authorization:** superadmin, admin, manager, staff  
**Validation Rules:** Same as StoreNetworkUserRequest, except:
- `username`: unique except current network user
- `password`: nullable (not required for updates)
- `ip_address`: unique except current network user
- `mac_address`: unique except current network user

### Hotspot Management

#### StoreHotspotUserRequest
**Purpose:** Validates hotspot user creation  
**Authorization:** Based on hotspot policy  
**Validation Rules:**
- `phone_number`: required, string, max:20, unique:hotspot_users,phone_number
- `username`: required, string, max:50, unique:hotspot_users,username
- `password`: required, string, min:6, max:50
- `package_id`: required, exists:packages,id
- `tenant_id`: required, exists:tenants,id

#### UpdateHotspotUserRequest
**Purpose:** Validates hotspot user updates  
**Authorization:** Can update hotspot user  
**Validation Rules:**
- `phone_number`: required, string, max:20, unique except current user
- `username`: required, string, max:50, unique except current user
- `password`: nullable, string, min:6, max:50
- `package_id`: required, exists:packages,id
- `status`: required, in:active,suspended,expired,pending

### Cable TV Management

#### StoreCableTvSubscriptionRequest
**Purpose:** Validates cable TV subscription creation  
**Authorization:** superadmin, admin, manager  
**Validation Rules:**
- `user_id`: nullable, exists:users,id
- `package_id`: required, exists:cable_tv_packages,id
- `customer_name`: required, string, max:255
- `customer_phone`: required, string, max:20
- `customer_email`: nullable, email, max:255
- `customer_address`: nullable, string, max:500
- `installation_address`: nullable, string, max:500
- `start_date`: required, date
- `auto_renew`: boolean
- `notes`: nullable, string, max:1000

#### UpdateCableTvSubscriptionRequest
**Purpose:** Validates cable TV subscription updates  
**Authorization:** superadmin, admin, manager  
**Validation Rules:** Same as StoreCableTvSubscriptionRequest, plus:
- `expiry_date`: required, date
- `status`: required, in:active,suspended,expired,cancelled

### MikroTik Router Management

#### StoreMikrotikRouterRequest
**Purpose:** Validates MikroTik router addition  
**Authorization:** superadmin, admin  
**Validation Rules:**
- `name`: required, string, max:255
- `host`: required, string, max:255
- `port`: required, integer, min:1, max:65535
- `username`: required, string, max:255
- `password`: required, string, max:255
- `api_port`: nullable, integer, min:1, max:65535
- `is_active`: boolean
- `location`: nullable, string, max:255
- `notes`: nullable, string, max:1000

#### UpdateMikrotikRouterRequest
**Purpose:** Validates MikroTik router updates  
**Authorization:** superadmin, admin  
**Validation Rules:** Same as StoreMikrotikRouterRequest, except:
- `password`: nullable (not required for updates)

### Ticket Management

#### StoreTicketRequest
**Purpose:** Validates support ticket creation  
**Authorization:** Any authenticated user  
**Validation Rules:**
- `subject`: required, string, max:255
- `category`: required, in:technical,billing,general,complaint
- `priority`: required, in:low,medium,high,urgent
- `description`: required, string, min:10, max:2000
- `attachment`: nullable, file, max:5120, mimes:jpg,jpeg,png,pdf,doc,docx

### Bulk Operations

#### BulkDeleteRequest
**Purpose:** Validates bulk delete operations  
**Authorization:** superadmin, admin  
**Validation Rules:**
- `ids`: required, array, min:1
- `ids.*`: required, integer, min:1
- `confirm`: accepted

#### BulkActionRequest
**Purpose:** Validates bulk actions on multiple records  
**Authorization:** superadmin, admin, manager  
**Validation Rules:**
- `ids`: required, array, min:1
- `ids.*`: required, integer, min:1
- `action`: required, string, in:activate,deactivate,suspend,delete,lock,unlock,generate_invoice
- `confirm`: accepted

## Error Handling Pattern

All controllers using FormRequests follow this pattern:

```php
public function store(StoreXxxRequest $request): RedirectResponse
{
    try {
        $data = $request->validated();
        
        // Business logic here
        $model = Model::create($data);
        
        return redirect()->route('xxx.index')
            ->with('success', 'Record created successfully.');
    } catch (\Exception $e) {
        Log::error('Operation failed: ' . $e->getMessage(), [
            'data' => $request->validated(),
        ]);
        
        return back()->withInput()
            ->with('error', 'Operation failed. Please try again.');
    }
}
```

## Client-Side Validation

HTML5 validation attributes are used on forms:
- `required` - Marks field as mandatory
- `minlength` / `maxlength` - String length constraints
- `min` / `max` - Numeric constraints
- `pattern` - Regex validation for formats
- `type="email"` - Email validation
- `type="tel"` - Phone number input
- `type="number"` - Numeric input

Error display using Blade:
```blade
@error('field_name')
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
@enderror
```

Old input values are preserved:
```blade
<input value="{{ old('field_name') }}">
```

## Best Practices

1. **Keep validation in FormRequests** - Don't validate in controllers
2. **Use custom messages** - Provide clear, user-friendly error messages
3. **Implement authorization** - Check permissions in FormRequest::authorize()
4. **Log errors** - Always log exceptions with context
5. **Handle failures gracefully** - Return user-friendly messages
6. **Preserve form data** - Use withInput() on errors
7. **Use transactions** - Wrap multi-step operations in DB transactions
8. **Validate early** - Client-side validation improves UX
9. **Sanitize input** - Trust but verify all user input
10. **Test validation** - Write tests for validation rules

## Testing Validation

Example test for FormRequest validation:

```php
public function test_store_customer_requires_name()
{
    $response = $this->post(route('customers.store'), [
        // Missing 'name'
        'email' => 'test@example.com',
    ]);
    
    $response->assertSessionHasErrors('name');
}

public function test_store_customer_validates_email_format()
{
    $response = $this->post(route('customers.store'), [
        'name' => 'John Doe',
        'email' => 'invalid-email',
    ]);
    
    $response->assertSessionHasErrors('email');
}
```

## Security Considerations

1. **CSRF Protection** - All forms include @csrf token
2. **Mass Assignment Protection** - Use $fillable or $guarded in models
3. **SQL Injection Prevention** - Use query builder and Eloquent
4. **XSS Prevention** - Blade escapes output by default
5. **Authorization Checks** - Verify permissions before operations
6. **Rate Limiting** - Implement throttling on sensitive endpoints
7. **Input Sanitization** - Validate and sanitize all input
8. **Secure Password Handling** - Use bcrypt/argon2 hashing
9. **File Upload Validation** - Restrict file types and sizes
10. **API Token Validation** - Validate and sanitize API requests
