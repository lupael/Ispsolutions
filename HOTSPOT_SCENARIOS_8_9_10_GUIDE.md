# Hotspot Login Scenarios 8, 9, 10 - Implementation Guide

## Overview

This document describes the implementation of three advanced scenarios for the Intelligent Hotspot Login Detection feature:

- **Scenario 8**: Link login (public access)
- **Scenario 9**: Logout tracking
- **Scenario 10**: Cross-radius server lookup (federated authentication)

## Prerequisites

Before using these features, run the migrations:

```bash
php artisan migrate
```

This will create:
- `hotspot_login_logs` table
- `operator_registry` table

## Scenario 8: Link Login (Public Access)

### Purpose
Generate temporary access links for guest users without requiring authentication.

### Features
- Generates unique, time-limited access tokens
- Configurable duration (default: 60 minutes)
- No authentication required
- Tracks usage in `hotspot_login_logs` table
- Auto-expires after time limit

### API Endpoints

#### Generate Link (Admin Only)
```http
POST /hotspot/generate-link
Authorization: Bearer {token}
Content-Type: application/json

{
  "duration_minutes": 60,
  "metadata": {
    "purpose": "Guest WiFi",
    "location": "Conference Room A"
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "scenario": "link_login",
    "link_token": "abc123...",
    "session_id": "uuid...",
    "expires_at": "2024-01-24T16:30:00",
    "duration_minutes": 60,
    "login_url": "https://example.com/hotspot/login/link/abc123...",
    "login_log_id": 1,
    "message": "Temporary access link generated successfully"
  }
}
```

#### Access Link (Public)
```http
GET /hotspot/login/link/{token}
```

Users can access this URL directly to get internet access without authentication.

### Usage Example

**Step 1: Admin generates link**
```php
use App\Services\HotspotScenarioDetectionService;

$scenarioService = app(HotspotScenarioDetectionService::class);

$result = $scenarioService->generateLinkLogin(
    tenantId: 1,
    durationMinutes: 120, // 2 hours
    metadata: [
        'purpose' => 'Conference Guest WiFi',
        'location' => 'Building A',
    ]
);

// Share the login_url with guests
$loginUrl = $result['login_url'];
```

**Step 2: Guest clicks the link**
- Redirected to link dashboard
- Auto-connected (no auth required)
- Session expires after configured duration

### Link Dashboard Features
- Real-time countdown timer
- Progress bar showing session usage
- Session details (ID, expiry time)
- Manual disconnect option

## Scenario 9: Logout Tracking

### Purpose
Properly track user logouts with session duration, RADIUS accounting updates, and cleanup.

### Features
- Updates `hotspot_login_logs` with logout time and duration
- Updates RADIUS `radacct` table with stop time
- Clears active sessions from memory
- Logs all logout events for auditing

### API Endpoints

#### Logout
```http
POST /hotspot/logout
Content-Type: application/json
```

**Response:**
```json
{
  "scenario": "logout_success",
  "success": true,
  "session_id": "uuid...",
  "login_at": "2024-01-24T14:30:00",
  "logout_at": "2024-01-24T15:45:00",
  "duration": 4500,
  "message": "Logout successful"
}
```

### Usage Example

**Programmatic logout:**
```php
use App\Services\HotspotScenarioDetectionService;

$scenarioService = app(HotspotScenarioDetectionService::class);

$result = $scenarioService->handleLogout(
    sessionId: 'abc-123-def',
    username: 'user001',
    sessionData: [
        'input_octets' => 1048576,    // 1 MB downloaded
        'output_octets' => 524288,     // 512 KB uploaded
        'terminate_cause' => 'User-Request',
    ]
);
```

### What Gets Updated

1. **hotspot_login_logs table:**
   - `logout_at`: Current timestamp
   - `session_duration`: Seconds between login and logout
   - `status`: Changed to 'completed'

2. **radacct table:**
   - `acctstoptime`: Current timestamp
   - `acctsessiontime`: Session duration in seconds
   - `acctinputoctets`: Downloaded bytes
   - `acctoutputoctets`: Uploaded bytes
   - `acctterminatecause`: Reason for disconnect

3. **hotspot_users table:**
   - `active_session_id`: Set to NULL
   - `mac_address`: Optionally cleared

## Scenario 10: Cross-Radius Server Lookup (Federated Authentication)

### Purpose
Support multi-operator environments where users can authenticate via their home operator's RADIUS server.

### Features
- Detects realm in username (user@realm format)
- Queries central `operator_registry` for home operator
- Redirects to home operator's portal
- Logs federated login attempts
- Supports roaming agreements between operators

### Database Setup

First, add operators to the registry:

