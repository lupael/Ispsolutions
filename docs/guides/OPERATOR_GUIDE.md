# Operator Guide - ISP Solution

## Role Overview

**Level**: 30  
**Access**: Own customers + Sub-operator customers in your segment

As an Operator, you manage a specific area or segment of the ISP business. You can create sub-operators, add customers, manage services, and collect payments for your assigned zone.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard](#dashboard)
3. [Managing Customers](#managing-customers)
4. [Creating Sub-Operators](#creating-sub-operators)
5. [Billing & Collections](#billing--collections)
6. [Network Operations](#network-operations)
7. [Daily Tasks](#daily-tasks)
8. [Reports](#reports)
9. [Troubleshooting](#troubleshooting)

## Getting Started

### Login

1. Access your ISP Solution URL
2. Login with credentials provided by Admin:
   - **Demo Email**: operator@ispbills.com
   - **Password**: password

### First Steps

After logging in:
1. **Change Password**: Profile → Security
2. **Review Your Zone**: Check assigned area
3. **Check Customer Limit**: View your quota
4. **Review Packages**: Available service plans
5. **Check Targets**: Monthly collection goals

## Dashboard

### Your Metrics

Dashboard shows:
- **My Customers**: Total in your zone
- **Active Connections**: Currently online
- **This Month Revenue**: Collections
- **Pending Payments**: Due amounts
- **My Target**: Monthly goal vs achieved
- **Sub-Operators**: Your team size

### Today's Tasks

Checklist of daily activities:
- [ ] New customer installations
- [ ] Payment collections
- [ ] Service complaints
- [ ] Pending activations
- [ ] Follow-up calls

## Managing Customers

### Adding New Customer

1. Click **Add Customer**
2. Fill in form:
   ```
   Customer Details:
   - Name
   - Phone Number
   - Email (optional)
   - National ID
   
   Installation:
   - Address
   - Landmark
   - GPS coordinates (use mobile)
   
   Service:
   - Select Package
   - Connection Type
   - Installation Date
   - Router Serial Number
   ```
3. Click **Create**
4. System generates username/password

### Customer Visit

When visiting customer:
1. **Verify Location**: Use GPS
2. **Document Installation**:
   - Take photos (router, cable, location)
   - Note cable length
   - Record router MAC
3. **Customer Education**:
   - Explain package details
   - Demo customer portal
   - Share support contact
4. **Collect Payment**:
   - Installation fee
   - First month advance
   - Get receipt signed

### Service Activation

After installation:
1. Go to customer profile
2. Upload installation photos
3. Verify all details
4. Click **Activate Service**
5. Test connection with customer
6. Get customer signature

### Managing Your Customers

**View Customers**:
- List view: All your customers
- Map view: Locations on map
- Filter by status/package
- Search by name/phone

**Update Customer Info**:
1. Open customer profile
2. Click **Edit**
3. Update details
4. Save changes

**Change Package**:
1. Customer profile → **Change Package**
2. Select new package
3. Choose effective date
4. Update billing
5. Confirm

**Suspend Service**:
1. Open customer
2. **Actions → Suspend**
3. Select reason:
   - Non-payment
   - Technical issue
   - Customer request
4. Add notes
5. Confirm

## Creating Sub-Operators

### When to Create Sub-Operator

Create sub-operator when:
- Managing large area
- Need local presence
- Want to delegate work
- Have permission from Admin

### Adding Sub-Operator

1. Navigate to **Team → Add Sub-Operator**
2. Fill details:
   ```
   Personal Info:
   - Name
   - Phone
   - Email
   - National ID
   
   Assignment:
   - Sub-area/Colony
   - Customer Quota
   - Commission Rate
   
   Permissions:
   - Can add customers
   - Can collect payments
   - Can suspend services
   ```
3. Click **Create**

### Managing Sub-Operators

**Monitor Performance**:
- Customers added
- Collections made
- Service quality
- Response time

**Support Sub-Operators**:
- Provide training
- Share best practices
- Resolve issues
- Review performance monthly

## Billing & Collections

### Monthly Collection Process

**Week 1 (1-7th)**:
1. Review payment due list
2. Send SMS reminders
3. Call high-value customers
4. Schedule collection visits

**Week 2-3 (8-21st)**:
1. Visit customers
2. Collect payments
3. Record in system immediately
4. Issue receipts

**Week 4 (22-30th)**:
1. Final reminders
2. Suspend non-paying customers
3. Escalate difficult cases
4. Reconcile collections

### Recording Payments

**Mobile Collection**:
1. Open customer profile (mobile app)
2. Click **Collect Payment**
3. Enter amount
4. Select method:
   - Cash
   - Mobile money
   - Bank transfer
5. Take receipt photo
6. Click **Record**

**Cash Handling**:
- Keep cash secure
- Daily deposit to bank
- Maintain collection register
- Get deposit receipts
- Update system daily

### Payment Follow-up

**Reminder Strategy**:
1. **Day -3**: SMS reminder
2. **Due Date**: Phone call
3. **Day +3**: Personal visit
4. **Day +7**: Service suspension warning
5. **Day +10**: Suspend service

**Difficult Customers**:
- Document all interactions
- Be professional always
- Escalate to Admin if needed
- Offer payment plans
- Follow company policy

## Network Operations

### Basic Troubleshooting

**Customer Cannot Connect**:
1. Check customer status (active?)
2. Verify payment status
3. Test from MikroTik:
   - Check PPPoE user exists
   - View active sessions
   - Check IP assignment
4. Physical check:
   - Router powered on?
   - Cables connected?
   - LAN lights blinking?

**Slow Internet**:
1. Check package speed
2. View bandwidth usage
3. Test speed from router
4. Check multiple devices
5. Verify no local issues

**Connection Drops**:
1. Check signal quality
2. Review session logs
3. Check cable connections
4. Test router
5. Check for interference

### Session Management

**View Active Sessions**:
1. Go to **Network → Sessions**
2. Filter by your area
3. See real-time data:
   - Online customers
   - Bandwidth usage
   - Session duration

**Disconnect Customer**:
When needed (maintenance, abuse):
1. Find customer session
2. Click **Disconnect**
3. Add reason
4. Confirm

### Field Operations

**Installation Checklist**:
- [ ] Verify customer location
- [ ] Check cable route
- [ ] Test signal strength
- [ ] Install router
- [ ] Configure connection
- [ ] Test speed
- [ ] Customer training
- [ ] Collect payment
- [ ] Get signed agreement
- [ ] Update system

**Maintenance Visit**:
- [ ] Verify complaint
- [ ] Check physical setup
- [ ] Test connection
- [ ] Fix issue
- [ ] Test with customer
- [ ] Update ticket
- [ ] Get signature

## Daily Tasks

### Morning (9 AM - 12 PM)

1. **Check Dashboard**:
   - New installations today
   - Pending activations
   - Support tickets

2. **Plan Routes**:
   - Plot customer locations
   - Optimize travel
   - Pack equipment

3. **Customer Calls**:
   - Confirm appointments
   - Follow up pending payments
   - Service quality check

### Afternoon (12 PM - 5 PM)

1. **Field Work**:
   - Customer installations
   - Maintenance visits
   - Payment collections
   - Technical support

2. **Real-time Updates**:
   - Update customer status
   - Record payments
   - Add notes/photos
   - Update tickets

### Evening (5 PM - 7 PM)

1. **Office Work**:
   - Reconcile collections
   - Update system
   - Respond to messages
   - Plan tomorrow

2. **Reporting**:
   - Daily collection report
   - Installations completed
   - Pending issues
   - Tomorrow's plan

## Reports

### Daily Reports

**Collection Report**:
- Amount collected
- Number of payments
- Payment methods
- Cash on hand

**Activity Report**:
- Customers visited
- Installations done
- Issues resolved
- Tickets closed

### Weekly Reports

**Performance Summary**:
- Total collections
- New customers
- Churned customers
- Target achievement

**Area Status**:
- Active customers
- Suspended customers
- Pending activations
- Support issues

### Monthly Reports

Admin requires:
- Complete collection summary
- Customer growth
- Service quality metrics
- Sub-operator performance
- Target achievement

## Troubleshooting

### Common Issues

**Cannot Add Customer**:
- Check customer quota
- Verify package selected
- Complete all required fields
- Check internet connection
- Clear browser cache

**Payment Not Recording**:
- Verify customer selected
- Check amount format
- Ensure stable internet
- Try again
- Contact support if fails

**Cannot Access Some Features**:
- Verify your permissions
- Check with Admin
- May need additional access
- Review user guide

### Technical Support

**First Level Support** (You):
- Connection issues
- Password reset
- Speed problems
- Basic configuration

**Escalate to Admin**:
- Router configuration
- RADIUS issues
- Billing disputes
- System errors

## Best Practices

### Customer Service

✅ **DO**:
- Be punctual for appointments
- Communicate clearly
- Document everything
- Follow up promptly
- Be professional

❌ **DON'T**:
- Miss appointments
- Make false promises
- Ignore complaints
- Share customer info
- Argue with customers

### Financial Management

✅ **Good Practices**:
- Record payments immediately
- Deposit daily
- Keep receipts organized
- Reconcile regularly
- Never mix personal/business money

### Time Management

**Prioritize Tasks**:
1. Urgent installations
2. Payment collections
3. Service complaints
4. Routine maintenance
5. Administrative work

**Use Tools**:
- Mobile app for field work
- GPS for navigation
- WhatsApp for communication
- Camera for documentation

## Mobile App Usage

### Field Operations

**Add Customer (Mobile)**:
1. Open app
2. Tap **Add Customer**
3. Fill form
4. Take photos
5. Submit

**Collect Payment (Mobile)**:
1. Search customer
2. Tap **Collect**
3. Enter amount
4. Take receipt photo
5. Submit

**Update Ticket (Mobile)**:
1. Open ticket
2. Add comment
3. Attach photos
4. Update status
5. Submit

### Offline Mode

App works offline:
- Add customers
- Record payments
- Update tickets
- Take notes

Data syncs when online.

## Commission & Incentives

### Earnings Structure

Your income includes:
- Base salary
- Per customer commission
- Collection percentage
- Target achievement bonus
- Installation fees

### Target Achievement

Monthly targets:
- New customers: X
- Collection rate: Y%
- Customer retention: Z%

Bonus tiers:
- 100-110%: Standard bonus
- 111-125%: Higher bonus
- 126%+: Top performer bonus

## Safety Guidelines

### Field Safety

- Share your location with office
- Visit customers in pairs if needed
- Keep emergency contacts handy
- Avoid night visits alone
- Report suspicious activities

### Cash Safety

- Never carry large cash
- Deposit daily
- Use secure bag
- Vary routes
- Stay alert

## Additional Resources

- [Sub-Operator Guide](SUBOPERATOR_GUIDE.md)
- [Customer Portal Guide](CUSTOMER_GUIDE.md)
- [Technical Troubleshooting](../technical/TROUBLESHOOTING.md)
- Mobile App Guide (in app)

## Contact Information

### Your Admin
Check your dashboard for Admin contact

### Technical Support
- **Phone**: [From dashboard]
- **Email**: support@[yourdomain]
- **Hours**: 9 AM - 6 PM

### Emergency
- **Critical Network Issues**: [Emergency number]
- **Security Concerns**: [Security contact]

---

**Version**: 1.0  
**Last Updated**: January 2026  
**Questions?** Contact your Admin or check the Help section in your dashboard.
