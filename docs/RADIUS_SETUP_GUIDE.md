# RADIUS Setup Guide

This guide explains how to configure and troubleshoot RADIUS database connectivity for PPP and Hotspot logging.

## Quick Start

### Installation in 3 Steps

1. **Configure Environment Variables**
   
   Add these to your `.env` file:
   ```env
   RADIUS_DB_CONNECTION=mysql
   RADIUS_DB_HOST=127.0.0.1
   RADIUS_DB_PORT=3306
   RADIUS_DB_DATABASE=radius
   RADIUS_DB_USERNAME=radius
   RADIUS_DB_PASSWORD=your_secure_password
   ```

2. **Create RADIUS Database**
   
   ```bash
   mysql -u root -p
   CREATE DATABASE radius CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'radius'@'localhost' IDENTIFIED BY 'your_secure_password';
   GRANT ALL PRIVILEGES ON radius.* TO 'radius'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

3. **Install RADIUS Tables**
   
   ```bash
   # Install RADIUS tables
   php artisan radius:install
   
   # Verify installation
   php artisan radius:install --check
   ```

That's it! RADIUS is now ready to use.

## Overview

The ISP Solution uses a separate RADIUS database to store accounting data for PPP (PPPoE) and Hotspot connections. This separation allows:
- Better performance by isolating high-volume accounting data
- Compatibility with standard RADIUS server configurations
- Easier integration with external RADIUS systems

## Database Tables

The RADIUS database includes the following tables:
- `radcheck` - User authentication credentials
- `radreply` - User-specific RADIUS reply attributes
- `radacct` - Accounting records for all sessions (PPP and Hotspot)

## Configuration

### 1. Environment Variables

Add these to your `.env` file:

```env
# RADIUS Database Configuration
RADIUS_DB_CONNECTION=mysql
RADIUS_DB_HOST=127.0.0.1
RADIUS_DB_PORT=3306
RADIUS_DB_DATABASE=radius
RADIUS_DB_USERNAME=radius
RADIUS_DB_PASSWORD=your_secure_password
```

### 2. Create RADIUS Database

```bash
# Connect to MySQL
mysql -u root -p

# Create database and user
CREATE DATABASE radius CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'radius'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON radius.* TO 'radius'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Run RADIUS Migrations

**Recommended Method (using the new artisan command):**
```bash
# Install RADIUS tables
php artisan radius:install

# Check if everything is configured properly
php artisan radius:install --check

# Force reinstall if needed
php artisan radius:install --force
```

**Alternative Method (manual migration):**
```bash
# Run migrations specifically for the RADIUS database
php artisan migrate --database=radius --path=database/migrations/radius

# Verify tables were created
php artisan db:table radacct --database=radius
```

## Graceful Degradation

As of the latest update, the application handles missing RADIUS tables gracefully:

- **Before**: Pages would crash with a 500 error when radacct table was missing
- **After**: Pages load successfully with an informational message and empty data

This means you can:
- Access PPP and Hotspot log pages even without RADIUS configured
- Set up RADIUS at your own pace without breaking the application
- See clear error messages guiding you through the setup

## Troubleshooting

### Error: Table 'radius.radacct' doesn't exist

**Solution 1 (Recommended)**: Use the radius:install command
```bash
php artisan radius:install
```

**Solution 2 (Alternative)**: Run the RADIUS migrations manually
```bash
php artisan migrate --database=radius --path=database/migrations/radius
```

**Solution 2**: Verify database connection
```bash
# Test connection
mysql -h 127.0.0.1 -P 3306 -u radius -p radius

# If connection fails, check:
# 1. Database credentials in .env
# 2. MySQL service is running
# 3. User has proper permissions
```

### Error: SQLSTATE[HY000] [2002] Connection refused

**Cause**: RADIUS database server is not accessible

**Solutions**:
1. Check if MySQL is running:
   ```bash
   systemctl status mysql
   # or
   systemctl status mariadb
   ```

2. Verify port is correct:
   ```bash
   netstat -tlnp | grep mysql
   ```

3. Check firewall rules if using remote database

### Error: Access denied for user 'radius'@'localhost'

**Cause**: Database credentials are incorrect or user doesn't exist

**Solutions**:
1. Verify credentials in `.env` file
2. Recreate the database user:
   ```sql
   DROP USER IF EXISTS 'radius'@'localhost';
   CREATE USER 'radius'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON radius.* TO 'radius'@'localhost';
   FLUSH PRIVILEGES;
   ```

## Testing RADIUS Setup

### 1. Check Configuration Status
```bash
# Use the radius:install command with --check flag
php artisan radius:install --check

# This will verify:
# - Database connection
# - Database exists
# - Tables exist and are accessible
# - Show record counts for each table
```

### 2. Check Database Connection (Alternative)
```bash
php artisan tinker
# In tinker:
DB::connection('radius')->getPdo();
# Should output PDO object if connection successful
```

### 2. Verify Tables Exist (Alternative)
```bash
php artisan db:table radacct --database=radius
# Should show table structure
```

### 3. Test Log Pages

Visit these URLs in your browser:
- PPP Logs: `http://your-domain.com/panel/admin/logs/ppp`
- Hotspot Logs: `http://your-domain.com/panel/admin/logs/hotspot`

**Expected Results**:
- If RADIUS is configured: Shows actual session data
- If RADIUS is not configured: Shows informational message and empty tables
- Should NEVER show a 500 error

## Integration with FreeRADIUS

If you're using FreeRADIUS server, you can point it to the same database:

1. Edit FreeRADIUS SQL configuration (`/etc/freeradius/3.0/mods-available/sql`):
   ```
   sql {
       driver = "rlm_sql_mysql"
       dialect = "mysql"
       
       server = "localhost"
       port = 3306
       login = "radius"
       password = "your_secure_password"
       
       radius_db = "radius"
   }
   ```

2. Restart FreeRADIUS:
   ```bash
   systemctl restart freeradius
   ```

## Best Practices

1. **Use separate database instance**: Consider running RADIUS database on a separate MySQL instance (different port) for better performance
2. **Regular backups**: The radacct table can grow large - implement regular archiving/purging
3. **Indexing**: The migration includes proper indexes for common queries
4. **Monitoring**: Monitor radacct table size and query performance

## Support

If you encounter issues not covered in this guide:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check MySQL error logs
3. Verify all environment variables are set correctly
4. Clear caches: `php artisan config:clear && php artisan cache:clear`

## Related Documentation

- [POST_DEPLOYMENT_STEPS.md](POST_DEPLOYMENT_STEPS.md) - General deployment steps
- [INSTALLATION.md](INSTALLATION.md) - Initial installation guide
- Database migration files in `database/migrations/radius/`
