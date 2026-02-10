# Hotspot Self-Signup Implementation Guide

## Overview
Complete implementation of a public-facing hotspot self-signup system with OTP verification, payment integration, and automated account activation.

## 🎯 Features Implemented

### 1. OTP Service (`app/Services/OtpService.php`)
- ✅ 6-digit OTP generation
- ✅ Secure OTP storage with encryption (hashed)
- ✅ OTP expiration (5 minutes)
- ✅ Rate limiting (max 3 requests per hour per number)
- ✅ Resend cooldown (60 seconds)
- ✅ Brute force protection (max 3 verification attempts)
- ✅ IP address logging for security
- ✅ Automatic cleanup of expired OTPs
- ✅ SMS integration for sending OTPs

### 2. Database Structure
#### OTPs Table (`otps`)
```sql
- id (primary key)
- tenant_id (foreign key to tenants)
- mobile_number (string)
- otp (encrypted string)
- expires_at (timestamp)
- verified_at (nullable timestamp)
- attempts (integer, default 0)
- ip_address (string)
- timestamps
```

#### HotspotUsers Table Updates
Added fields:
- name (string, nullable)
- email (string, nullable)
- address (text, nullable)

### 3. Form Request Validation
- ✅ `RequestOtpRequest.php` - Mobile number & package validation
- ✅ `VerifyOtpRequest.php` - OTP code validation
- ✅ `CompleteRegistrationRequest.php` - Profile data validation
- ✅ `HotspotPaymentRequest.php` - Payment gateway validation

### 4. Controller (`HotspotSelfSignupController.php`)
Implements complete 10-step signup flow:
1. Show registration form
2. Request OTP
3. Show OTP verification form
4. Verify OTP
5. Show profile completion form
6. Complete registration
7. Show payment page
8. Process payment
9. Handle payment callback
10. Show success page

### 5. Views (Mobile-First Design)
All views use Tailwind CSS and are fully responsive:

#### `registration-form.blade.php`
- Mobile number input with validation
- Package selection with cards
- Terms & conditions checkbox
- Progress indicator (Step 1/4)

#### `verify-otp.blade.php`
- 6-digit OTP input
- Countdown timer (5 minutes)
- Resend OTP button with cooldown
- Auto-focus on OTP field
- Progress indicator (Step 2/4)

#### `complete-profile.blade.php`
- Name input (required)
- Email input (optional)
- Address textarea (optional)
- Package summary display
- Verified mobile number display
- Progress indicator (Step 3/4)

#### `payment.blade.php`
- Payment gateway selection (bKash, Nagad, SSLCommerz, Stripe)
- Order summary sidebar
- Security notice
- Progress indicator (Step 4/4)

#### `success.blade.php`
- Success animation
- Login credentials display (username & password)
- Copy-to-clipboard buttons
- Next steps guide
- Support contact information

#### `error.blade.php`
- Error message display
- Retry button
- Support contact information

## 🔐 Security Features

### Rate Limiting
- Max 3 OTP requests per hour per mobile number
- 60-second cooldown between resend requests
- Stored in cache (Redis recommended)

### OTP Security
- OTPs are hashed using Laravel's Hash::make()
- Never stored in plain text
- Expire after 5 minutes
- Max 3 verification attempts per OTP
- IP address logged for audit trail

### Input Validation
- Mobile number format validation (10-15 digits)
- XSS protection via Laravel's sanitization
- CSRF token validation on all forms
- SQL injection protection via Eloquent ORM

### Session Security
- Signup state stored in encrypted session
- OTP verification required before profile completion
- Mobile number verification at each step
- Session cleared after successful registration

## 📱 User Flow

```
1. User lands on /hotspot/signup
   └─> Enters mobile number & selects package
   
2. System sends OTP via SMS
   └─> User redirected to /hotspot/signup/verify-otp
   
3. User enters 6-digit OTP
   └─> System verifies OTP
   └─> If valid, redirect to /hotspot/signup/complete
   
4. User completes profile (name, email, address)
   └─> System creates hotspot account (pending_payment status)
   └─> Redirect to /hotspot/signup/payment/{user_id}
   
5. User selects payment gateway
   └─> System creates invoice
   └─> Redirects to payment gateway
   
6. User completes payment
   └─> Payment gateway calls webhook
   └─> System verifies payment
   └─> If successful, activates account
   └─> Sends SMS with credentials
   └─> Redirect to /hotspot/signup/success
   
7. User sees success page with credentials
   └─> Can copy username/password
   └─> Receives SMS confirmation
```

