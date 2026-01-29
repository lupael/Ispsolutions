# Testing Guide for Mikrotik API and RADIUS Fixes

## Overview
This guide provides step-by-step instructions to test the fixes implemented for:
1. VSOL OLT Persistence
2. Mikrotik API Write Operations
3. RADIUS Request Logging

## Prerequisites
- Access to the ISP Solution application
- Admin/Super Admin credentials
- MySQL/MariaDB access for database verification
- (Optional) Access to test Mikrotik router at [REDACTED_IP]:8777

---

## Test 1: VSOL OLT Persistence

### Objective
Verify that all form fields for OLT creation are saved to the database, specifically for VSOL brand devices.

### Steps

1. **Login to Application**
   - Navigate to the ISP Solution admin panel
   - Login with admin credentials

2. **Navigate to OLT Management**
   - Go to Network > OLT Management (or similar menu)
   - Click "Add New OLT" button

3. **Fill Out Form with VSOL OLT**
   - **Name**: "Test VSOL OLT 001"
   - **Brand**: Select "VSOL" from dropdown
   - **Model**: "V1600G16"
   - **Firmware Version**: "2.1.8"
   - **IP Address**: "192.168.100.50" (use unique IP)
   - **Telnet Port**: "23"
   - **Username**: "admin"
   - **Password**: "admin123"
   - **SNMP Version**: "v2c"
   - **SNMP Community**: "public"
   - **SNMP Port**: "161"
   - **Location**: "Data Center A"
   - **Coverage Area**: "Zone 1"
   - **Total Ports**: "16"
   - **Max ONUs**: "64"
   - **Status**: "Active"
   - **Description**: "Test VSOL OLT for verification"

4. **Submit Form**
   - Click "Save OLT Device"
   - Should see success message: "OLT device created successfully"

5. **Verify in Database**
   ```sql
   SELECT 
       id, name, brand, model, firmware_version, 
       ip_address, telnet_port, location, coverage_area, 
       total_ports, max_onus, snmp_port, status
   FROM olts 
   ORDER BY id DESC 
   LIMIT 1;
   ```

### Expected Results
- ✅ Success message displayed
- ✅ All fields present in database query result
- ✅ `brand` = "vsol"
- ✅ `firmware_version` = "2.1.8"
- ✅ `telnet_port` = 23
- ✅ `coverage_area` = "Zone 1"
- ✅ `total_ports` = 16
- ✅ `max_onus` = 64
- ✅ `snmp_port` = 161

### Test Update Operation

1. **Edit the Created OLT**
   - Click "Edit" on the test OLT
   - Change **Firmware Version** to "2.2.0"
   - Change **Total Ports** to "8"
   - Click "Save"

2. **Verify Update**
   ```sql
   SELECT firmware_version, total_ports 
   FROM olts 
   WHERE name = 'Test VSOL OLT 001';
   ```

### Expected Results
- ✅ Update success message
- ✅ `firmware_version` = "2.2.0"
- ✅ `total_ports` = 8

---

## Test 2: Mikrotik API Logging and Error Reporting

### Objective
Verify that Mikrotik API operations are properly logged with detailed error information.

### Steps

1. **Check Log File Location**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Trigger API Operation**
   - Go to Mikrotik Router Management
   - Select a router or add test router:
     - IP: [REDACTED_IP]
     - API Port: 8777
     - Username: [REDACTED_CREDENTIAL]
     - Password: [REDACTED_CREDENTIAL]
   - Try to import PPP Profiles or IP Pools

3. **Monitor Logs**
   - Watch `laravel.log` file in real-time
   - Look for entries like:
     ```
     [INFO] Successfully fetched rows from MikroTik
     [INFO] Added rows to MikroTik
     [WARNING] Failed to add row to MikroTik
     ```

### Expected Log Entries

**Success Case:**
```
[timestamp] local.INFO: Successfully added row to MikroTik
{
    "router_id": 1,
    "menu": "/ppp/profile",
    "row_index": 0,
    "row_data": ["name", "local-address", "remote-address"]
}
```

