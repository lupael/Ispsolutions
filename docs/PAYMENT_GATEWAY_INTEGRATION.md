# Payment Gateway Integration Guide

## Overview

This ISP Solution supports four major payment gateways with production-ready implementations:
- **bKash** (Bangladesh mobile financial service)
- **Nagad** (Bangladesh mobile payment gateway)
- **SSLCommerz** (Bangladeshi payment gateway)
- **Stripe** (International payment gateway)

All implementations include:
- ✅ Real API integration (not stubs)
- ✅ Webhook signature verification
- ✅ Payment verification
- ✅ Test/Sandbox mode support
- ✅ Comprehensive error handling and logging

---

## Configuration

### 1. bKash Configuration

**Required Environment Variables:**
```env
BKASH_APP_KEY=your_app_key
BKASH_APP_SECRET=your_app_secret
BKASH_USERNAME=your_username
BKASH_PASSWORD=your_password
BKASH_TEST_MODE=true  # Set to false for production
```

**Configuration in Database (payment_gateways table):**
```json
{
    "app_key": "your_app_key",
    "app_secret": "your_app_secret",
    "username": "your_username",
    "password": "your_password"
}
```

**API Documentation:** https://developer.bkash.com/

---

### 2. Nagad Configuration

**Required Environment Variables:**
```env
NAGAD_MERCHANT_ID=your_merchant_id
NAGAD_MERCHANT_KEY=your_merchant_public_key
NAGAD_PRIVATE_KEY=your_private_key
NAGAD_ACCOUNT_NUMBER=your_account_number
NAGAD_TEST_MODE=true  # Set to false for production
```

**Configuration in Database:**
```json
{
    "merchant_id": "your_merchant_id",
    "merchant_key": "your_merchant_public_key",
    "private_key": "your_private_key",
    "account_number": "your_account_number"
}
```

**API Documentation:** https://developer.nagad.com.bd/

---

### 3. SSLCommerz Configuration

**Required Environment Variables:**
```env
SSLCOMMERZ_STORE_ID=your_store_id
SSLCOMMERZ_STORE_PASSWORD=your_store_password
SSLCOMMERZ_CURRENCY=BDT
SSLCOMMERZ_TEST_MODE=true  # Set to false for production
```

**Configuration in Database:**
```json
{
    "store_id": "your_store_id",
    "store_password": "your_store_password",
    "currency": "BDT"
}
```

**API Documentation:** https://developer.sslcommerz.com/

---

### 4. Stripe Configuration

**Required Environment Variables:**
```env
STRIPE_SECRET_KEY=sk_live_...
STRIPE_TEST_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_live_...
STRIPE_TEST_PUBLISHABLE_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_CURRENCY=USD
STRIPE_TEST_MODE=true  # Set to false for production
```

**Configuration in Database:**
```json
{
    "secret_key": "sk_live_...",
    "test_secret_key": "sk_test_...",
    "publishable_key": "pk_live_...",
    "test_publishable_key": "pk_test_...",
    "currency": "USD"
}
```

**API Documentation:** https://stripe.com/docs/api

---

## Usage

### Initiating a Payment

```php
use App\Services\PaymentGatewayService;
use App\Models\Invoice;

$paymentGateway = new PaymentGatewayService();
$invoice = Invoice::find($invoiceId);

// Initiate payment
$result = $paymentGateway->initiatePayment($invoice, 'bkash', [
    // Optional additional data
]);

if ($result['success']) {
    // Redirect user to payment URL
    return redirect($result['payment_url']);
} else {
    // Handle error
    return back()->withErrors($result['error']);
}
```

### Verifying a Payment

```php
$result = $paymentGateway->verifyPayment($transactionId, 'bkash', $tenantId);

if ($result['verified']) {
    // Payment is valid
    $amount = $result['amount'];
    // Process payment...
} else {
    // Payment failed or invalid
    Log::warning('Payment verification failed', $result);
}
```

### Processing Webhooks

Webhooks are automatically processed when payment gateways send callbacks. The system:
1. Verifies the webhook signature
2. Validates the payment status
3. Updates the invoice
4. Unlocks the customer account
5. Sends confirmation notifications

