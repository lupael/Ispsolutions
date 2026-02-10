# Phase 2 Features User Guide
## SMS Payments, Auto-Debit, Subscriptions & Bkash Tokenization

> **Last Updated:** 2026-01-29  
> **Version:** 1.0  
> **Reference:** REFERENCE_SYSTEM_QUICK_GUIDE.md Phase 2 Implementation

---

## 📚 Table of Contents

1. [SMS Payment System](#1-sms-payment-system)
2. [Auto-Debit System](#2-auto-debit-system)
3. [Subscription Payments](#3-subscription-payments)
4. [Bkash Tokenization (Saved Payment Methods)](#4-bkash-tokenization-saved-payment-methods)
5. [Troubleshooting](#5-troubleshooting)
6. [FAQs](#6-faqs)

---

## 1. SMS Payment System

### Overview
The SMS Payment System allows operators to purchase SMS credits for sending messages to customers. Credits are purchased through secure payment gateways and added to your account instantly.

### How to Purchase SMS Credits

#### Step 1: Navigate to SMS Payments
1. Log in to your operator panel
2. Navigate to **SMS Payments** from the main menu
3. Click **Purchase SMS Credits** button

#### Step 2: Select Package
Choose from our tiered pricing packages:

| Package | Quantity | Price | Rate per SMS | Savings |
|---------|----------|-------|--------------|---------|
| **Basic** | 1,000 SMS | ৳500 | ৳0.50 | - |
| **Standard** | 5,000 SMS | ৳2,250 | ৳0.45 | 10% off |
| **Premium** | 10,000 SMS | ৳4,000 | ৳0.40 | 20% off |
| **Custom** | 100-100,000 | Variable | ৳0.50 | No discount |

**💡 Tip:** Purchase larger packages to save up to 20% per SMS!

#### Step 3: Select Payment Method
Choose your preferred payment gateway:
- **bKash** - Mobile payment (recommended)
- **Nagad** - Mobile payment
- **Rocket** - Mobile payment
- **SSLCommerz** - Card and bank payment

#### Step 4: Complete Payment
1. Review your order summary
2. Click **Proceed to Payment**
3. You'll be redirected to the payment gateway
4. Complete the payment process
5. Credits are added instantly upon successful payment

### Features

#### Balance Tracking
- View current balance in real-time
- Track purchase history
- Monitor usage statistics
- See monthly consumption trends

#### Low Balance Warnings
- Set custom low balance threshold
- Receive email notifications
- Dashboard warnings when balance is low

#### Payment History
- View all SMS credit purchases
- Track payment status
- Download receipts
- Filter by date range

### SMS Balance Dashboard

Your SMS balance is displayed in multiple places:
- **Main Dashboard** - SMS Balance widget (coming soon)
- **SMS Payments Page** - Current balance in header
- **Purchase Page** - Balance projection after purchase

### Usage Tips

1. **Purchase in Bulk** - Save up to 20% with larger packages
2. **Monitor Usage** - Check monthly statistics regularly
3. **Set Alerts** - Configure low balance threshold
4. **Plan Ahead** - Purchase before running out to avoid disruptions

---

## 2. Auto-Debit System

### Overview
Auto-Debit automatically charges your customers' saved payment methods on their bill due date, eliminating the need for manual bill payments and reducing overdue accounts.

### Setting Up Auto-Debit (Customer View)

#### Step 1: Access Settings
1. Log in to your customer portal
2. Navigate to **Auto-Debit Settings**

#### Step 2: Enable Auto-Debit
1. Toggle **Enable Auto-Debit** switch
2. Select your preferred payment method
3. Configure maximum retries (default: 3)
4. Click **Save Settings**

### Features

#### Automatic Payments
- Bills are automatically paid on due date
- No manual intervention required
- Reduces overdue accounts
- Improves cash flow

#### Retry Logic
- Automatic retry on failed payments
- Configurable retry attempts (1-5)
- Intelligent retry scheduling
- Retry count tracking

#### Payment History
- View all auto-debit attempts
- Track successful payments
- See failed payment reasons
- Download payment reports

### Status Overview Dashboard

The Auto-Debit settings page shows:
- **Status** - Enabled or Disabled
- **Payment Method** - Currently configured gateway
- **Retry Count** - Current retry attempts vs. maximum

### How Auto-Debit Works

1. **Due Date Detection**
   - System checks for bills due today
   - Only processes enabled customers
   - Runs daily at 5:00 AM

2. **Payment Processing**
   - Attempts payment via saved method
   - Uses saved Bkash token or selected gateway
   - Processes payment securely

3. **Success/Failure Handling**
   - **Success:** Balance updated, receipt sent
   - **Failure:** Retry scheduled, notification sent

4. **Retry Attempts**
   - Automatic retry on next run
   - Stops after max attempts reached
   - Admin can manually reset retry count

### Configuration Options

| Option | Description | Default |
|--------|-------------|---------|
| **Enabled** | Turn auto-debit on/off | Disabled |
| **Payment Method** | bKash, Nagad, Rocket, etc. | None |
| **Max Retries** | Number of retry attempts | 3 |

### Best Practices

1. **Save Payment Method First** - Set up Bkash tokenization before enabling
2. **Ensure Sufficient Balance** - Keep payment method funded
3. **Monitor Notifications** - Check for failed payment alerts
4. **Update Payment Method** - Keep payment details current

### For Operators

#### Monitoring Auto-Debit
- View failed payment reports
- Monitor success rates
- Identify problematic customers
- Manually trigger retry attempts

#### Admin Functions
- Reset retry counts
- Manually process payments
- View system-wide statistics
- Export payment reports

---

## 3. Subscription Payments

### Overview
Subscription Payments allow platform operators to subscribe to monthly or annual billing plans for using the ISP management platform.

### Available Plans

Plans are displayed on the subscription page with:
- Monthly/Annual pricing
- Feature inclusions
- Customer limits
- Storage limits
- Support level

### Subscribing to a Plan

#### Step 1: View Plans
1. Log in to your operator panel
2. Navigate to **Subscriptions**
3. Review available plans

#### Step 2: Select Plan
1. Click **Subscribe** on your chosen plan
2. Review plan details
3. Confirm subscription

#### Step 3: Payment
1. Select payment method
2. Review billing period
3. Complete payment
4. Subscription activates immediately

### Features

#### Plan Management
- View current subscription
- See renewal dates
- Check usage limits
- Upgrade/downgrade options

#### Billing
- Monthly billing cycle
- Automatic renewal
- Invoice generation
- Payment history

#### Invoices
- Download PDF invoices
- Email invoice delivery
- View payment history
- Track billing periods

### Renewal Process

1. **7 Days Before** - Renewal reminder email
2. **Due Date** - Auto-debit processes payment
3. **On Success** - Subscription renewed
4. **On Failure** - Notification sent, grace period begins

---

## 4. Bkash Tokenization (Saved Payment Methods)

### Overview
Bkash Tokenization allows you to save your bKash payment details for quick, one-click payments without entering details each time.

### Setting Up Saved Payment Method

#### Step 1: Add Payment Method
1. Navigate to **Payment Methods**
2. Click **Add Payment Method**
3. Enter your bKash mobile number (e.g., 01712345678)
4. Accept terms and conditions
5. Click **Continue to bKash**

#### Step 2: Authorize with bKash
1. You'll be redirected to bKash
2. Log in to your bKash account
3. Review the authorization request
4. Approve the connection
5. You'll be redirected back

#### Step 3: Confirmation
- Success page displays your saved method
- View in **Payment Methods** list
- Ready for one-click payments

### Managing Payment Methods

#### View Saved Methods
Navigate to **Payment Methods** to see:
- Mobile number
- Status (Active/Pending/Cancelled/Expired)
- Agreement ID
- Creation date
- Number of tokens

#### Remove Payment Method
1. Find the payment method to remove
2. Click the **trash icon**
3. Confirm removal
4. Method is deactivated

### Using Saved Payment Methods

Once set up, use your saved payment method for:
- **SMS Credit Purchases** - One-click checkout
- **Subscription Payments** - Automatic renewal
- **Auto-Debit** - Automatic bill payments
- **Manual Payments** - Quick payment option

### Features

#### Security
- Payment details stored securely with bKash
- We never see your bKash PIN
- Encrypted token storage
- Secure API communication

#### Convenience
- No need to enter details each time
- Quick one-click payments
- Save multiple methods
- Easy to manage

#### Control
- View all saved methods
- Remove anytime
- No charges without approval
- Full audit trail

### Status Indicators

| Status | Meaning |
|--------|---------|
| 🟢 **Active** | Ready for payments |
| 🟡 **Pending** | Awaiting bKash approval |
| 🔴 **Expired** | Token expired, re-add |
| ⚫ **Cancelled** | Removed by you |

---

## 5. Troubleshooting

### SMS Payments

**Problem:** Payment fails to process  
**Solution:**
- Check your internet connection
- Try a different payment method
- Ensure sufficient funds
- Contact support if issue persists

**Problem:** Credits not added after payment  
**Solution:**
- Wait 5-10 minutes for processing
- Check payment status in history
- Contact support with transaction ID

### Auto-Debit

**Problem:** Auto-debit not working  
**Solution:**
- Verify auto-debit is enabled
- Check payment method is active
- Ensure sufficient funds
- Verify not at max retry limit

**Problem:** Payment keeps failing  
**Solution:**
- Check payment method balance
- Update payment method details
- Contact your payment provider
- Manually pay and reset retries

### Bkash Tokenization

**Problem:** Can't add payment method  
**Solution:**
- Verify mobile number format (01XXXXXXXXX)
- Ensure you have bKash account
- Check internet connection
- Try different browser

**Problem:** bKash authorization fails  
**Solution:**
- Complete bKash setup first
- Ensure bKash account is active
- Check you have sufficient balance
- Try again in a few minutes

**Problem:** Payment method shows as expired  
**Solution:**
- Remove old method
- Add new payment method
- Re-authorize with bKash

---

## 6. FAQs

### SMS Payments

**Q: Do SMS credits expire?**  
A: No, SMS credits never expire. Use them whenever you need.

**Q: Can I get a refund for unused credits?**  
A: Please contact support for refund requests. Refunds are subject to our refund policy.

**Q: What's the minimum purchase?**  
A: Minimum purchase is 100 SMS credits.

**Q: How quickly are credits added?**  
A: Credits are added instantly upon successful payment.

### Auto-Debit

**Q: Can customers disable auto-debit?**  
A: Yes, customers can enable/disable auto-debit at any time from their settings.

**Q: What happens if payment fails?**  
A: System will retry based on configured retry count. Customer is notified of failures.

**Q: How many times will it retry?**  
A: Default is 3 retries, configurable from 1-5 attempts.

**Q: When does auto-debit run?**  
A: Daily at 5:00 AM for all eligible customers with due bills.

### Subscriptions

**Q: Can I change plans mid-cycle?**  
A: Yes, you can upgrade anytime. Pro-rated adjustments apply.

**Q: What happens if payment fails?**  
A: You get a grace period to update payment. Service continues during grace period.

**Q: Can I cancel anytime?**  
A: Yes, cancel anytime. No charges after current period ends.

### Bkash Tokenization

**Q: Is my payment information safe?**  
A: Yes, all information is securely stored with bKash using industry-standard encryption.

**Q: Do I need to enter details every time?**  
A: No, once set up, payments are one-click.

**Q: Can I have multiple payment methods?**  
A: Currently, you can save multiple bKash numbers.

**Q: Are there any fees?**  
A: No fees for saving payment methods. Standard transaction fees apply to payments.

**Q: What if my number changes?**  
A: Remove old method and add new one with your new number.

---

## 📞 Support

### Need Help?

- **Documentation:** Check this guide and other documentation
- **Email Support:** support@example.com
- **Phone:** +880-XXX-XXX-XXXX
- **Live Chat:** Available on dashboard

### Reporting Issues

When reporting issues, please include:
- Your user ID/operator ID
- Date and time of issue
- Transaction ID (if applicable)
- Screenshot of error message
- Steps to reproduce

---

## 🔄 Updates

This guide is regularly updated. Check back for:
- New features
- Updated procedures
- Additional tips
- FAQ additions

**Version History:**
- **v1.0** (2026-01-29) - Initial release covering SMS Payments, Auto-Debit, Subscriptions, and Bkash Tokenization

---

**Document Version:** 1.0  
**Last Updated:** 2026-01-29  
**Next Review:** 2026-02-15