```sql
INSERT INTO operator_registry (name, realm, portal_url, radius_server, radius_port, radius_secret, is_active)
VALUES 
  ('ISP Alpha', 'alpha.com', 'https://portal.alpha.com', '192.168.1.1', 1812, 'secret123', 1),
  ('ISP Beta', 'beta.net', 'https://portal.beta.net', '192.168.2.1', 1812, 'secret456', 1),
  ('ISP Gamma', 'gamma.org', 'https://portal.gamma.org', '192.168.3.1', 1812, 'secret789', 1);
```

### API Endpoints

#### Federated Login
```http
POST /hotspot/login/federated
Content-Type: application/json

{
  "username": "john@alpha.com"
}
```

**Response (Local User):**
```json
{
  "success": true,
  "federated": false,
  "message": "User authenticated locally"
}
```

**Response (Federated User):**
```json
{
  "success": true,
  "federated": true,
  "redirect_url": "https://portal.alpha.com/hotspot/login?username=john@alpha.com&realm=alpha.com&federated=true",
  "message": "User belongs to another operator. Redirecting to home operator."
}
```

### Usage Example

**Step 1: User enters username with realm**
```
Username: john@alpha.com
```

**Step 2: System performs lookup**
```php
use App\Services\HotspotScenarioDetectionService;

$scenarioService = app(HotspotScenarioDetectionService::class);

$result = $scenarioService->crossRadiusLookup(
    username: 'john@alpha.com',
    tenantId: 1
);

if ($result['federated']) {
    // Redirect to home operator
    return redirect()->away($result['redirect_url']);
}

// Continue with local authentication
```

**Step 3: User redirected to home operator**
- Automatic redirect to home operator portal
- Home operator authenticates user
- Session established under home operator

### Federated Login Flow

```
┌─────────────┐           ┌─────────────┐           ┌─────────────┐
│   Visitor   │           │   ISP B     │           │   ISP A     │
│             │           │  (Visited)  │           │   (Home)    │
└──────┬──────┘           └──────┬──────┘           └──────┬──────┘
       │                         │                         │
       │  Login: user@ispa.com   │                         │
       ├────────────────────────>│                         │
       │                         │                         │
       │    Lookup user@ispa.com │                         │
       │                         ├─ Check operator_registry│
       │                         │                         │
       │    Redirect to ISP A    │                         │
       │<────────────────────────┤                         │
       │                         │                         │
       │       Login at ISP A    │                         │
       ├─────────────────────────┼────────────────────────>│
       │                         │                         │
       │    Authenticated        │                         │
       │<────────────────────────┼─────────────────────────┤
       │                         │                         │
```

## SMS Notifications (Optional)

The system includes optional SMS notification methods for:

### Device Change Notification
```php
protected function sendDeviceChangeSms(
    HotspotUser $user, 
    string $oldMac, 
    string $newMac
): void
```

**Message:**
> "Security Alert: Your device MAC address has changed from XX:XX:XX:XX:XX:XX to YY:YY:YY:YY:YY:YY. If this was not you, please contact support immediately."

### Account Suspension Notification
```php
protected function sendSuspensionSms(
    HotspotUser $user, 
    string $reason
): void
```

**Message:**
> "Your account has been suspended. Reason: {reason}. Please contact support for assistance."

### Successful Login Notification
```php
protected function sendLoginSuccessSms(
    HotspotUser $user, 
    string $macAddress
): void
```

**Message:**
> "Login successful! Your device (XX:XX:XX:XX:XX:XX) is now connected. Welcome back!"

### Enable SMS Notifications

To enable SMS notifications, configure the SMS gateway in your `.env`:

```env
SMS_GATEWAY=twilio
TWILIO_SID=your_sid
TWILIO_TOKEN=your_token
TWILIO_FROM=+1234567890
```

Or use a local SMS gateway:

```env
SMS_GATEWAY=maestro
MAESTRO_API_KEY=your_key
MAESTRO_SENDER_ID=YourBrand
```

## Security Considerations

### Link Login Security
- Tokens are 64 characters long (random string)
- Single-use recommended (can be enforced with additional logic)
- Time-limited (auto-expire)
- Tokens stored with tenant isolation
- Rate limiting recommended for token generation

### Logout Security
- Session ID must match to logout
- RADIUS updates are atomic (transaction-based)
- Failed RADIUS updates are logged but don't block logout

### Federated Authentication Security
- Realm validation against whitelist
- HTTPS required for redirects
- RADIUS shared secrets are encrypted
- Operator registry access is restricted
- Audit logs for all federated attempts

## Database Schema