**Webhook URLs** (configure these in your payment gateway dashboards):
```
bKash:       https://yourdomain.com/api/webhooks/bkash
Nagad:       https://yourdomain.com/api/webhooks/nagad
SSLCommerz:  https://yourdomain.com/api/webhooks/sslcommerz
Stripe:      https://yourdomain.com/api/webhooks/stripe
```

---

## Security Features

### 1. Webhook Signature Verification

All webhooks are verified before processing:

**bKash:** Validates paymentID and merchantInvoiceNumber
**Nagad:** Verifies RSA signature with public key
**SSLCommerz:** Validates MD5 hash with verify_sign and verify_key
**Stripe:** Uses HMAC SHA256 signature verification with webhook secret

### 2. Test Mode Protection

All gateways support test/sandbox mode:
- Test transactions are clearly marked
- Different API endpoints for test vs production
- Prevents accidental production charges during development

### 3. Error Handling

Comprehensive error handling with:
- Try-catch blocks around all API calls
- Detailed error logging
- User-friendly error messages
- Automatic retry logic for transient failures

---

## Payment Flow

### Standard Payment Flow:

```
1. Customer selects payment method
   ↓
2. System initiates payment with gateway
   ↓
3. Customer redirected to gateway checkout page
   ↓
4. Customer completes payment
   ↓
5. Gateway sends webhook to system
   ↓
6. System verifies webhook signature
   ↓
7. System updates invoice status
   ↓
8. System unlocks customer account
   ↓
9. System sends payment confirmation email/SMS
   ↓
10. Customer redirected to success page
```

### Webhook Processing Flow:

```
1. Gateway sends POST request to webhook URL
   ↓
2. System receives webhook payload
   ↓
3. System verifies signature
   ↓
4. System validates payment status
   ↓
5. System finds invoice by reference
   ↓
6. System processes payment via BillingService
   ↓
7. System creates payment record
   ↓
8. System updates invoice status
   ↓
9. System unlocks customer (if applicable)
   ↓
10. System sends notifications
   ↓
11. System returns 200 OK to gateway
```

---

## Testing

### Test Cards (Stripe)

```
Success: 4242 4242 4242 4242
Decline: 4000 0000 0000 0002
Authentication Required: 4000 0025 0000 3155
```

### Test Credentials

**bKash Sandbox:**
- Wallet: 01770618567
- OTP: 123456

**Nagad Sandbox:**
- Wallet: 01711111111
- PIN: 123456

**SSLCommerz Sandbox:**
- Test cards provided in their documentation

**Stripe Test Mode:**
- Use test API keys (sk_test_...)
- Use test cards listed above

---

## Monitoring and Logs

All payment operations are logged:

```php
// Successful payment
Log::info('Payment initiated', [
    'gateway' => 'bkash',
    'invoice' => $invoice->invoice_number,
    'amount' => $amount,
]);

// Failed payment
Log::error('Payment failed', [
    'gateway' => 'bkash',
    'invoice' => $invoice->invoice_number,
    'error' => $errorMessage,
]);

// Webhook processing
Log::info('Webhook processed', [
    'gateway' => 'bkash',
    'transaction_id' => $transactionId,
    'status' => 'success',
]);
```

Check logs at: `storage/logs/laravel.log`

---

## Troubleshooting

### Common Issues

**1. bKash: "Failed to get token"**
- Verify app_key and app_secret are correct
- Check if credentials are for correct environment (sandbox vs production)
- Ensure IP whitelisting is configured in bKash merchant panel

**2. Nagad: "Signature verification failed"**
- Ensure public/private keys are correctly formatted
- Keys should include header/footer: `-----BEGIN PUBLIC KEY-----`
- Check if merchant_id matches your account

**3. SSLCommerz: "Invalid store credentials"**
- Verify store_id and store_password
- Check if store is active in SSLCommerz dashboard
- Ensure correct environment (sandbox vs live)