## 🛣️ Routes

### Public Routes (No Authentication)
```php
GET  /hotspot/signup                    - Show registration form
POST /hotspot/signup/request-otp        - Request OTP
GET  /hotspot/signup/verify-otp         - Show OTP verification form
POST /hotspot/signup/verify-otp         - Verify OTP
POST /hotspot/signup/resend-otp         - Resend OTP (AJAX)
GET  /hotspot/signup/complete           - Show profile form
POST /hotspot/signup/complete           - Complete registration
GET  /hotspot/signup/payment/{user}     - Show payment page
POST /hotspot/signup/payment/{user}     - Process payment
GET  /hotspot/signup/payment/callback   - Payment callback
GET  /hotspot/signup/success            - Success page
GET  /hotspot/signup/error              - Error page
```

## 💳 Payment Integration

### Supported Gateways
- bKash
- Nagad
- SSLCommerz
- Stripe

### Payment Flow
1. Invoice created with hotspot package price
2. Payment gateway initiated via `PaymentGatewayService`
3. User redirected to gateway
4. Gateway processes payment
5. Webhook received at `/webhooks/payment/{gateway}`
6. Payment verified and invoice marked as paid
7. Hotspot account activated
8. SMS sent with credentials

## 📧 SMS Notifications

### OTP SMS
```
Your verification code is: 123456

This code will expire in 5 minutes.

Do not share this code with anyone.
```

### Activation SMS
```
Welcome to Hotspot!

Your account is now active.
Username: HS12345678
Password: abc12345

Package: Premium Package
Valid until: 31 Jan 2026

Thank you!
```

## 🧪 Testing

### Test OTP Flow (Development)
In `config/app.php`, set `debug => true`:
- OTP will be returned in API response for testing
- SMS service can be disabled in config

### Test Scenarios

#### 1. Successful Registration
```
1. Enter mobile: 01712345678
2. Select package: Premium
3. Receive OTP: 123456
4. Verify OTP
5. Complete profile
6. Select payment gateway
7. Complete payment
8. See success page
```

#### 2. Rate Limiting
```
1. Request OTP 3 times for same number
2. 4th request should fail with error:
   "Too many OTP requests. Please try again after 1 hour."
```

#### 3. Expired OTP
```
1. Request OTP
2. Wait 6 minutes
3. Try to verify - should fail:
   "Invalid or expired OTP. Please request a new one."
```

#### 4. Invalid OTP
```
1. Request OTP
2. Enter wrong code
3. Should see remaining attempts
4. After 3 wrong attempts:
   "Maximum verification attempts exceeded. Please request a new OTP."
```

#### 5. Duplicate Registration
```
1. Complete full registration for mobile 01712345678
2. Try to register again with same number
3. Should fail:
   "This mobile number is already registered."
```

## 🔧 Configuration

### Environment Variables
```env
# SMS Configuration
SMS_ENABLED=true
SMS_GATEWAY=...
SMS_API_KEY=...

# Cache Configuration (for rate limiting)
CACHE_DRIVER=redis  # Recommended for production

# Payment Gateway Configuration
BKASH_APP_KEY=...
BKASH_APP_SECRET=...
NAGAD_MERCHANT_ID=...
SSL_STORE_ID=...
STRIPE_KEY=...
```

### Cache Configuration
For production, use Redis for OTP rate limiting:
```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),
```

## 🚀 Deployment Checklist

- [ ] Run migrations: `php artisan migrate`
- [ ] Configure SMS gateway in `.env`
- [ ] Configure payment gateways in database
- [ ] Set up Redis for caching (production)
- [ ] Test OTP sending
- [ ] Test payment flow with test credentials
- [ ] Verify SMS notifications
- [ ] Set up monitoring for failed OTPs
- [ ] Set up monitoring for failed payments
- [ ] Create scheduled task for OTP cleanup:
  ```php
  // app/Console/Kernel.php
  $schedule->call(function () {
      app(\App\Services\OtpService::class)->cleanupExpiredOtps();
  })->daily();
  ```

## 📝 Database Migrations

Run these migrations in order:
```bash
php artisan migrate --path=database/migrations/2026_01_22_100000_create_otps_table.php
php artisan migrate --path=database/migrations/2026_01_22_173925_add_profile_fields_to_hotspot_users_table.php
```

