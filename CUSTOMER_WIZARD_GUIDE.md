# Customer Creation Wizard - Task 10

## Overview
A comprehensive 7-step wizard for creating new customers with temporary data storage, draft saving, and automatic initial billing.

## Features

### 1. Temporary Data Storage
- Uses `temp_customers` table to store partial data during wizard progression
- Data stored as JSON for flexibility
- Automatic 24-hour expiration with daily cleanup
- Session-based tracking for resuming drafts

### 2. 7-Step Workflow

#### Step 1: Basic Information
- Customer name (required)
- Mobile number (required)
- Email address (required, unique)

#### Step 2: Connection Type
Select from:
- **PPPoE**: Requires username and password (optional profile)
- **Hotspot**: Requires MAC address (optional device type)
- **Static IP**: Requires IP address (optional subnet)
- **Other**: Custom configuration textarea

#### Step 3: Package Selection
- Visual display of all active packages
- Shows bandwidth, price, and validity
- Package details automatically stored

#### Step 4: Address & Zone
- Street address (required)
- City, State, Postal Code, Country (optional)
- Zone selection dropdown (optional)

#### Step 5: Custom Fields
- Placeholder for future custom field implementation
- Currently skippable

#### Step 6: Initial Payment
- Payment amount (defaults to package price)
- Payment method selection (cash, bank transfer, card, mobile money, other)
- Payment reference (optional)
- Payment notes (optional)
- Smart handling:
  - Full payment: Invoice marked as paid
  - Partial payment: Invoice remains pending
  - Overpayment: Excess added to customer wallet
  - Zero payment: Pending invoice created

#### Step 7: Review & Confirmation
- Display all collected data for review
- Create customer account
- Generate username/password
- Create network user (for PPPoE, Hotspot, Static IP)
- Generate first invoice
- Process payment
- Set expiry date
- Sync to MikroTik (for PPPoE)

### 3. Key Features
- **Draft Saving**: Save progress at any step
- **Session Resumption**: Continue where you left off
- **Validation**: Each step validates before proceeding
- **Progress Indicator**: Visual progress bar showing current step
- **Navigation**: Previous/Next buttons, Cancel option
- **Tenant Isolation**: All data scoped to current tenant

## Usage

### Starting the Wizard
```
GET /panel/admin/customers/wizard/start
```

### Routes
- Start: `panel.admin.customers.wizard.start`
- Step View: `panel.admin.customers.wizard.step` (GET)
- Step Submit: `panel.admin.customers.wizard.store` (POST)
- Cancel: `panel.admin.customers.wizard.cancel` (POST)

### Authorization
Users with roles: super-admin, admin, manager, staff, operator, sub-operator

### Cancel Wizard
- Deletes all temporary data
- Clears session tracking
- Redirects to customer list

## Technical Details

### Database Schema
```sql
CREATE TABLE temp_customers (
    id BIGINT PRIMARY KEY,
    user_id BIGINT (FK to users),
    tenant_id BIGINT (FK to tenants),
    session_id VARCHAR(255) UNIQUE,
    step TINYINT DEFAULT 1,
    data JSON,
    expires_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Models
- **TempCustomer**: Handles temporary data storage
  - Methods: `getStepData()`, `setStepData()`, `getAllData()`, `isExpired()`, `extend()`
  - Scopes: `notExpired()`, `expired()`

### Controller
- **CustomerWizardController**: Handles all wizard steps
  - Methods for each step: `showStepX()`, `processStepX()`
  - Transaction-based final customer creation
  - Automatic username generation if not provided
  - Invoice number generation

### Jobs
- **CleanupExpiredTempCustomersJob**: Daily cleanup of expired records
  - Scheduled: 2:30 AM daily
  - Automatically removes records older than 24 hours

### Views
- Layout: `panels.shared.customers.wizard.layout`
- Steps: `panels.shared.customers.wizard.step1` through `step7`
- Uses Alpine.js for dynamic field visibility
- Tailwind CSS for styling

## Integration

### With Existing Systems
- **Users**: Creates customer user with operator_level=100
- **Roles**: Assigns 'customer' role
- **Packages**: Links to ServicePackage model
- **Zones**: Links to Zone model
- **NetworkUser**: Creates for PPPoE/Hotspot/Static IP
- **Invoices**: Generates first invoice
- **Payments**: Records initial payment
- **MikroTik**: Syncs PPPoE users to router

### Error Handling
- Database transactions ensure data integrity
- Rollback on any failure
- Error logging for MikroTik sync failures (non-blocking)
- User-friendly error messages

## Maintenance

### Cleanup Schedule
The cleanup job runs daily at 2:30 AM and removes:
- Temporary customer records older than 24 hours
- Associated session data

### Manual Cleanup
```bash
php artisan tinker
> App\Models\TempCustomer::deleteExpired();
```

### Extending Expiration
Expiration is automatically extended when user accesses the wizard.

## Future Enhancements
1. Custom field implementation in Step 5
2. Email/SMS notifications on customer creation
3. Multi-language support
4. Import wizard data from CSV
5. Wizard templates for quick setup
6. Welcome email/SMS with credentials

## Security
- Session-based tracking prevents unauthorized access
- Tenant isolation on all queries
- Unique email validation
- Automatic data expiration
- Transaction-based operations
- Password hashing for credentials

## Troubleshooting

### Wizard Session Lost
- Check if session has expired (24 hours)
- Verify browser cookies are enabled
- Check session configuration

### Migration Issues
```bash
php artisan migrate --path=database/migrations/2026_01_24_160128_create_temp_customers_table.php --force
```

### Route Not Found
```bash
php artisan route:cache
php artisan route:list | grep wizard
```

### View Not Found
```bash
php artisan view:clear
php artisan view:cache
```
