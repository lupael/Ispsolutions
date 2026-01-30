# Payment Gateway Integration Guide

## Overview

The ISP Solution platform now includes production-ready integrations for four major payment gateways:

1. **bKash** - Bangladesh's leading mobile financial service
2. **Nagad** - Bangladesh's digital payment gateway
3. **SSLCommerz** - Bangladesh's premier payment gateway
4. **Stripe** - International payment processing platform

All integrations include:
- Production API implementations
- Secure webhook signature verification
- Comprehensive error handling and logging
- Retry logic for failed requests
- Multi-tenancy support

## Table of Contents

- [Gateway Features](#gateway-features)
- [Setup Instructions](#setup-instructions)
- [Configuration](#configuration)
- [Webhook URLs](#webhook-urls)
- [Testing](#testing)
- [Security](#security)
- [Troubleshooting](#troubleshooting)

## Gateway Features

### bKash Integration

**Features:**
- Production API v1.2.0-beta implementation
- OAuth2 token-based authentication
- Automatic token refresh
- Payment creation and execution
- Payment status verification
- HMAC SHA256 webhook signature verification
- Comprehensive error logging

**API Documentation:** https://developer.bkash.com/

**Credentials Required:**
- App Key
- App Secret
- Username
- Password
- Webhook Secret (optional, for signature verification)

### Nagad Integration

**Features:**
- Production API v0.2.0 implementation
- RSA public/private key encryption
- Two-step payment initialization and completion
- Payment verification
- RSA signature verification for webhooks
- Proper key formatting and validation

**API Documentation:** https://developer.nagad.com.bd/

**Credentials Required:**
- Merchant ID
- Merchant Number
- Merchant Private Key (PEM format)
- Nagad Public Key (PEM format)

### SSLCommerz Integration

**Features:**
- Production API v4 implementation
- Complete customer and product information handling
- IPN (Instant Payment Notification) support
- MD5 hash signature verification
- Secondary API validation for transactions
- Multi-currency support

**API Documentation:** https://developer.sslcommerz.com/

**Credentials Required:**
- Store ID
- Store Password
- Currency (default: BDT)

### Stripe Integration

**Features:**
- Stripe Checkout Session implementation
- Payment Intents support
- Webhook event handling
- HMAC SHA256 signature verification with timestamp validation
- Metadata for tracking invoices
- Multi-currency support
- Test and live mode separation

**API Documentation:** https://stripe.com/docs/api

**Credentials Required:**
- Live Publishable Key (pk_live_...)
- Live Secret Key (sk_live_...)
- Test Publishable Key (pk_test_...)
- Test Secret Key (sk_test_...)
- Webhook Secret (whsec_...)
- Currency (default: USD)

## Setup Instructions

### 1. Access Payment Gateway Settings

Navigate to: **Super Admin Panel → Payment Gateway → Settings**

Or visit: `/panel/super-admin/payment-gateway/settings`

### 2. Configure Each Gateway

#### bKash Setup

1. Register for a bKash merchant account:
   - Visit: https://www.bkash.com/merchants
   - Complete merchant registration
   - Get business approval

2. Obtain API credentials:
   - Log in to bKash Merchant Portal
   - Navigate to API Integration section
   - Get: App Key, App Secret, Username, Password

3. Configure webhook:
   - In bKash portal, set webhook URL to:
     ```
     https://yourdomain.com/webhooks/payment/bkash
     ```
   - Enable webhook notifications

4. Enter credentials in ISP Solution:
   - Go to Payment Gateway Settings
   - Fill in bKash form
   - Enable "Test Mode" for sandbox testing
   - Save configuration

#### Nagad Setup

1. Register for Nagad merchant account:
   - Visit: https://nagad.com.bd/
   - Apply for merchant account
   - Complete verification process

2. Generate or obtain RSA keys:
   - You'll receive Nagad's public key
   - Generate your private/public key pair if required
   - Format: PEM encoded RSA keys

3. Get Merchant credentials:
   - Merchant ID
   - Merchant Number (11-digit mobile number)

4. Configure callback URL:
   - Set in Nagad portal:
     ```
     https://yourdomain.com/webhooks/payment/nagad
     ```

5. Enter credentials in ISP Solution:
   - Go to Payment Gateway Settings
   - Fill in Nagad form
   - Paste private key (include BEGIN/END markers or just the key)
   - Paste Nagad public key
   - Enable "Test Mode" for sandbox
   - Save configuration

#### SSLCommerz Setup

1. Register for SSLCommerz account:
   - Visit: https://sslcommerz.com/
   - Sign up for merchant account
   - Submit required documents
   - Get account approved

2. Obtain credentials:
   - Log in to SSLCommerz portal
   - Get Store ID and Store Password from API Credentials section

3. Configure IPN (webhook):
   - In SSLCommerz settings, set IPN URL to:
     ```
     https://yourdomain.com/webhooks/payment/sslcommerz
     ```

4. Enter credentials in ISP Solution:
   - Go to Payment Gateway Settings
   - Fill in SSLCommerz form
   - Select currency (BDT, USD, EUR, GBP)
   - Enable "Test Mode" for sandbox
   - Save configuration

#### Stripe Setup

1. Create Stripe account:
   - Visit: https://stripe.com/
   - Sign up for account
   - Complete business verification (for live mode)

2. Get API keys:
   - Log in to Stripe Dashboard
   - Go to Developers → API keys
   - Copy Publishable and Secret keys (both test and live)

3. Create webhook:
   - Go to Developers → Webhooks
   - Click "Add endpoint"
   - Enter webhook URL:
     ```
     https://yourdomain.com/webhooks/payment/stripe
     ```
   - Select events to listen for:
     - `payment_intent.succeeded`
     - `checkout.session.completed`
   - Copy the webhook signing secret (starts with whsec_)

4. Enter credentials in ISP Solution:
   - Go to Payment Gateway Settings
   - Fill in Stripe form
   - Enter both test and live keys
   - Enter webhook secret
   - Select currency
   - Enable "Test Mode" for testing with test keys
   - Save configuration

## Configuration

### Database Structure

Payment gateway configurations are stored in the `payment_gateways` table with the following structure:

```sql
- id: bigint
- tenant_id: bigint (for multi-tenancy)
- name: string (display name)
- slug: string (bkash, nagad, sslcommerz, stripe)
- is_active: boolean
- configuration: text (encrypted JSON)
- test_mode: boolean
- created_at: timestamp
- updated_at: timestamp
```

### Configuration Encryption

All sensitive credentials are encrypted using Laravel's encryption:
- App secrets
- API keys
- Private keys
- Webhook secrets

The `configuration` field uses Laravel's `encrypted:array` cast for automatic encryption/decryption.

### Multi-Tenancy

Each tenant can have their own payment gateway configurations:
- Configurations are isolated by `tenant_id`
- Each tenant can enable/disable gateways independently
- Credentials are tenant-specific

## Webhook URLs

Configure these webhook URLs in your payment gateway dashboards:

| Gateway | Webhook URL |
|---------|-------------|
| bKash | `https://yourdomain.com/webhooks/payment/bkash` |
| Nagad | `https://yourdomain.com/webhooks/payment/nagad` |
| SSLCommerz | `https://yourdomain.com/webhooks/payment/sslcommerz` |
| Stripe | `https://yourdomain.com/webhooks/payment/stripe` |

### Webhook Security

All webhooks implement signature verification:

**bKash:**
- Uses HMAC SHA256 signature
- Signature in `X-Bkash-Signature` header
- Verified against webhook secret

**Nagad:**
- Uses RSA signature with Nagad's public key
- Signature in `X-Nagad-Signature` header
- Verified using OpenSSL functions

**SSLCommerz:**
- Uses MD5 hash verification
- Includes `verify_sign` and `verify_key` in payload
- Secondary API validation call

**Stripe:**
- Uses HMAC SHA256 signature
- Signature in `Stripe-Signature` header
- Includes timestamp for replay attack prevention
- 5-minute tolerance window

## Testing

### Test Mode

All gateways support test/sandbox mode:

1. Enable "Test Mode" checkbox in gateway settings
2. Use sandbox/test credentials
3. Gateway will connect to sandbox endpoints

### Test Credentials

**bKash Sandbox:**
- Base URL: https://tokenized.sandbox.bkash.com
- Test wallets and credentials provided by bKash

**Nagad Sandbox:**
- Base URL: https://sandbox.mynagad.com:8094
- Test merchant credentials provided by Nagad

**SSLCommerz Sandbox:**
- Base URL: https://sandbox.sslcommerz.com
- Test credentials provided by SSLCommerz
- Test cards: Multiple test card numbers available

**Stripe Test Mode:**
- Use test API keys (pk_test_... and sk_test_...)
- Test cards:
  - Success: 4242 4242 4242 4242
  - Decline: 4000 0000 0000 0002
  - More test cards: https://stripe.com/docs/testing

### Testing Workflow

1. **Initialize Payment:**
   ```php
   $paymentService = app(PaymentGatewayService::class);
   $result = $paymentService->initiatePayment($invoice, 'bkash');
   ```

2. **Redirect User:**
   ```php
   if ($result['success']) {
       return redirect($result['payment_url']);
   }
   ```

3. **Handle Webhook:**
   - Webhook automatically processes payment
   - Check logs for webhook processing
   - Verify payment status in invoice

4. **Verify Payment:**
   ```php
   $verification = $paymentService->verifyPayment($transactionId, 'bkash', $tenantId);
   ```

## Security

### Best Practices

1. **Credential Storage:**
   - Never commit credentials to version control
   - Use environment variables for sensitive data
   - Leverage Laravel's encryption for database storage

2. **Webhook Verification:**
   - Always verify webhook signatures
   - Reject requests with invalid signatures
   - Log all verification failures

3. **HTTPS:**
   - Always use HTTPS in production
   - Payment gateways require HTTPS for webhooks
   - Configure SSL certificate properly

4. **Rate Limiting:**
   - Implement rate limiting on webhook endpoints
   - Prevent brute force attacks
   - Use Laravel's throttle middleware

5. **Logging:**
   - Log all payment attempts
   - Log webhook processing
   - Never log sensitive credentials
   - Use log rotation

6. **Error Handling:**
   - Never expose sensitive errors to users
   - Provide generic error messages
   - Log detailed errors for debugging

### Security Checklist

- [ ] All credentials are encrypted
- [ ] Webhook signature verification is enabled
- [ ] HTTPS is configured
- [ ] Rate limiting is implemented
- [ ] Error logging is configured
- [ ] Test mode is disabled in production
- [ ] Webhook URLs are correct
- [ ] IP whitelist configured (if supported by gateway)

## Production Deployment

### Pre-Deployment Checklist

Before deploying to production, ensure the following:

#### 1. API Endpoint Configuration

**bKash:**
- ✅ Production URL is correctly set: `https://tokenized.pay.bkash.com`
- ✅ API version path is configured: `/v1.2.0-beta/` 
  - Note: Despite the "beta" label, this is bKash's official stable production API version
  - bKash uses this naming convention for their tokenized payment API
  - Reference: https://developer.bkash.com/
- ✅ Test mode is **disabled** in gateway settings
- ✅ Production credentials are configured (not sandbox credentials)
- ✅ Webhook secret is set and verified

**Nagad:**
- ✅ Production URL is correctly set: `https://api.mynagad.com`
- ✅ Test mode is **disabled** in gateway settings
- ✅ Production merchant credentials are configured
- ✅ Production RSA keys are configured (not sandbox keys)
- ✅ Webhook secret is set and verified

**SSLCommerz:**
- ✅ Production URL is correctly set: `https://securepay.sslcommerz.com`
- ✅ Test mode is **disabled** in gateway settings
- ✅ Production Store ID and Password are configured
- ✅ Webhook/IPN URL is properly configured

**Stripe:**
- ✅ Production keys are configured (sk_live_... and pk_live_...)
- ✅ Test mode is **disabled** in gateway settings
- ✅ Webhook signing secret is configured (whsec_...)
- ✅ Webhook endpoint is registered in Stripe Dashboard

#### 2. Database Configuration

Ensure the following columns exist in `network_users` table:
- ✅ `fup_exceeded` (boolean, default: false)
- ✅ `fup_exceeded_at` (timestamp, nullable)
- ✅ `fup_reset_at` (timestamp, nullable)

Run migrations if not already applied:
```bash
php artisan migrate
```

#### 3. Webhook Signature Verification

**Critical for Production Security:**

All payment gateways MUST have webhook secrets configured for signature verification. Without this, your application is vulnerable to fraudulent payment notifications.

**Verification Status by Gateway:**

| Gateway | Signature Method | Header | Required Field |
|---------|-----------------|---------|----------------|
| bKash | HMAC SHA256 | X-Bkash-Signature | webhook_secret |
| Nagad | RSA Signature | X-Nagad-Signature | nagad_public_key |
| SSLCommerz | MD5 Hash + API Validation | - | verify_sign (from payload) |
| Stripe | HMAC SHA256 + Timestamp | Stripe-Signature | webhook_secret |

**Testing Signature Verification:**

Before production deployment, test webhook signature verification in staging:

1. Configure webhook secrets in staging environment
2. Initiate test payment
3. Monitor logs for signature verification:
   ```bash
   tail -f storage/logs/laravel.log | grep "webhook"
   ```
4. Confirm successful signature verification:
   - Look for: "Webhook signature verified successfully"
   - Should NOT see: "webhook signature verification failed"

5. Test with invalid signature:
   - Send webhook with modified payload
   - Should be rejected with signature verification failure

#### 4. Environment Variables

Update `.env` file for production (NOT stored in version control):

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Set appropriate log level
LOG_LEVEL=warning
```

**Important:** Payment gateway configurations are stored in the database, not `.env` file. Use the Super Admin panel to configure gateways.

#### 5. Production Settings in Admin Panel

For each payment gateway, navigate to:
**Super Admin Panel → Payment Gateway → Settings**

Then configure:

1. **bKash:**
   - Enter production App Key, App Secret, Username, Password
   - Set webhook_secret (REQUIRED)
   - **UNCHECK** "Test Mode" checkbox
   - Save and verify configuration

2. **Nagad:**
   - Enter production Merchant ID, Merchant Number
   - Enter production RSA keys (private and public)
   - Set webhook_secret (REQUIRED)
   - **UNCHECK** "Test Mode" checkbox
   - Save and verify configuration

3. **SSLCommerz:**
   - Enter production Store ID and Store Password
   - Set currency (BDT/USD/EUR)
   - **UNCHECK** "Test Mode" checkbox
   - Save and verify configuration

4. **Stripe:**
   - Enter production Secret Key (sk_live_...)
   - Enter production Publishable Key (pk_live_...)
   - Set webhook signing secret (whsec_...)
   - Set currency (USD/BDT)
   - **UNCHECK** "Test Mode" checkbox
   - Save and verify configuration

### Post-Deployment Verification

After deploying to production:

1. **Test Each Gateway:**
   - Initiate small test payment (minimum amount)
   - Complete payment flow
   - Verify webhook is received and processed
   - Verify invoice status is updated
   - Verify payment record is created

2. **Monitor Logs:**
   ```bash
   # Monitor for errors
   tail -f storage/logs/laravel.log
   
   # Check for webhook issues
   grep "webhook" storage/logs/laravel.log | tail -20
   
   # Check for signature verification failures
   grep "signature verification failed" storage/logs/laravel.log
   ```

3. **Verify Webhook Endpoints:**
   - Test webhook URLs are accessible: `curl https://yourdomain.com/webhooks/payment/bkash`
   - Should return 405 Method Not Allowed (POST required)
   - Should NOT return 404 or connection errors

4. **Security Verification:**
   - Verify HTTPS is working (no mixed content warnings)
   - Test webhook with invalid signature (should be rejected)
   - Verify rate limiting is working
   - Check that sensitive data is not exposed in error messages

### Rollback Plan

If issues are detected after deployment:

1. **Immediate Action:**
   - Enable "Test Mode" on all gateways to prevent real transactions
   - Or disable gateways entirely by unchecking "Is Active"

2. **Investigation:**
   - Review logs for errors
   - Check webhook signature verification
   - Verify API credentials

3. **Fix and Redeploy:**
   - Fix identified issues
   - Test in staging environment
   - Redeploy to production
   - Re-verify all gateways

### Monitoring and Alerts

Set up monitoring for:
- Failed webhook verifications (> 5 per hour = potential attack)
- Payment initialization failures
- Webhook processing failures
- API timeout errors
- Signature verification failures

## Troubleshooting

### Common Issues

#### 1. Webhook Not Receiving Calls

**Symptoms:**
- Payment initiated but not completed
- No webhook logs

**Solutions:**
- Verify webhook URL is accessible from internet
- Check firewall rules
- Verify HTTPS certificate is valid
- Check webhook URL in gateway dashboard
- Enable webhook logs in gateway portal

#### 2. Signature Verification Failed

**Symptoms:**
- Webhook calls rejected
- "Signature verification failed" in logs

**Solutions:**
- Verify webhook secret is correct
- Check if using correct format (with/without BEGIN/END markers for keys)
- Ensure no extra whitespace in credentials
- Verify timestamp tolerance (Stripe)

#### 3. Payment Not Processing

**Symptoms:**
- User completes payment but invoice not updated

**Solutions:**
- Check webhook processing logs
- Verify invoice number mapping
- Check tenant_id matches
- Verify BillingService is working

#### 4. API Errors

**Symptoms:**
- "Failed to initialize payment"
- Gateway API errors

**Solutions:**
- Verify credentials are correct
- Check if using test/live mode correctly
- Verify account is active with gateway
- Check API rate limits
- Review gateway API logs

### Debugging

Enable detailed logging:

```php
// In .env file
LOG_LEVEL=debug

// Check logs
tail -f storage/logs/laravel.log | grep -i payment
```

View specific gateway logs:

```bash
# bKash logs
grep -i "bkash" storage/logs/laravel.log

# Webhook logs
grep -i "webhook" storage/logs/laravel.log

# Error logs
grep -i "payment.*error" storage/logs/laravel.log
```

### Support

For gateway-specific issues:

- **bKash:** support@bkash.com
- **Nagad:** merchantsupport@mynagad.com
- **SSLCommerz:** support@sslcommerz.com
- **Stripe:** https://support.stripe.com/

For ISP Solution issues:
- Check documentation: `/docs`
- Review logs: `/panel/super-admin/logs`
- Contact system administrator

## API Reference

### PaymentGatewayService Methods

#### initiatePayment()

```php
/**
 * Initiate payment through gateway
 *
 * @param Invoice $invoice
 * @param string $gatewaySlug
 * @param array $additionalData
 * @return array
 */
public function initiatePayment(Invoice $invoice, string $gatewaySlug, array $additionalData = []): array
```

**Returns:**
```php
[
    'success' => true|false,
    'payment_url' => 'https://...',
    'transaction_id' => '...',
    'payment_id' => '...', // Gateway specific
    'amount' => 100.00,
    'gateway' => 'bkash',
    'error' => '...' // If success is false
]
```

#### processWebhook()

```php
/**
 * Process webhook callback from payment gateway
 *
 * @param string $gatewaySlug
 * @param array $payload
 * @return bool
 */
public function processWebhook(string $gatewaySlug, array $payload): bool
```

#### verifyPayment()

```php
/**
 * Verify payment status
 *
 * @param string $transactionId
 * @param string $gatewaySlug
 * @param int $tenantId
 * @return array
 */
public function verifyPayment(string $transactionId, string $gatewaySlug, int $tenantId): array
```

**Returns:**
```php
[
    'success' => true|false,
    'status' => 'completed|pending|failed',
    'transaction_id' => '...',
    'verified' => true|false,
    'amount' => 100.00,
    'data' => [...] // Gateway response
]
```

## Migration from Stub Implementation

If you're upgrading from the stub implementation:

1. **Backup current configurations:**
   ```bash
   php artisan db:backup payment_gateways
   ```

2. **Update credentials:**
   - Review existing gateway configurations
   - Update with production credentials
   - Verify webhook secrets are set

3. **Test each gateway:**
   - Enable test mode
   - Test payment flow
   - Verify webhooks work
   - Check payment verification

4. **Enable production:**
   - Disable test mode
   - Enable gateway
   - Monitor logs for first few transactions

## Changelog

### Version 2.0 - Production Ready (January 2026)

**Added:**
- Production API implementations for all 4 gateways
- Webhook signature verification
- Retry logic for failed requests
- Comprehensive error handling
- SuperAdmin settings UI
- Complete documentation

**Improved:**
- Authentication handling
- Error logging
- Transaction verification
- Multi-tenancy support

**Security:**
- HMAC signature verification
- RSA signature verification
- MD5 hash verification
- Timestamp validation
- Credential encryption

## License

This implementation follows the ISP Solution license agreement.

---

**Last Updated:** January 2026
**Version:** 2.0
**Maintained By:** ISP Solution Development Team
