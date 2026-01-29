# SMS Payment User Guide

> **Version:** 1.0  
> **Last Updated:** 2026-01-29  
> **Audience:** ISP Operators, Sub-Operators, Administrators  
> **Reference:** REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: SMS Payment Integration

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Getting Started](#getting-started)
3. [Purchasing SMS Credits](#purchasing-sms-credits)
4. [Managing SMS Balance](#managing-sms-balance)
5. [Understanding Pricing](#understanding-pricing)
6. [Payment Methods](#payment-methods)
7. [Notifications](#notifications)
8. [Troubleshooting](#troubleshooting)
9. [FAQ](#faq)

---

## Overview

The SMS Payment system allows operators to purchase SMS credits for sending notifications to customers. The system features:

- **Tiered Pricing:** Bulk discounts for larger purchases
- **Multiple Payment Gateways:** bKash, Nagad, Rocket, SSLCommerz
- **Real-time Balance Tracking:** See your SMS usage and history
- **Automatic Notifications:** Get alerts for low balance and payment status
- **Secure Transactions:** All payments are processed through verified gateways

---

## Getting Started

### Prerequisites

- Active operator or sub-operator account
- Access to the operator dashboard
- Payment method (mobile wallet or credit card)

### Accessing SMS Payment

1. Log in to your operator panel
2. Navigate to **Dashboard** → Your SMS balance widget is displayed
3. Or go to **SMS** → **SMS Payments** from the sidebar

---

## Purchasing SMS Credits

### Quick Purchase

1. **From Dashboard:**
   - Click **"Buy SMS Credits"** button in the SMS Balance widget
   
2. **From SMS Payments Page:**
   - Navigate to **SMS** → **SMS Payments**
   - Click **"Purchase SMS Credits"** button

### Step-by-Step Purchase Process

#### Step 1: Select Quantity

Choose from preset packages or enter a custom quantity:

- **1,000 SMS** - Good for small campaigns
- **5,000 SMS** - Popular choice with 10% discount
- **10,000 SMS** - Best value with 20% discount
- **Custom Amount** - Enter any quantity you need

#### Step 2: Review Pricing

The system automatically calculates your cost:

| Quantity | Price per SMS | Total Cost | Savings |
|----------|--------------|------------|---------|
| < 5,000 | 0.50 BDT | Quantity × 0.50 | - |
| 5,000 - 9,999 | 0.45 BDT | Quantity × 0.45 | 10% |
| 10,000+ | 0.40 BDT | Quantity × 0.40 | 20% |

#### Step 3: Choose Payment Method

Select your preferred payment gateway:

- **bKash** - Most popular in Bangladesh
- **Nagad** - Government-backed mobile wallet
- **Rocket** - DBBL mobile financial service
- **SSLCommerz** - For credit/debit cards

#### Step 4: Complete Payment

1. Click **"Proceed to Payment"**
2. You'll be redirected to the payment gateway
3. Complete the payment using your mobile wallet or card
4. Wait for confirmation (usually instant)

#### Step 5: Confirmation

- Payment success notification will be sent via email
- SMS credits will be added to your balance immediately
- View transaction details in your payment history

---

## Managing SMS Balance

### Viewing Your Balance

Your SMS balance is displayed in multiple locations:

1. **Dashboard Widget:**
   - Current balance with low balance indicator
   - Quick purchase button
   - Recent usage summary

2. **SMS Payments Page:**
   - Detailed balance information
   - Usage history
   - Monthly statistics

### Balance Information

The balance widget shows:

- **Current Balance:** Total SMS credits available
- **Low Balance Threshold:** Alert level (default: 100 SMS)
- **Monthly Usage:** SMS sent this month
- **Projected Days Remaining:** Based on average usage

### Low Balance Alerts

You'll receive notifications when:

- Balance drops below your threshold (default: 100 SMS)
- Balance reaches critical level (25 SMS)
- Daily balance check (9:30 AM)

**To adjust your threshold:**
1. Go to **Settings** → **SMS Settings**
2. Set your preferred threshold
3. Save changes

---

## Understanding Pricing

### Base Rates

- **Standard Rate:** 0.50 BDT per SMS
- **5,000+ SMS:** 0.45 BDT per SMS (10% discount)
- **10,000+ SMS:** 0.40 BDT per SMS (20% discount)

### Example Calculations

**Example 1: Small Purchase**
- Quantity: 1,000 SMS
- Rate: 0.50 BDT per SMS
- Total: 500 BDT

**Example 2: Medium Purchase**
- Quantity: 5,000 SMS
- Rate: 0.45 BDT per SMS
- Total: 2,250 BDT
- Savings: 250 BDT (compared to standard rate)

**Example 3: Bulk Purchase**
- Quantity: 10,000 SMS
- Rate: 0.40 BDT per SMS
- Total: 4,000 BDT
- Savings: 1,000 BDT (compared to standard rate)

### Tips for Saving Money

1. **Buy in Bulk:** Purchase larger quantities for better discounts
2. **Plan Ahead:** Estimate your monthly SMS needs
3. **Monitor Usage:** Track your SMS consumption patterns
4. **Set Alerts:** Enable low balance notifications

---

## Payment Methods

### bKash

**How to pay with bKash:**
1. Select bKash as payment method
2. Enter your bKash account number
3. You'll receive a payment request on your bKash app
4. Enter your PIN to confirm
5. Credits added instantly

**Requirements:**
- Active bKash account
- Sufficient balance in bKash wallet
- Valid mobile number

### Nagad

**How to pay with Nagad:**
1. Select Nagad as payment method
2. Enter your Nagad account number
3. Confirm payment in Nagad app
4. Enter PIN to complete
5. Instant credit addition

### Rocket

**How to pay with Rocket:**
1. Select Rocket as payment method
2. Provide Rocket account details
3. Authorize payment
4. Credits added immediately

### SSLCommerz (Cards)

**How to pay with cards:**
1. Select SSLCommerz
2. Enter card details (Visa, Mastercard, Amex)
3. Complete 3D Secure verification
4. Payment processed instantly

**Supported Cards:**
- Visa
- Mastercard
- American Express
- Local bank cards

---

## Notifications

### Email Notifications

You'll receive emails for:

1. **Payment Success:**
   - Transaction ID
   - Amount paid
   - SMS credits added
   - New balance

2. **Payment Failed:**
   - Failure reason
   - Retry link
   - Support contact

3. **Low Balance:**
   - Current balance
   - Threshold level
   - Quick purchase link

### In-App Notifications

Dashboard notifications for:
- Payment confirmations
- Balance alerts
- Transaction updates

### Notification Settings

Configure notifications:
1. Go to **Settings** → **Notifications**
2. Toggle email notifications
3. Set notification frequency
4. Save preferences

---

## Troubleshooting

### Payment Issues

**Problem: Payment failed**

**Solutions:**
1. Check payment method balance
2. Verify account details are correct
3. Try a different payment method
4. Contact your payment provider
5. Contact our support if issue persists

**Problem: Credits not added after payment**

**Solutions:**
1. Wait 5 minutes (some payments take time)
2. Check payment history for transaction status
3. Verify payment was actually completed at gateway
4. Contact support with transaction ID

**Problem: Cannot complete payment**

**Solutions:**
1. Clear browser cache and cookies
2. Try a different browser
3. Disable ad blockers
4. Check internet connection
5. Try from mobile app

### Balance Issues

**Problem: Balance showing incorrectly**

**Solutions:**
1. Refresh the page
2. Check usage history for recent deductions
3. Verify no sub-operators using credits
4. Contact support with screenshot

**Problem: Not receiving low balance alerts**

**Solutions:**
1. Check email spam folder
2. Verify email address in profile
3. Check notification settings
4. Update low balance threshold

---

## FAQ

### General Questions

**Q: How long do SMS credits last?**  
A: SMS credits never expire. Purchase once and use anytime.

**Q: Can I get a refund?**  
A: Once credits are added, refunds are not available. Credits can be used indefinitely.

**Q: Is there a minimum purchase?**  
A: No minimum. You can purchase any quantity starting from 1 SMS.

**Q: Can I transfer credits to another operator?**  
A: No, SMS credits are tied to your account and non-transferable.

### Payment Questions

**Q: Are payments secure?**  
A: Yes, all payments go through verified payment gateways with bank-level security.

**Q: What if payment gateway is down?**  
A: Try a different payment method or wait and retry later.

**Q: Do you store my payment information?**  
A: No, we don't store payment card details. All handled by payment gateways.

**Q: Can I use multiple payment methods?**  
A: Each transaction uses one payment method, but you can use different methods for different purchases.

### Usage Questions

**Q: How is SMS usage calculated?**  
A: Each SMS sent deducts 1 credit. Long messages may count as multiple SMS.

**Q: Can sub-operators use my SMS credits?**  
A: Yes, if you enable SMS features for sub-operators, they use your balance.

**Q: How do I track SMS usage?**  
A: View detailed usage history in **SMS** → **SMS Logs**

**Q: Can I set limits for sub-operators?**  
A: Currently, sub-operators share your balance. Individual limits coming soon.

---

## Support

### Need Help?

**Email:** support@yourisp.com  
**Phone:** +880-XXX-XXXXXX  
**Support Hours:** 24/7

### Report an Issue

1. Go to **Support** → **Create Ticket**
2. Select category: "SMS Payments"
3. Describe the issue with details
4. Attach screenshots if applicable
5. Submit ticket

**Response Time:**
- Critical issues: 1 hour
- High priority: 4 hours
- Normal: 24 hours

---

## Related Documentation

- [SMS Service Guide](SMS_SERVICE_GUIDE.md)
- [Bkash Tokenization Guide](BKASH_TOKENIZATION_GUIDE.md)
- [Operator Dashboard Guide](OPERATOR_DASHBOARD_GUIDE.md)
- [API Documentation](API_DOCUMENTATION.md)

---

**Document Version:** 1.0  
**Last Updated:** 2026-01-29  
**Status:** Production Ready  
**Feedback:** Please report issues or suggestions to documentation@yourisp.com
