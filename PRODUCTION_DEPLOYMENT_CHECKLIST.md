# Production Configuration Deployment Guide

## Summary of Changes

This PR updates the payment gateway configuration to be production-ready and adds FUP (Fair Usage Policy) tracking capabilities.

### Key Changes Made:

1. **bKash Production Configuration**
   - Updated API endpoint configuration to use a configurable version variable
   - Changed from hardcoded `/v1.2.0-beta/` path to variable-based approach
   - Applied changes to all bKash API endpoints (token grant, payment creation, status verification)
   - Added clarification that v1.2.0-beta is bKash's official stable production API version

2. **FUP Tracking Database Schema**
   - Added migration for three new columns to `network_users` table:
     - `fup_exceeded` (boolean): Tracks if user exceeded data limit
     - `fup_exceeded_at` (timestamp): When limit was exceeded  
     - `fup_reset_at` (timestamp): When counter resets
   - Added composite index for efficient querying
   - Updated NetworkUser model with proper type casts

3. **Comprehensive Documentation**
   - Enhanced `.env.example` with payment gateway configuration guide
   - Added "Production Deployment" section to PAYMENT_GATEWAY_GUIDE.md
   - Documented webhook security requirements for all gateways
   - Created pre-deployment and post-deployment checklists

## Deployment Steps

### 1. Run Database Migrations

```bash
php artisan migrate
```

This will add the FUP tracking columns to the `network_users` table.

### 2. Configure Payment Gateways for Production

Navigate to: **Super Admin Panel → Payment Gateway → Settings**

For each gateway you're using, ensure:

#### bKash
- [ ] Enter production credentials (App Key, App Secret, Username, Password)
- [ ] Set webhook_secret for signature verification
- [ ] **UNCHECK** "Test Mode" checkbox
- [ ] Save configuration

#### Nagad
- [ ] Enter production Merchant ID and Number
- [ ] Enter production RSA keys
- [ ] Set webhook_secret
- [ ] **UNCHECK** "Test Mode" checkbox
- [ ] Save configuration

#### SSLCommerz
- [ ] Enter production Store ID and Password
- [ ] Set currency (BDT recommended for Bangladesh)
- [ ] **UNCHECK** "Test Mode" checkbox
- [ ] Save configuration

#### Stripe
- [ ] Enter production keys (sk_live_... and pk_live_...)
- [ ] Set webhook signing secret (whsec_...)
- [ ] Set currency
- [ ] **UNCHECK** "Test Mode" checkbox
- [ ] Save configuration

### 3. Verify Webhook Configuration

Ensure webhook URLs are configured in each payment gateway dashboard:

| Gateway | Webhook URL |
|---------|-------------|
| bKash | `https://yourdomain.com/webhooks/payment/bkash` |
| Nagad | `https://yourdomain.com/webhooks/payment/nagad` |
| SSLCommerz | `https://yourdomain.com/webhooks/payment/sslcommerz` |
| Stripe | `https://yourdomain.com/webhooks/payment/stripe` |

### 4. Test in Staging First

Before deploying to production:

1. Configure staging environment with production-like settings
2. Test each payment gateway with real transactions
3. Verify webhook signature verification is working
4. Check logs for any errors
5. Confirm payments are processed correctly

### 5. Monitor After Deployment

Watch for:
- Failed webhook signature verifications (potential security issue)
- Payment initialization failures
- API timeout errors
- Webhook processing errors

Check logs:
```bash
tail -f storage/logs/laravel.log | grep "webhook\|payment"
```

## Security Considerations

### Critical Security Requirements

1. **Webhook Signature Verification**: All gateways MUST have webhook secrets configured. Without this, the system is vulnerable to fraudulent payment notifications.

2. **HTTPS Required**: Payment gateways require HTTPS for webhook callbacks. Ensure SSL certificate is valid.

3. **Test Mode Disabled**: Verify test_mode is set to false for all gateways in production.

4. **Credential Security**: 
   - Never commit real credentials to version control
   - Use encrypted database storage (already implemented)
   - Rotate webhook secrets periodically

### Signature Verification by Gateway

| Gateway | Method | Header | Implemented |
|---------|--------|--------|-------------|
| bKash | HMAC SHA256 | X-Bkash-Signature | ✅ Yes |
| Nagad | RSA Signature | X-Nagad-Signature | ✅ Yes |
| SSLCommerz | MD5 Hash + API Validation | - | ✅ Yes |
| Stripe | HMAC SHA256 + Timestamp | Stripe-Signature | ✅ Yes |

## Rollback Plan

If issues are detected after deployment:

1. **Immediate Action**: Enable "Test Mode" on all gateways to prevent real transactions
2. **Investigation**: Review logs for errors
3. **Fix**: Address identified issues in staging
4. **Redeploy**: Test thoroughly before re-enabling production mode

## Testing Checklist

### Before Production Deployment
- [ ] Database migrations run successfully in staging
- [ ] FUP columns exist in network_users table
- [ ] Payment gateway webhooks configured with correct URLs
- [ ] Webhook secrets configured for all gateways
- [ ] Test Mode enabled in staging for testing
- [ ] Test transactions complete successfully in staging
- [ ] Webhook signature verification tested and working
- [ ] SSL certificate valid and HTTPS working
- [ ] Logs reviewed for any errors

### After Production Deployment
- [ ] Test Mode disabled for all gateways
- [ ] Production credentials verified and working
- [ ] Small test payment completed successfully
- [ ] Webhook received and processed correctly
- [ ] Invoice status updated properly
- [ ] Payment record created in database
- [ ] No errors in logs
- [ ] Signature verification working (no failed verification logs)

## Support

For issues during deployment:
1. Check `storage/logs/laravel.log` for detailed error messages
2. Verify gateway credentials in admin panel
3. Test webhook endpoints are accessible
4. Review PAYMENT_GATEWAY_GUIDE.md for troubleshooting

## Additional Resources

- Full deployment guide: `PAYMENT_GATEWAY_GUIDE.md` (Production Deployment section)
- Environment configuration: `.env.example`
- bKash API Documentation: https://developer.bkash.com/
- Nagad API Documentation: https://developer.nagad.com.bd/
- SSLCommerz Documentation: https://developer.sslcommerz.com/
- Stripe Documentation: https://stripe.com/docs/api
