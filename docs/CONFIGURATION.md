# ISP Solution Configuration Guide

This document describes the required configuration for the ISP Solution application.

## Table of Contents

1. [Database Permissions](#database-permissions)
2. [Billing Configuration](#billing-configuration)
3. [Currency Configuration](#currency-configuration)
4. [SMS Gateway Configuration](#sms-gateway-configuration)
5. [Environment Variables](#environment-variables)

## Database Permissions

### Required Special Permissions

The application includes the following special permissions that can be granted to operators for specific actions:

#### Billing & Payment Permissions

- **`generate_bills`**: Permission to generate bills for customers
- **`edit_billing_profile`**: Permission to edit customer billing profiles
- **`record_payments`**: Permission to record customer payments
- **`send_payment_link`**: Permission to send payment links to customers

#### Communication Permissions

- **`send_sms`**: Permission to send SMS messages to customers

#### Customer Management Permissions

- **`change_operator`**: Permission to change customer operator assignments
- **`edit_suspend_date`**: Permission to edit customer suspend dates

#### Hotspot Permissions

- **`hotspot_recharge`**: Permission to recharge hotspot user accounts

### Configuration File

These permissions are defined in `config/special_permissions.php`. Each permission includes:

- **label**: Human-readable name displayed in the UI
- **description**: Detailed explanation of what the permission allows
- **default**: Whether the permission is granted by default (typically `false` for security)

### Granting Permissions

Special permissions can be granted to operators through:

1. **Admin Panel**: Navigate to Operators → Select Operator → Special Permissions
2. **Database**: Insert records into the `special_permissions` table
3. **Migration/Seeder**: Create custom seeders for default permission sets

## Billing Configuration

### Tax Rate

Configure the default tax rate (VAT/GST) in `config/billing.php` or via environment variable:

```env
BILLING_TAX_RATE=15  # Percentage (e.g., 15 for 15%)
```

### Other Billing Settings

```env
BILLING_GRACE_PERIOD=7          # Days after due date before account is locked
BILLING_INVOICE_PREFIX=INV      # Prefix for invoice numbers
BILLING_PAYMENT_PREFIX=PAY      # Prefix for payment numbers
BILLING_DAILY_BASE_DAYS=30      # Base days for daily billing calculation
```

### Billing Types

The system supports the following billing types (defined in `config/billing.php`):

- **daily**: Daily billing cycle
- **monthly**: Monthly billing cycle
- **onetime**: One-time payment

## Currency Configuration

### Currency Symbol

Configure the currency symbol displayed throughout the application in `config/app.php` or via environment variable:

```env
APP_CURRENCY=$
```

Common currency symbols:
- `$` - US Dollar
- `€` - Euro
- `£` - British Pound
- `₹` - Indian Rupee
- `৳` - Bangladeshi Taka

### Usage

The currency symbol is used in:
- Bill displays
- Payment forms
- Financial reports
- Invoice generation
- Dashboard statistics

Access the currency symbol in your code:

```php
$symbol = config('app.currency');
```

## SMS Gateway Configuration

### Database Table

The application uses the `sms_gateways` table to store SMS gateway configurations. This table includes:

- **name**: Gateway display name
- **slug**: Gateway identifier (twilio, nexmo, bulksms, custom)
- **is_active**: Whether the gateway is currently active
- **is_default**: Whether this is the default gateway
- **configuration**: Encrypted JSON field for API keys and settings
- **balance**: SMS balance for the gateway
- **rate_per_sms**: Cost per SMS

### Supported Gateways

The application supports multiple SMS gateways:

#### 1. Twilio

```env
SMS_ENABLED=true
SMS_DEFAULT_GATEWAY=twilio
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890
```

#### 2. Nexmo/Vonage

```env
SMS_ENABLED=true
SMS_DEFAULT_GATEWAY=nexmo
NEXMO_API_KEY=your_api_key
NEXMO_API_SECRET=your_api_secret
NEXMO_FROM_NUMBER=ISP
```

#### 3. BulkSMS

```env
SMS_ENABLED=true
SMS_DEFAULT_GATEWAY=bulksms
BULKSMS_USERNAME=your_username
BULKSMS_PASSWORD=your_password
```

#### 4. Bangladeshi SMS Gateways

```env
SMS_ENABLED=true
SMS_DEFAULT_GATEWAY=bangladeshi
BD_SMS_API_KEY=your_api_key
BD_SMS_SENDER_ID=your_sender_id
BD_SMS_API_URL=https://api.example.com/send
```

### Gateway Management

SMS gateways can be managed through:

1. **Admin Panel**: Navigate to Settings → SMS Gateways
2. **Database**: Directly insert/update records in the `sms_gateways` table
3. **Configuration**: Set default gateway in `config/sms.php`

### Gateway Configuration Fields

When adding a gateway through the admin panel, you'll need to configure:

- **Name**: Display name for the gateway
- **Gateway Type**: Select from supported gateway types
- **API Credentials**: Gateway-specific credentials (stored encrypted)
- **Sender ID/Number**: The number/ID that appears as the sender
- **Balance**: Initial SMS balance (if applicable)
- **Rate Per SMS**: Cost per SMS message
- **Active Status**: Enable/disable the gateway
- **Default Gateway**: Set as the default gateway for outgoing SMS

## Environment Variables

### Complete Configuration Example

Here's a complete `.env` configuration example:

```env
# Application
APP_NAME="ISP Solution"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_CURRENCY=৳

# Billing
BILLING_TAX_RATE=15
BILLING_GRACE_PERIOD=7
BILLING_INVOICE_PREFIX=INV
BILLING_PAYMENT_PREFIX=PAY

# SMS Gateway
SMS_ENABLED=true
SMS_DEFAULT_GATEWAY=twilio
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890
```

## Setup Steps

Follow these steps after configuring the environment:

### 1. Update Environment File

Copy `.env.example` to `.env` and update with your configuration:

```bash
cp .env.example .env
# Edit with your preferred editor (nano, vim, etc.)
nano .env
```

### 2. Clear Configuration Cache

After updating configuration files, clear and rebuild the Laravel cache:

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan optimize
```

### 3. Run Database Migrations

Ensure all database tables are created:

```bash
php artisan migrate
```

### 4. Verify SMS Gateway Table

Check that the `sms_gateways` table exists:

```bash
php artisan db:show
php artisan migrate:status
```

### 5. Configure SMS Gateway

Add your first SMS gateway through:

- Admin panel: `/panel/admin/sms/gateways`
- Or manually insert into `sms_gateways` table

### 6. Grant Special Permissions

Grant required permissions to operators:

1. Log in as Admin
2. Navigate to Operators & Managers
3. Select an operator
4. Click "Special Permissions"
5. Enable required permissions

## Verification

### Test Configuration

1. **Currency Symbol**: Check if bills and invoices display the correct currency symbol
2. **Tax Rate**: Verify tax calculations on invoices
3. **SMS Gateway**: Send a test SMS through the admin panel
4. **Permissions**: Test operator actions with granted permissions

### Common Issues

#### Currency Symbol Not Displaying

```bash
php artisan config:clear
php artisan optimize
```

#### SMS Not Sending

1. Check `SMS_ENABLED=true` in `.env`
2. Verify gateway credentials
3. Check gateway balance in `sms_gateways` table
4. Review `storage/logs/laravel.log` for errors

#### Permissions Not Working

1. Clear cache: `php artisan cache:clear`
2. Verify permission keys match those in `config/special_permissions.php`
3. Check `special_permissions` table for granted permissions

## Security Considerations

### SMS Gateway Credentials

- Gateway API keys and secrets are stored encrypted in the database
- Never commit actual credentials to version control
- Use environment variables for sensitive configuration
- Rotate API keys periodically

### Special Permissions

- Grant special permissions judiciously
- Regularly audit permission grants
- Use the `granted_at` and `expires_at` fields to track permissions
- Consider setting expiration dates for temporary permissions

## Related Documentation

- [POST_DEPLOYMENT_STEPS.md](POST_DEPLOYMENT_STEPS.md) - Post-deployment checklist
- [INSTALLATION.md](INSTALLATION.md) - Complete installation guide
- [README.md](README.md) - Project overview
- `config/special_permissions.php` - Permission definitions
- `config/billing.php` - Billing configuration
- `config/sms.php` - SMS gateway configuration
- `config/app.php` - Application configuration

## Support

For issues or questions about configuration:

1. Check the troubleshooting sections above
2. Review log files in `storage/logs/laravel.log`
3. Consult the related documentation files
4. Check the GitHub repository issues

---

**Last Updated**: January 26, 2026  
**Version**: 1.0.0