**4. Stripe: "Invalid API key"**
- Use correct key for environment (test vs live)
- Ensure key starts with `sk_` (secret key)
- Check if key has required permissions

**5. Webhook not received:**
- Verify webhook URL is publicly accessible
- Check if URL is correctly configured in gateway dashboard
- Ensure SSL certificate is valid
- Check firewall/security rules

### Debug Mode

Enable debug logging by setting in `.env`:
```env
LOG_LEVEL=debug
```

Then check detailed API request/response logs in `storage/logs/laravel.log`

---

## Production Checklist

Before going live:

### bKash
- [ ] Obtain production credentials from bKash
- [ ] Set `BKASH_TEST_MODE=false`
- [ ] Configure production webhook URL in bKash merchant panel
- [ ] Test with real transactions (small amounts)
- [ ] Set up IP whitelisting for production server

### Nagad
- [ ] Obtain production keys from Nagad
- [ ] Set `NAGAD_TEST_MODE=false`
- [ ] Configure production callback URLs
- [ ] Test signature generation/verification
- [ ] Complete merchant verification process

### SSLCommerz
- [ ] Obtain production store credentials
- [ ] Set `SSLCOMMERZ_TEST_MODE=false`
- [ ] Configure success/fail/cancel/IPN URLs
- [ ] Enable required payment methods in dashboard
- [ ] Test validation API

### Stripe
- [ ] Replace test keys with live keys
- [ ] Set `STRIPE_TEST_MODE=false`
- [ ] Configure webhook endpoint in Stripe dashboard
- [ ] Enable required payment methods (card, etc.)
- [ ] Set up webhook secret for signature verification
- [ ] Enable 3D Secure (SCA) if required

### General
- [ ] Ensure SSL certificate is valid
- [ ] Set up monitoring for webhook endpoints
- [ ] Configure proper error alerting
- [ ] Test all payment flows end-to-end
- [ ] Set up backup webhook endpoints
- [ ] Configure rate limiting
- [ ] Set up payment reconciliation reports
- [ ] Train support staff on troubleshooting

---

## Support

### Gateway Support

**bKash:** support@bkash.com
**Nagad:** support@nagad.com.bd
**SSLCommerz:** support@sslcommerz.com
**Stripe:** https://support.stripe.com

### Internal Support

For implementation issues:
- Check logs: `storage/logs/laravel.log`
- Review webhook logs in database: `payment_logs` table
- Contact development team

---

## API Rate Limits

**bKash:** 100 requests/minute
**Nagad:** 60 requests/minute
**SSLCommerz:** 100 requests/minute
**Stripe:** 100 requests/second (test mode: 25/second)

Implement exponential backoff for rate limit errors.

---

## Compliance

### PCI DSS Compliance

This implementation is PCI DSS compliant because:
- Card details never touch our servers
- All payments processed via hosted checkout pages
- No storage of card numbers or CVV
- HTTPS/TLS encryption for all API calls

### Data Protection

- Transaction IDs are logged (safe to log)
- Customer payment details are not logged
- API keys stored securely in database (encrypted)
- Webhook signatures prevent tampering

---

## Updates and Maintenance

### Keeping Up-to-Date

1. Monitor gateway API changelog
2. Test updates in sandbox environment
3. Review deprecation notices
4. Update API versions as needed
5. Maintain backward compatibility

### Version History

**v2.0.0 (2026-01-19)**
- Implemented production-ready bKash integration
- Implemented production-ready Nagad integration
- Implemented production-ready SSLCommerz integration
- Implemented production-ready Stripe integration
- Added webhook signature verification for all gateways
- Added comprehensive error handling and logging

**v1.0.0 (Previous)**
- Stub implementations for all gateways
- Basic payment flow

---

## Contributing

When adding new payment gateways:
1. Follow the existing pattern in `PaymentGatewayService`
2. Implement three methods: `initiate*Payment`, `process*Webhook`, `verify*Payment`
3. Add signature verification
4. Add comprehensive error handling
5. Update this documentation
6. Add tests for the new gateway

---

**Last Updated:** 2026-01-19
**Maintained by:** ISP Solution Development Team
