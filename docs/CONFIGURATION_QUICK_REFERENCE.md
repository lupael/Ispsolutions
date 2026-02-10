# Quick Reference: Required Configuration

This is a quick reference for the required configuration implemented in this PR.

## Environment Variables to Set

```bash
# Currency Symbol
APP_CURRENCY=৳  # or $, €, £, ₹, etc.

# Tax Rate (percentage)
BILLING_TAX_RATE=15  # 15%

# SMS Gateway
SMS_ENABLED=true
SMS_DEFAULT_GATEWAY=twilio  # or nexmo, bulksms, bangladeshi

# Twilio Configuration (if using Twilio)
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890
```

## Special Permissions Available

Grant these permissions to operators via Admin Panel:

| Permission Key | Description |
|---------------|-------------|
| `generate_bills` | Permission to generate bills for customers |
| `edit_billing_profile` | Permission to edit customer billing profiles |
| `record_payments` | Permission to record customer payments |
| `send_sms` | Permission to send SMS to customers |
| `send_payment_link` | Permission to send payment links to customers |
| `change_operator` | Permission to change customer operator |
| `edit_suspend_date` | Permission to edit customer suspend dates |
| `hotspot_recharge` | Permission to recharge hotspot accounts |

## Quick Setup

1. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your settings
   ```

2. **Clear Cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan config:cache
   ```

3. **Run Migrations** (if not already run)
   ```bash
   php artisan migrate
   ```

4. **Configure SMS Gateway**
   - Go to Admin Panel → Settings → SMS Gateways
   - Add your gateway configuration
   - Test with a sample SMS

5. **Grant Permissions**
   - Go to Admin Panel → Operators & Managers
   - Select an operator
   - Click "Special Permissions"
   - Enable required permissions

## Verify Configuration

```bash
# Test config loading
php artisan tinker --execute="
  echo 'Currency: ' . config('app.currency') . PHP_EOL;
  echo 'Tax Rate: ' . config('billing.tax_rate') . '%' . PHP_EOL;
  echo 'SMS: ' . (config('sms.enabled') ? 'Enabled' : 'Disabled') . PHP_EOL;
"
```

## More Information

See [CONFIGURATION.md](CONFIGURATION.md) for comprehensive documentation.