## 🎨 Customization

### Branding
To customize the views:
1. Update colors in Tailwind classes
2. Add your logo to header sections
3. Update support contact information
4. Modify SMS templates in `OtpService.php` and `HotspotSelfSignupController.php`

### OTP Settings
Modify constants in `OtpService.php`:
```php
const OTP_LENGTH = 6;                    // OTP digit count
const OTP_EXPIRY_MINUTES = 5;            // Expiration time
const MAX_ATTEMPTS = 3;                  // Max verification attempts
const RESEND_COOLDOWN_SECONDS = 60;      // Cooldown between resends
const MAX_REQUESTS_PER_HOUR = 3;         // Rate limit
```

### Package Display
Modify `registration-form.blade.php` to customize package cards:
- Add/remove package details
- Change pricing display
- Modify selection UI

## 🐛 Troubleshooting

### OTP Not Received
1. Check SMS service configuration
2. Verify mobile number format
3. Check logs: `storage/logs/laravel.log`
4. Test SMS service directly

### Payment Fails
1. Check payment gateway configuration
2. Verify test/production mode
3. Check webhook URL is accessible
4. Review payment gateway logs

### Session Expired
- Sessions expire if user takes too long
- OTP expires after 5 minutes
- User must restart signup process

### Rate Limit Issues
- Clear cache: `php artisan cache:clear`
- Adjust limits in `OtpService.php`
- Check Redis connection

## 📚 API Integration (Future Enhancement)

To add API support:
1. Create `HotspotSelfSignupApiController`
2. Return JSON responses instead of views
3. Use Laravel Sanctum for API authentication
4. Document API endpoints

## 🔄 Future Enhancements

- [ ] Social login integration (Facebook, Google)
- [ ] Email verification option
- [ ] Multiple package pricing tiers
- [ ] Promo code support
- [ ] Referral system
- [ ] Auto-renewal subscription
- [ ] Customer dashboard
- [ ] Usage statistics
- [ ] Top-up/recharge functionality
- [ ] Multi-language support

## 🔐 Hotspot User Login System

### Overview
Complete passwordless login system for hotspot users using OTP verification and MAC address-based device restriction.

### Features Implemented

#### 1. Login Controller (`HotspotLoginController.php`)
- ✅ OTP-based authentication (no password required)
- ✅ MAC address tracking for device identification
- ✅ Single device restriction (1 user = 1 active device)
- ✅ Device conflict detection and resolution
- ✅ Secure session management
- ✅ User dashboard with account information

#### 2. Database Changes
Added to `hotspot_users` table:
- `mac_address` - Stores device MAC address
- `active_session_id` - Tracks active user session
- `last_login_at` - Records last login timestamp

Migration file: `2026_01_24_030000_add_mac_address_and_session_fields_to_hotspot_users_table.php`

#### 3. Login Views (Mobile-First Design)

**`login-form.blade.php`**
- Mobile number input
- Clean, modern interface
- Link to signup page
- Secure login indicator

**`verify-otp.blade.php`**
- 6-digit OTP input
- Countdown timer
- Resend OTP functionality
- Auto-focus on input field

**`device-conflict.blade.php`**
- Device conflict warning
- Current session information
- Force login option
- Security notices

**`dashboard.blade.php`**
- User account overview
- Package details and expiration
- Session information
- Quick access to support

#### 4. Login Routes

```php
GET  /hotspot/login                    - Show login form
POST /hotspot/login/request-otp        - Request login OTP
GET  /hotspot/login/verify-otp         - Show OTP verification
POST /hotspot/login/verify-otp         - Verify OTP and login
GET  /hotspot/login/device-conflict    - Show device conflict
POST /hotspot/login/force-login        - Force login on new device
GET  /hotspot/dashboard                - User dashboard
POST /hotspot/logout                   - Logout user
```

### User Login Flow

```
1. User visits /hotspot/login
   └─> Enters registered mobile number
   
2. System validates mobile number
   └─> Checks if user exists and is active
   └─> Checks account expiration
   └─> Sends OTP via SMS
   
3. User enters OTP code
   └─> System verifies OTP
   └─> Checks for active session on different device
   
4a. If no device conflict:
    └─> Creates new session
    └─> Updates MAC address and session ID
    └─> Redirects to dashboard
    
4b. If device conflict detected:
    └─> Shows device conflict page
    └─> User can force login (logs out other device)
    └─> Or cancel and go back
    
5. Dashboard displays:
    └─> Account status and details
    └─> Package information
    └─> Session information
    └─> Days remaining
    └─> Support options
```

