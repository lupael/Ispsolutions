# Auto-Debit User Guide

> **Version:** 1.0  
> **Last Updated:** 2026-01-29  
> **Audience:** ISP Customers, Operators  
> **Reference:** REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Auto-Debit System

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Getting Started](#getting-started)
3. [Setting Up Auto-Debit](#setting-up-auto-debit)
4. [How Auto-Debit Works](#how-auto-debit-works)
5. [Managing Auto-Debit](#managing-auto-debit)
6. [Payment History](#payment-history)
7. [Failed Payments](#failed-payments)
8. [Security and Safety](#security-and-safety)
9. [Troubleshooting](#troubleshooting)
10. [FAQ](#faq)

---

## Overview

Auto-Debit is an automated payment system that charges your saved payment method automatically when your bill is due. Never worry about missing a payment again!

### Benefits

- ✅ **Never Miss a Payment:** Automatic charges on due date
- ✅ **No Late Fees:** Always pay on time
- ✅ **Uninterrupted Service:** Avoid service suspension
- ✅ **Convenience:** Set it and forget it
- ✅ **Flexible:** Enable/disable anytime
- ✅ **Secure:** Bank-grade encryption
- ✅ **Control:** Set payment limits and retry options

---

## Getting Started

### Prerequisites

**To use Auto-Debit, you need:**
- Active customer account
- Saved payment method (bKash, Nagad, Rocket, or card)
- Sufficient balance in payment account

### Who Can Use Auto-Debit?

- Individual customers
- Business customers
- Anyone with recurring bills

### Payment Methods Supported

- **bKash** (with agreement/token)
- **Nagad** (with saved wallet)
- **Rocket** (with saved account)
- **Credit/Debit Cards** (via SSLCommerz)

---

## Setting Up Auto-Debit

### Step 1: Save a Payment Method

**Before enabling Auto-Debit, save a payment method:**

#### For Mobile Wallets (bKash/Nagad/Rocket):

1. Navigate to **My Account** → **Payment Methods**
2. Click **"Add Payment Method"**
3. Select your preferred wallet
4. Follow the tokenization process:
   - Enter mobile number
   - Authorize in wallet app
   - Create agreement
5. Payment method saved successfully

#### For Credit/Debit Cards:

1. Go to **My Account** → **Payment Methods**
2. Click **"Add Card"**
3. Enter card details
4. Complete 3D Secure verification
5. Card saved securely

### Step 2: Enable Auto-Debit

1. Navigate to **My Account** → **Auto-Debit Settings**
2. Toggle **"Enable Auto-Debit"** to ON
3. Select your preferred payment method
4. Set maximum retry attempts (default: 3)
5. Click **"Save Settings"**
6. Confirmation email sent

### Configuration Options

**Payment Method:**
- Choose which saved payment method to use
- Can change anytime

**Max Retries:**
- How many times to retry if payment fails
- Options: 1, 2, 3, or 5 attempts
- Default: 3 attempts

**Notifications:**
- Email alerts for successful payments
- SMS alerts for failed payments
- Dashboard notifications

---

## How Auto-Debit Works

### Payment Schedule

**When Auto-Debit Runs:**
- Daily at 5:00 AM (Bangladesh Time)
- Processes all bills due on that day
- Automatic retry for failed payments

### Processing Steps

1. **Bill Generated:**
   - Your regular bill is created
   - Due date set (usually 7 days)
   - You receive bill notification

2. **Due Date Arrives:**
   - Auto-debit system activates
   - Checks if you have auto-debit enabled
   - Verifies payment method is valid

3. **Payment Attempted:**
   - Charges your saved payment method
   - Amount: Exact bill amount
   - No extra fees

4. **Success:**
   - Bill marked as paid
   - Service continues uninterrupted
   - Success notification sent

5. **Failure (if applicable):**
   - Retry scheduled for next day
   - Failure notification sent
   - Manual payment option provided

### Retry Logic

**If payment fails:**

**Day 1:** First attempt at 5:00 AM
- Fails → Retry scheduled

**Day 2:** Second attempt at 5:00 AM
- Fails → Retry scheduled (if retries remaining)

**Day 3:** Third attempt at 5:00 AM
- Fails → Manual payment required

**After Max Retries:**
- Auto-debit paused for this bill
- Must pay manually
- Auto-debit resumes for next bill

---

## Managing Auto-Debit

### Viewing Settings

**Check your current configuration:**
1. Go to **My Account** → **Auto-Debit Settings**
2. View status: Enabled or Disabled
3. See selected payment method
4. Check max retry count
5. View last attempt date

### Changing Payment Method

**Switch to a different payment method:**
1. Navigate to **Auto-Debit Settings**
2. Click **"Change Payment Method"**
3. Select from your saved methods
4. Confirm change
5. New method used for next payment

### Adjusting Retry Count

**Change maximum retry attempts:**
1. Go to **Auto-Debit Settings**
2. Select new retry limit (1-5)
3. Save changes
4. Applies to future payments

### Disabling Auto-Debit

**Turn off auto-debit:**
1. Navigate to **Auto-Debit Settings**
2. Toggle **"Enable Auto-Debit"** to OFF
3. Confirm action
4. Manual payments required going forward

**Note:** Already scheduled payments will still process. Disable before due date to stop payment.

### Re-enabling Auto-Debit

**Turn auto-debit back on:**
1. Go to **Auto-Debit Settings**
2. Toggle **"Enable Auto-Debit"** to ON
3. Verify payment method
4. Save settings
5. Resumes with next bill

---

## Payment History

### Viewing Auto-Debit History

**See all auto-debit transactions:**
1. Navigate to **My Account** → **Auto-Debit History**
2. View complete history with:
   - Date and time
   - Bill amount
   - Payment status
   - Payment method used
   - Transaction ID
   - Retry attempt number

### History Details

Each entry shows:

- **Successful Payments:**
  - ✅ Green checkmark
  - Transaction ID
  - Amount charged
  - Timestamp

- **Failed Payments:**
  - ❌ Red X
  - Failure reason
  - Retry count
  - Next retry time (if applicable)

### Filtering History

**Filter by:**
- Date range
- Status (Success/Failed)
- Payment method
- Amount

**Export:**
- Download as CSV
- Use for records/accounting

---

## Failed Payments

### Common Failure Reasons

**Insufficient Balance:**
- Not enough money in payment account
- Most common reason
- **Solution:** Add funds and payment will retry

**Invalid Payment Method:**
- Card expired
- Agreement cancelled
- Account closed
- **Solution:** Update payment method

**Gateway Issues:**
- Payment gateway temporarily down
- Network problems
- **Solution:** Wait for automatic retry

**Account Suspended:**
- Payment account locked/suspended
- **Solution:** Contact payment provider

### What Happens When Payment Fails

**Immediate:**
1. Failure notification sent
2. Email with details
3. SMS alert (if enabled)

**Within 24 Hours:**
4. Automatic retry scheduled
5. Retry notification sent

**After Max Retries:**
6. Manual payment required
7. Service may be limited
8. Final notice sent

### Recovering from Failed Payments

**Option 1: Wait for Retry**
- Ensure sufficient balance
- System retries automatically
- Nothing else needed

**Option 2: Pay Manually**
- Don't wait for retry
- Go to **Bills**
- Click **"Pay Now"**
- Complete payment

**Option 3: Update Payment Method**
- Add new payment method
- Update auto-debit settings
- Payment retries automatically

---

## Security and Safety

### How We Protect You

**Encryption:**
- All data encrypted in transit
- Bank-grade security (256-bit SSL)
- Payment details never stored on our servers

**Tokenization:**
- Card numbers replaced with secure tokens
- Even we can't see full card details
- Tokens stored with PCI-compliant providers

**Authentication:**
- Two-factor authentication available
- Secure login required
- Session timeout for inactive users

**Monitoring:**
- Fraud detection systems
- Unusual activity alerts
- 24/7 security monitoring

### Best Practices

**Keep Your Account Safe:**

1. **Strong Password:**
   - Use unique, complex password
   - Change regularly
   - Don't share with anyone

2. **Enable 2FA:**
   - Two-factor authentication
   - Extra security layer
   - Protect against unauthorized access

3. **Monitor Regularly:**
   - Check auto-debit history weekly
   - Review payment notifications
   - Report suspicious activity immediately

4. **Update Information:**
   - Keep email current
   - Update phone number
   - Verify payment methods regularly

5. **Secure Devices:**
   - Use antivirus software
   - Keep OS updated
   - Don't use public WiFi for payments

### What We Don't Do

**We will never:**
- Ask for your password via email
- Request payment method details by phone
- Charge without notification
- Share your data with third parties
- Store full card numbers

---

## Troubleshooting

### Common Issues

**Problem: Auto-debit not working**

**Solutions:**
1. Check if auto-debit is enabled
2. Verify payment method is active
3. Ensure sufficient balance
4. Check for expired cards
5. Review error notifications
6. Contact support if persists

**Problem: Charged wrong amount**

**Solutions:**
1. Verify the bill amount
2. Check for multiple charges
3. Review transaction history
4. Contact support with transaction ID
5. Refund processed if confirmed error

**Problem: Payment successful but bill shows unpaid**

**Solutions:**
1. Wait 5-10 minutes for system update
2. Refresh the page
3. Check transaction ID matches
4. Clear browser cache
5. Contact support with proof

**Problem: Want to cancel a scheduled payment**

**Solutions:**
1. Disable auto-debit before 5:00 AM on due date
2. Remove payment method (not recommended)
3. Contact support for urgent cancellation
4. Note: Very short notice may not stop payment

### Getting Help

**Self-Service:**
- Check FAQ section
- Review payment history
- Verify settings

**Contact Support:**
- **Email:** support@yourisp.com
- **Phone:** +880-XXX-XXXXXX
- **Hours:** 24/7 for payment issues

**What to Include:**
- Account number
- Transaction ID (if applicable)
- Error message
- Screenshots
- Date and time of issue

---

## FAQ

### General Questions

**Q: Is auto-debit mandatory?**  
A: No, it's completely optional. You can always pay manually.

**Q: Can I use auto-debit for some bills and pay others manually?**  
A: Once enabled, auto-debit applies to all bills. You can disable it anytime.

**Q: Are there any fees for auto-debit?**  
A: No, auto-debit is free. No extra charges.

**Q: What if I want to switch back to manual payments?**  
A: Simply disable auto-debit in settings. Takes effect immediately.

### Payment Questions

**Q: When exactly does auto-debit charge me?**  
A: On your bill's due date at 5:00 AM Bangladesh Time.

**Q: Can I change the payment date?**  
A: The payment date is your bill's due date. Contact support to change billing cycle.

**Q: What if I have multiple bills due the same day?**  
A: Each bill is processed separately. Ensure sufficient balance for all.

**Q: Can I set a maximum charge limit?**  
A: Not currently, but you can disable auto-debit if concerned about a particular bill.

### Failure Questions

**Q: What happens if my payment fails?**  
A: System automatically retries based on your retry settings. You're notified each time.

**Q: Will I be charged late fees if auto-debit fails?**  
A: Usually no during retry period. Contact support if charged.

**Q: Can I manually pay during the retry period?**  
A: Yes, you can pay manually anytime. Auto-debit will skip if bill is already paid.

**Q: Will auto-debit try forever if it keeps failing?**  
A: No, it stops after max retry attempts. You must then pay manually.

### Security Questions

**Q: Is my payment information safe?**  
A: Yes, we use bank-grade encryption and never store full payment details.

**Q: Can someone else access my auto-debit settings?**  
A: Only with your login credentials. Use strong password and enable 2FA.

**Q: What if I suspect unauthorized charges?**  
A: Contact support immediately. We'll investigate and freeze auto-debit if needed.

**Q: How do I know a notification is really from you?**  
A: Check sender email address, don't click suspicious links, verify in your account dashboard.

---

## Support

### Need Help?

**Auto-Debit Support:**
- **Email:** autodebit@yourisp.com
- **Phone:** +880-XXX-XXXXXX
- **Hours:** 24/7

### Report Issues

**Security Issues:**
- **Email:** security@yourisp.com
- **Response:** Immediate

**Billing Disputes:**
- **Email:** billing@yourisp.com
- **Response:** Within 24 hours

---

## Related Documentation

- [Payment Methods Guide](PAYMENT_METHODS_GUIDE.md)
- [Bkash Tokenization Guide](BKASH_TOKENIZATION_GUIDE.md)
- [Customer Dashboard Guide](CUSTOMER_DASHBOARD_GUIDE.md)
- [Billing FAQ](BILLING_FAQ.md)

---

**Document Version:** 1.0  
**Last Updated:** 2026-01-29  
**Status:** Production Ready  
**Feedback:** documentation@yourisp.com