**Failure Case:**
```
[timestamp] local.WARNING: Failed to add row to MikroTik
{
    "router_id": 1,
    "menu": "/ppp/profile",
    "row_index": 0,
    "row_keys": ["name", "local-address"],
    "status": 401,
    "response_body": "Unauthorized"
}
```

### Verify Sensitive Data Sanitization

Check that passwords are redacted in logs:
```
[timestamp] local.ERROR: Exception while adding row to MikroTik
{
    "router_id": 1,
    "menu": "/ppp/secret",
    "row_index": 0,
    "error": "Connection timeout"
}
```

**Note**: The log should NOT contain actual password values. If you see `"password": "***REDACTED***"`, the sanitization is working correctly.

---

## Test 3: RADIUS Request Logging

### Objective
Verify that all RADIUS authentication and accounting requests are logged with proper details.

### Part A: Authentication Logging

1. **Clear Logs (Optional)**
   ```bash
   > storage/logs/laravel.log
   ```

2. **Send Test Authentication Request**
   ```bash
   curl -X POST http://your-app-url/api/v1/radius/authenticate \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -d '{
       "username": "testuser",
       "password": "testpass"
     }'
   ```

3. **Check Logs**
   ```bash
   tail -100 storage/logs/laravel.log | grep -i radius
   ```

### Expected Authentication Logs

**Incoming Request:**
```
[timestamp] local.INFO: RADIUS authentication request received
{
    "username": "testuser",
    "client_ip": "192.168.1.100",
    "user_agent": "curl/7.68.0",
    "timestamp": "2026-01-29T10:00:00+00:00"
}
```

**Database Check:**
```
[timestamp] local.DEBUG: RADIUS authenticate: Checking credentials in database
{
    "username": "testuser",
    "connection": "radius"
}
```

**Success Result:**
```
[timestamp] local.INFO: RADIUS authenticate: User authenticated successfully
{
    "username": "testuser"
}
```

**Failure Result:**
```
[timestamp] local.WARNING: RADIUS authenticate: Authentication failed
{
    "username": "testuser"
}
```

### Part B: Accounting Logging

1. **Send Accounting Start Request**
   ```bash
   curl -X POST http://your-app-url/api/v1/radius/accounting/start \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -d '{
       "username": "testuser",
       "session_id": "test-session-12345",
       "nas_ip_address": "192.168.1.1",
       "framed_ip_address": "10.10.10.5"
     }'
   ```

2. **Check Logs**
   ```bash
   tail -100 storage/logs/laravel.log | grep -i "accounting start"
   ```

### Expected Accounting Logs

```
[timestamp] local.INFO: RADIUS accounting start request received
{
    "username": "testuser",
    "session_id": "test-session-12345",
    "nas_ip_address": "192.168.1.1",
    "framed_ip_address": "10.10.10.5",
    "client_ip": "192.168.1.100",
    "timestamp": "2026-01-29T10:05:00+00:00"
}
```

```
[timestamp] local.INFO: RADIUS accounting start successful
{
    "username": "testuser",
    "session_id": "test-session-12345",
    "client_ip": "192.168.1.100"
}
```

### Part C: Username Enumeration Prevention

1. **Test with Non-Existent User**
   ```bash
   curl -X POST http://your-app-url/api/v1/radius/authenticate \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -d '{
       "username": "nonexistentuser999",
       "password": "anypassword"
     }'
   ```

2. **Test with Existing User, Wrong Password**
   ```bash
   curl -X POST http://your-app-url/api/v1/radius/authenticate \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -d '{
       "username": "testuser",
       "password": "wrongpassword"
     }'
   ```

3. **Verify Both Return Same Error**
   - Both should return: `{"message": "Authentication failed", "authenticated": false}`
   - Response should NOT reveal if user exists or not
   - Internal logs can show the distinction, but API response should be identical

---

## Test 4: Database Schema Verification

### Verify Migration Applied

```sql
-- Check if new columns exist
DESCRIBE olts;

-- Should see these columns:
-- brand (varchar 50)
-- firmware_version (varchar 100)
-- telnet_port (int)
-- coverage_area (varchar 255)
-- total_ports (int)
-- max_onus (int)
-- snmp_port (int)
```

### Check Migration Status
```bash
php artisan migrate:status
```