### hotspot_login_logs
```sql
CREATE TABLE hotspot_login_logs (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT,
    hotspot_user_id BIGINT NULL,
    network_user_id BIGINT NULL,
    username VARCHAR(255) NULL,
    mac_address VARCHAR(50) NULL,
    ip_address VARCHAR(50) NULL,
    session_id VARCHAR(255) UNIQUE,
    login_type VARCHAR(20) DEFAULT 'normal',
    scenario VARCHAR(50) NULL,
    login_at TIMESTAMP NULL,
    logout_at TIMESTAMP NULL,
    session_duration INT DEFAULT 0,
    device_fingerprint TEXT NULL,
    user_agent TEXT NULL,
    nas_ip_address VARCHAR(50) NULL,
    calling_station_id VARCHAR(50) NULL,
    -- Link login fields
    link_token VARCHAR(100) UNIQUE NULL,
    link_expires_at TIMESTAMP NULL,
    is_link_login BOOLEAN DEFAULT FALSE,
    -- Federated login fields
    home_operator_id BIGINT NULL,
    federated_login BOOLEAN DEFAULT FALSE,
    redirect_url VARCHAR(255) NULL,
    status VARCHAR(20) DEFAULT 'active',
    failure_reason TEXT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    -- Indexes
    INDEX idx_tenant_status (tenant_id, status, login_at),
    INDEX idx_tenant_mac (tenant_id, mac_address, status),
    INDEX idx_tenant_username (tenant_id, username, login_at)
);
```

### operator_registry
```sql
CREATE TABLE operator_registry (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    realm VARCHAR(255) UNIQUE,
    portal_url VARCHAR(255),
    radius_server VARCHAR(255) NULL,
    radius_port INT DEFAULT 1812,
    radius_secret VARCHAR(255) NULL,
    description TEXT NULL,
    contact_email VARCHAR(255) NULL,
    contact_phone VARCHAR(255) NULL,
    country VARCHAR(2) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    metadata JSON NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_realm (realm),
    INDEX idx_active (is_active)
);
```

## Testing

### Test Scenario 8: Link Login

```bash
# Generate link
curl -X POST http://localhost/hotspot/generate-link \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"duration_minutes": 30}'

# Access link (use token from response)
curl -L http://localhost/hotspot/login/link/{token}
```

### Test Scenario 9: Logout

```bash
# Login first, then logout
curl -X POST http://localhost/hotspot/logout \
  -H "Cookie: laravel_session={session}"
```

### Test Scenario 10: Federated Login

```bash
# Local user (should authenticate locally)
curl -X POST http://localhost/hotspot/login/federated \
  -H "Content-Type: application/json" \
  -d '{"username": "localuser"}'

# Federated user (should redirect)
curl -X POST http://localhost/hotspot/login/federated \
  -H "Content-Type: application/json" \
  -d '{"username": "john@alpha.com"}'
```

## Monitoring and Analytics

### Query Active Link Logins
```sql
SELECT COUNT(*) as active_links
FROM hotspot_login_logs
WHERE is_link_login = TRUE
  AND status = 'active'
  AND link_expires_at > NOW();
```

### Query Logout Statistics
```sql
SELECT 
    DATE(logout_at) as date,
    COUNT(*) as total_logouts,
    AVG(session_duration) as avg_duration_seconds,
    SUM(session_duration) as total_duration_seconds
FROM hotspot_login_logs
WHERE logout_at IS NOT NULL
  AND tenant_id = ?
GROUP BY DATE(logout_at)
ORDER BY date DESC;
```

### Query Federated Login Attempts
```sql
SELECT 
    username,
    home_operator_id,
    redirect_url,
    status,
    created_at
FROM hotspot_login_logs
WHERE federated_login = TRUE
  AND tenant_id = ?
ORDER BY created_at DESC
LIMIT 100;
```

## Troubleshooting

### Link Login Not Working
1. Check if token exists and hasn't expired
2. Verify `hotspot_login_logs` table exists
3. Check route is registered: `php artisan route:list --path=hotspot/login/link`

### Logout Not Updating RADIUS
1. Verify RADIUS connection settings
2. Check `radacct` table exists
3. Review logs: `tail -f storage/logs/laravel.log`

### Federated Login Not Redirecting
1. Verify `operator_registry` table has entries
2. Check realm matches exactly (case-sensitive)
3. Ensure `is_active = 1` for operator

## Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Enable debug mode: `APP_DEBUG=true` in `.env`
- Review database migrations: `php artisan migrate:status`

## Changelog

### Version 1.0.0 (2024-01-24)
- Initial implementation of scenarios 8, 9, 10
- Created `HotspotLoginLog` model
- Created `hotspot_login_logs` and `operator_registry` tables
- Added SMS notification methods
- Created link dashboard view
- Updated routes and controller methods