### MAC Address Detection

The system uses a device fingerprint based on:
- IP address
- User agent string

This creates a unique identifier for each device. In production hotspot systems, the actual MAC address would be provided by the RADIUS server or router.

Format: `XX:XX:XX:XX:XX:XX` (uppercase, colon-separated)

### Device Restriction Logic

1. **Single Device Policy**: Only one active session per user
2. **Conflict Detection**: Checks MAC address on login
3. **Auto Logout**: Logging in on new device logs out previous device
4. **Session Validation**: Verifies session ID and MAC address on each request

### Security Features

#### Session Management
- Unique session IDs using UUID
- Session stored in encrypted Laravel session
- MAC address verification on dashboard access
- Automatic session invalidation when logged out

#### OTP Security
- Same security as signup (hashed, expiring, rate-limited)
- 5-minute expiration
- Max 3 verification attempts
- 60-second resend cooldown

#### Device Tracking
- MAC address indexed in database
- Session ID indexed for fast lookups
- Last login timestamp tracked
- Clear session on logout

### API Methods

#### HotspotUser Model Methods

```php
// Check if user has active session on different device
hasActiveSessionOnDifferentDevice(string $currentMacAddress): bool

// Update login session information
updateLoginSession(string $macAddress, string $sessionId): void

// Clear active session
clearSession(): void
```

### Testing Login System

#### 1. Normal Login
```
1. Go to /hotspot/login
2. Enter registered mobile: 01712345678
3. Receive OTP code
4. Enter OTP
5. Should redirect to dashboard
```

#### 2. Device Restriction
```
1. Login on Device A
2. Try to login with same account on Device B
3. Should see device conflict warning
4. Choose "Force Login"
5. Device A should be logged out
6. Device B should be logged in
```

#### 3. Session Validation
```
1. Login successfully
2. Open dashboard
3. Manually clear active_session_id in database
4. Refresh dashboard
5. Should redirect to login (session invalidated)
```

#### 4. Expired Account
```
1. Set expires_at to past date
2. Try to login
3. Should see "account expired" error
```

### Configuration

No additional configuration needed. The system uses existing:
- OTP Service for OTP generation
- SMS Service for OTP delivery
- Session configuration for session storage

### Monitoring

Track these metrics:
- Login success/failure rate
- Device conflicts per day
- Average session duration
- Active concurrent users

### Troubleshooting

**User can't login:**
- Check if account status is 'active'
- Verify account hasn't expired
- Check OTP rate limiting
- Review SMS delivery logs

**Device conflict issues:**
- Verify MAC address generation logic
- Check session storage (Redis/database)
- Review session configuration

**Session expires too quickly:**
- Check Laravel session configuration
- Verify session driver (file/database/redis)
- Check session lifetime setting

## 📞 Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Review error messages carefully
- Test in development environment first
- Contact technical support with error details

## ✅ Implementation Complete

All components have been implemented and are ready for deployment:

### Self-Signup System
- ✅ OTP Service with full security
- ✅ Database migrations
- ✅ Form validation classes
- ✅ Complete controller logic
- ✅ 6 responsive view files
- ✅ Public routes configured
- ✅ Payment gateway integration
- ✅ SMS notifications
- ✅ Security features
- ✅ Error handling

### Login System (NEW)
- ✅ Passwordless OTP-based login
- ✅ MAC address tracking
- ✅ Single device restriction
- ✅ Device conflict resolution
- ✅ User dashboard
- ✅ Session management
- ✅ 4 responsive login views
- ✅ Login routes configured

**Total Files Created:** 25+
**Total Lines of Code:** ~5,000+
**Test Coverage:** Ready for manual and automated testing

### Quick Deployment Steps

1. Run migrations:
   ```bash
   php artisan migrate
   ```

2. Configure environment:
   ```env
   SMS_ENABLED=true
   SMS_GATEWAY=...
   CACHE_DRIVER=redis
   ```

3. Test signup flow:
   - Visit `/hotspot/signup`
   - Complete registration
   - Make payment
   - Receive credentials

4. Test login flow:
   - Visit `/hotspot/login`
   - Enter mobile number
   - Verify OTP
   - Access dashboard

5. Test device restriction:
   - Login on one device
   - Try login on another
   - Verify device conflict handling