Should show:
```
| Ran? | Migration |
|------|-----------|
| Yes  | 2026_01_29_093119_add_missing_fields_to_olts_table |
```

---

## Test 5: Security Verification

### Test 1: Sensitive Data Not in Logs

1. **Create PPP Secret with Password**
2. **Check logs don't contain actual password**
   ```bash
   grep -i "password.*:" storage/logs/laravel.log | grep -v "REDACTED"
   ```
   - Should return empty or only show password field names, not values

### Test 2: Exception Details Not in API Response

1. **Trigger an error** (e.g., invalid database connection)
2. **Check API response**
   - Should see: `{"success": false, "message": "Authentication error"}`
   - Should NOT see database credentials, file paths, or stack traces

### Test 3: Password Optional on Update

1. **Edit an OLT without changing password field**
2. **Verify old password still works**
3. **Verify password not set to NULL or empty**

---

## Troubleshooting

### Issue: OLT Fields Not Saving

**Check:**
1. Migration ran: `php artisan migrate:status`
2. Column exists: `DESCRIBE olts;`
3. Model fillable array includes new fields
4. Form submits correct field names

**Solution:**
```bash
php artisan migrate
php artisan config:clear
php artisan cache:clear
```

### Issue: No Logs Appearing

**Check:**
1. Log file writable: `ls -la storage/logs/laravel.log`
2. Log level: Check `.env` has `LOG_LEVEL=debug`
3. Correct log channel: Check `config/logging.php`

**Solution:**
```bash
chmod -R 775 storage/logs
php artisan config:clear
```

### Issue: RADIUS Timeout

**Remember:**
- ISP Solution does NOT implement RADIUS protocol (UDP 1812/1813)
- Requires external FreeRADIUS server
- See `RADIUS_INTEGRATION_GUIDE.md` for setup instructions

---

## Success Criteria Checklist

### VSOL OLT Persistence
- [ ] New OLT created with all fields
- [ ] Brand field saved correctly
- [ ] Firmware version saved
- [ ] Port configuration saved
- [ ] Coverage area saved
- [ ] Total ports and max ONUs saved
- [ ] Update operation preserves data
- [ ] Password optional on update

### Mikrotik API
- [ ] API operations logged
- [ ] Success operations show in logs
- [ ] Failed operations show detailed errors
- [ ] HTTP response body captured
- [ ] Sensitive data redacted in logs
- [ ] Error array structure correct

### RADIUS Logging
- [ ] Authentication requests logged
- [ ] Accounting start/update/stop logged
- [ ] Client IP addresses captured
- [ ] Timestamps in ISO8601 format
- [ ] Username enumeration prevented
- [ ] Exception details not exposed in API
- [ ] Database lookup results logged

---

## Performance Considerations

### Log Volume
- RADIUS accounting updates use `Log::debug()` to reduce volume
- Consider log rotation:
  ```bash
  # In /etc/logrotate.d/laravel
  /path/to/storage/logs/*.log {
      daily
      rotate 14
      compress
      delaycompress
      notifempty
      missingok
  }
  ```

### Database Queries
- Encrypted fields (password, snmp_community) use Laravel's encryption
- Ensure `APP_KEY` is set in `.env`
- Monitor query performance on radcheck/radacct tables

---

## Support

For issues or questions:
1. Check `storage/logs/laravel.log`
2. Review `MIKROTIK_API_RADIUS_FIXES_SUMMARY.md`
3. See `RADIUS_INTEGRATION_GUIDE.md` for RADIUS setup
4. Submit issue to GitHub repository

## Test Result Summary Template

```
Date: _____________
Tester: _____________

Test 1 - VSOL OLT Persistence: [ PASS / FAIL ]
Notes: _____________________________________________

Test 2 - Mikrotik API Logging: [ PASS / FAIL ]
Notes: _____________________________________________

Test 3 - RADIUS Logging: [ PASS / FAIL ]
Notes: _____________________________________________

Test 4 - Database Schema: [ PASS / FAIL ]
Notes: _____________________________________________

Test 5 - Security: [ PASS / FAIL ]
Notes: _____________________________________________

Overall Status: [ PASS / FAIL ]
```
