# ISP Solution Installation Script

## Overview

This script provides a complete, automated installation of the ISP Solution on a fresh Ubuntu server. It handles everything from system updates to database configuration, making deployment as simple as running a single command.

## What Gets Installed

The script installs and configures:

1. **System Optimization**: 
   - Swap memory (configurable, default 2GB)
   - System updates and security patches

2. **Core Dependencies**: 
   - PHP 8.2+ with required extensions
   - Composer (PHP dependency manager)
   - Node.js LTS and NPM
   - MySQL 8.0 database server
   - Redis cache/queue server
   - Nginx web server

3. **Network Services**:
   - FreeRADIUS server for authentication
   - OpenVPN server (fully configured, default enabled)

4. **Application Setup**:
   - Clone ISP Solution from GitHub
   - Install PHP and Node dependencies
   - Configure Laravel application
   - Setup databases (application + RADIUS)
   - Run migrations
   - Seed demo data
   - Build frontend assets

5. **System Configuration**:
   - Nginx virtual host
   - PHP-FPM optimization
   - Firewall rules
   - Laravel task scheduler
   - SSL/HTTPS (optional with Let's Encrypt)
   - Tenant subdomain automation

## Supported Systems

- Ubuntu 18.04 LTS
- Ubuntu 20.04 LTS
- Ubuntu 22.04 LTS ✓ **Tested**
- Ubuntu 24.04 LTS ✓ **Tested**

**Note**: Script must be run on a **fresh** Ubuntu installation for best results.

## Quick Start

### Basic Installation

```bash
# Download the script
wget https://raw.githubusercontent.com/i4edubd/ispsolution/main/install.sh

# Make it executable
chmod +x install.sh

# Run as root
sudo bash install.sh
```

### With Custom Domain

```bash
# Set your domain before installation
export DOMAIN_NAME="isp.yourdomain.com"
sudo bash install.sh
```

### With Custom Domain and SSL

```bash
# Set domain and email for SSL certificate
export DOMAIN_NAME="isp.yourdomain.com"
export EMAIL="admin@yourdomain.com"
export SETUP_SSL="yes"
sudo bash install.sh
```

### With Custom Swap Size

```bash
# Set custom swap memory (default: 2G)
export SWAP_SIZE="4G"
sudo bash install.sh
```

### Full Configuration Example

```bash
# Complete configuration with all options
export DOMAIN_NAME="isp.example.com"
export EMAIL="admin@example.com"
export SETUP_SSL="yes"
export INSTALL_OPENVPN="yes"
export SWAP_SIZE="4G"
sudo bash install.sh
```

## Configuration Options

Set these environment variables before running the script:

```bash
# Domain name (default: localhost)
export DOMAIN_NAME="isp.example.com"

# Email for SSL notifications (required if SETUP_SSL=yes)
export EMAIL="admin@example.com"

# Enable SSL with Let's Encrypt (default: no)
export SETUP_SSL="yes"

# Swap memory size (default: 2G)
export SWAP_SIZE="2G"

# Installation directory (default: /var/www/ispsolution)
export INSTALL_DIR="/var/www/ispsolution"

# Database names and users (defaults shown)
export DB_NAME="ispsolution"
export DB_USER="ispsolution"
export RADIUS_DB_NAME="radius"
export RADIUS_DB_USER="radius"

# Install OpenVPN server (default: yes)
export INSTALL_OPENVPN="yes"

# Run installation
sudo bash install.sh
```

**Note**: Database passwords are auto-generated securely and saved in `/root/ispsolution-credentials.txt`

## What Happens During Installation

The script performs these steps in order:

1. ✅ **Setup Swap Memory** - Configures swap space for better performance
2. ✅ **System Update** - Updates package lists and upgrades system
3. ✅ **Basic Dependencies** - Installs curl, wget, git, etc.
4. ✅ **PHP 8.2** - Installs PHP and required extensions
5. ✅ **Composer** - Installs PHP dependency manager
6. ✅ **Node.js** - Installs Node.js LTS and NPM
7. ✅ **MySQL 8.0** - Installs and secures database server
8. ✅ **Redis** - Installs cache and queue server
9. ✅ **Nginx** - Installs web server
10. ✅ **FreeRADIUS** - Installs RADIUS authentication server
11. ✅ **OpenVPN** - Installs and configures VPN server (default enabled)
12. ✅ **Clone Repository** - Gets latest code from GitHub
13. ✅ **Setup Databases** - Creates application and RADIUS databases
13. ✅ **Configure Laravel** - Sets up environment and dependencies
14. ✅ **Install Node Modules** - Installs and builds frontend assets
15. ✅ **Run Migrations** - Creates database tables
16. ✅ **Seed Data** - Loads demo users and data
17. ✅ **Configure Nginx** - Sets up virtual host
18. ✅ **Configure Firewall** - Opens required ports
19. ✅ **Configure FreeRADIUS** - Connects to MySQL
20. ✅ **Setup SSL** - Installs Let's Encrypt certificate (if enabled)
21. ✅ **Subdomain Automation** - Configures tenant subdomain creation tool
22. ✅ **Setup Scheduler** - Configures Laravel cron
23. ✅ **Optimize** - Caches configuration and routes
24. ✅ **Summary** - Displays credentials and next steps

## Post-Installation

### Access Your Installation

After successful installation:

1. **Web Interface**: http://your-domain.com (or http://server-ip)
2. **Login Credentials**: Check `/root/ispsolution-credentials.txt`

### Demo Accounts

The script creates these demo accounts (all use password: `password`):

| Email | Role | Level |
|-------|------|-------|
| developer@ispbills.com | Developer | 0 |
| superadmin@ispbills.com | Super Admin | 10 |
| admin@ispbills.com | Admin | 20 |
| operator@ispbills.com | Operator | 30 |
| suboperator@ispbills.com | Sub-Operator | 40 |
| customer@ispbills.com | Customer | 100 |

**⚠️ IMPORTANT**: Change all demo passwords immediately in production!

### Saved Credentials

All credentials are saved securely to:
```
/root/ispsolution-credentials.txt
```

**File permissions**: 600 (readable only by root)

**⚠️ IMPORTANT**: 
- Copy credentials to a secure password manager
- Delete the file after copying: `rm /root/ispsolution-credentials.txt`

### Next Steps

1. **Change Demo Passwords**
   ```bash
   # Access the application and change all demo account passwords
   ```

2. **Configure DNS**
   - Point your domain to the server IP
   - For tenant subdomains, create wildcard DNS: *.yourdomain.com → server IP
   - Update APP_URL in `.env` file if needed

3. **OpenVPN Client Setup** (if installed)
   ```bash
   # Client certificates are in ~/openvpn-ca/keys/
   # Generate client config:
   cd ~/openvpn-ca
   ./build-key client1
   
   # Transfer client.crt, client.key, ca.crt, and ta.key to client
   ```

4. **Create Tenant Subdomains**
   ```bash
   # Automatically create subdomain for new tenant
   sudo create-tenant-subdomain.sh tenant1 123
   
   # This creates: tenant1.yourdomain.com
   # And automatically obtains SSL if configured
   ```

5. **Configure MikroTik Routers**
   # Using Let's Encrypt (recommended)
   sudo apt-get install certbot python3-certbot-nginx
   sudo certbot --nginx -d yourdomain.com
   ```

4. **Configure MikroTik Routers**
   - Login to admin panel
   - Go to Network → Routers
   - Add your MikroTik router details

5. **Configure RADIUS Clients**
   - Edit FreeRADIUS clients configuration
   - Add your network devices

6. **Review Settings**
   - Check `.env` file
   - Verify database connections
   - Test RADIUS connectivity

## Firewall Ports

The script opens these ports:

- **22** - SSH
- **80** - HTTP
- **443** - HTTPS (for SSL)
- **1812/udp** - RADIUS authentication
- **1813/udp** - RADIUS accounting
- **1194/udp** - OpenVPN (if installed)

### Additional Ports (for reference)

These ports are used by the application but may not require firewall rules depending on your setup:

- **3306** - MySQL (application database) - Usually internal only
- **3307** - MySQL (RADIUS database, Docker only) - Host port mapping to access Docker container (container internally uses 3306)
- **6379** - Redis (cache and queue server) - Usually internal only
- **8728** - MikroTik API - Required if managing MikroTik routers remotely
- **8000** - Application HTTP (Docker development) - Use 80/443 in production
- **1025** - Mailpit SMTP (development only) - Not needed in production

## RADIUS Server Configuration

The ISP Solution uses FreeRADIUS for authenticating PPPoE and Hotspot users. The installation script automatically installs FreeRADIUS, but you may need to configure it for your specific setup.

### RADIUS Architecture

The system uses a two-tier RADIUS architecture:

1. **FreeRADIUS Server** - Handles RADIUS protocol communication (UDP ports 1812/1813)
2. **RADIUS Database** - Stores user credentials and accounting data (MySQL on port 3306 for native/container-internal, or 3307 when connecting from host to Docker container)

```
┌──────────────┐    RADIUS Protocol     ┌──────────────┐
│ MikroTik     │  ◄──(UDP 1812/1813)──► │ FreeRADIUS   │
│ Router/AP    │                         │ Server       │
└──────────────┘                         └──────────────┘
                                               │
                                               │ SQL
                                               ▼
                                         ┌──────────────┐
                                         │ MySQL RADIUS │
                                         │ Database     │
                                         └──────────────┘
                                               │
                                               │ HTTP API
                                               ▼
                                         ┌──────────────┐
                                         │ ISP Solution │
                                         │ Laravel App  │
                                         └──────────────┘
```

### Post-Installation RADIUS Setup

After running the installation script, FreeRADIUS is installed and configured. Here are the key configuration steps:

#### 1. Configure RADIUS Database Connection

The RADIUS database is automatically created during installation. Verify the connection in `.env`:

```env
RADIUS_DB_CONNECTION=mysql
RADIUS_DB_HOST=127.0.0.1
RADIUS_DB_PORT=3306  # Use 3306 for native installation or within Docker, or 3307 when connecting from host to Docker
RADIUS_DB_DATABASE=radius
RADIUS_DB_USERNAME=radius
RADIUS_DB_PASSWORD=your_password  # Check /root/ispsolution-credentials.txt

RADIUS_SERVER_IP=127.0.0.1
RADIUS_AUTH_PORT=1812
RADIUS_ACCT_PORT=1813
```

**Note**: In Docker environments, the RADIUS database container internally uses port 3306 but is mapped to host port 3307 (3307:3306). Use port 3307 only when connecting from the host machine to the Docker container. For native installations or container-to-container communication, use port 3306.

#### 2. Configure FreeRADIUS SQL Module

Edit FreeRADIUS SQL configuration (`/etc/freeradius/3.0/mods-available/sql`):

```conf
sql {
    driver = "rlm_sql_mysql"
    dialect = "mysql"
    
    server = "localhost"
    port = 3306
    login = "radius"
    password = "your_radius_password"
    
    radius_db = "radius"
    
    read_clients = yes
}
```

Enable the SQL module:
```bash
sudo ln -s /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/sql
```

#### 3. Configure RADIUS Clients (NAS Devices)

Add your MikroTik routers or access points to `/etc/freeradius/3.0/clients.conf`:

```conf
client mikrotik_router {
    ipaddr = 192.168.1.1  # Your router IP
    secret = your_shared_secret_here
    nastype = mikrotik
    shortname = mikrotik-main
}
```

**Important**: The `secret` must match the RADIUS secret configured on your MikroTik device.

#### 4. Restart FreeRADIUS

```bash
sudo systemctl restart freeradius
sudo systemctl enable freeradius

# Check status
sudo systemctl status freeradius

# Test in debug mode (optional)
sudo freeradius -X
```

#### 5. Install RADIUS Tables in Application

Run the RADIUS installation command:

```bash
cd /var/www/ispsolution
php artisan radius:install

# Verify installation
php artisan radius:install --check
```

### Testing RADIUS

#### Test Authentication with radtest

```bash
# Install radtest utility if not present
sudo apt-get install freeradius-utils

# Test authentication
radtest testuser testpassword localhost 0 testing123

# Expected output:
# Received Access-Accept
```

#### Test from MikroTik

Configure RADIUS on your MikroTik router:

```bash
# Via RouterOS CLI
/radius add address=YOUR_SERVER_IP secret=your_shared_secret service=ppp,hotspot

# Test connectivity
/radius monitor 0
```

### Troubleshooting RADIUS

#### Check FreeRADIUS Status

```bash
sudo systemctl status freeradius

# View logs
sudo tail -f /var/log/freeradius/radius.log

# Debug mode (shows all requests)
sudo freeradius -X
```

#### Check RADIUS Database

```bash
# Login to MySQL
mysql -u radius -p radius

# Check for users
SELECT * FROM radcheck;

# Check accounting records
SELECT * FROM radacct LIMIT 10;
```

#### Common Issues

1. **Port 1812/1813 Already in Use**
   ```bash
   netstat -ulpn | grep 1812
   # If another service is using it, stop that service
   ```

2. **FreeRADIUS Can't Connect to Database**
   - Verify credentials in `/etc/freeradius/3.0/mods-available/sql`
   - Check MySQL is running: `sudo systemctl status mysql`
   - Verify user has permissions: `SHOW GRANTS FOR 'radius'@'localhost';`

3. **MikroTik Not Receiving Response**
   - Verify firewall allows UDP 1812/1813
   - Check RADIUS client configuration in `/etc/freeradius/3.0/clients.conf`
   - Ensure shared secret matches on both sides
   - Test with `radtest` first before testing from MikroTik

### Related Documentation

For more detailed RADIUS configuration:
- **[RADIUS_SETUP_GUIDE.md](RADIUS_SETUP_GUIDE.md)** - Complete RADIUS database setup
- **[RADIUS_INTEGRATION_GUIDE.md](RADIUS_INTEGRATION_GUIDE.md)** - FreeRADIUS integration details
- **[MIKROTIK_QUICKSTART.md](MIKROTIK_QUICKSTART.md)** - MikroTik router configuration

## Troubleshooting

### Installation Fails

**Check Logs**:
```bash
# The script outputs detailed logs during installation
# Review any error messages carefully
```

**Common Issues**:

1. **Insufficient Disk Space**
   ```bash
   df -h  # Check available space
   # Need at least 10GB free
   ```

2. **Network Issues**
   ```bash
   ping -c 3 google.com  # Test connectivity
   curl -I https://github.com  # Test GitHub access
   ```

3. **Port Already in Use**
   ```bash
   netstat -tulpn | grep :80  # Check if port 80 is in use
   ```

### MySQL Issues

**Check MySQL Status**:
```bash
sudo systemctl status mysql
sudo mysql -u root -p  # Use password from credentials file
```

**Reset MySQL Root Password**:
```bash
sudo mysql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'new_password';
FLUSH PRIVILEGES;
```

### Nginx Issues

**Check Nginx Status**:
```bash
sudo systemctl status nginx
sudo nginx -t  # Test configuration
```

**View Nginx Logs**:
```bash
sudo tail -f /var/log/nginx/error.log
```

### Laravel Issues

**Check Laravel Logs**:
```bash
cd /var/www/ispsolution
tail -f storage/logs/laravel.log
```

**Clear Laravel Caches**:
```bash
cd /var/www/ispsolution
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**Fix Permissions**:
```bash
cd /var/www/ispsolution
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## Security Considerations

### Production Deployment

For production use:

1. **Change All Passwords**
   - Database passwords
   - Demo account passwords
   - Add strong admin password

2. **Enable SSL/HTTPS**
   - Install Let's Encrypt certificate
   - Force HTTPS redirects
   - Update APP_URL to https

3. **Secure MySQL**
   - Disable remote root access
   - Use strong passwords
   - Regular backups

4. **Configure Firewall**
   - Limit SSH access
   - Close unnecessary ports
   - Consider fail2ban

5. **Regular Updates**
   ```bash
   # System updates
   sudo apt-get update && sudo apt-get upgrade
   
   # Application updates
   cd /var/www/ispsolution
   git pull
   composer install
   npm run build
   php artisan migrate
   ```

6. **Backup Strategy**
   - Regular database backups
   - Code repository backups
   - Configuration backups

### Security Best Practices

```bash
# 1. Setup automatic security updates
sudo apt-get install unattended-upgrades
sudo dpkg-reconfigure --priority=low unattended-upgrades

# 2. Install and configure fail2ban
sudo apt-get install fail2ban
sudo systemctl enable fail2ban

# 3. Disable root SSH login
sudo nano /etc/ssh/sshd_config
# Set: PermitRootLogin no
sudo systemctl restart sshd

# 4. Setup firewall logging
sudo ufw logging on

# 5. Regular security audits
sudo apt-get install lynis
sudo lynis audit system
```

## Uninstallation

If you need to remove the installation:

```bash
# Stop services
sudo systemctl stop nginx php8.2-fpm mysql redis-server freeradius

# Remove packages
sudo apt-get remove --purge nginx php8.2* mysql-server redis-server freeradius

# Remove application
sudo rm -rf /var/www/ispsolution

# Remove databases
sudo mysql -u root -p
DROP DATABASE ispsolution;
DROP DATABASE radius;
DROP USER 'ispsolution'@'localhost';
DROP USER 'radius'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Remove credentials file
sudo rm -f /root/ispsolution-credentials.txt
```

## Support

### Documentation

- **User Guides**: See `docs/guides/` folder
- **Technical Docs**: See `docs/technical/` folder
- **API Docs**: See `docs/API.md`
- **Full Index**: See `docs/INDEX.md`

### Getting Help

- **GitHub Issues**: https://github.com/i4edubd/ispsolution/issues
- **Email Support**: support@example.com
- **Documentation**: Full docs in `docs/` folder

### Contributing

Contributions welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License

This project is licensed under the MIT License. See LICENSE file for details.

## Credits

Developed by the ISP Solution team.

---

**Version**: 1.0  
**Last Updated**: January 2026  
**Compatibility**: Ubuntu 18.04+, 20.04+, 22.04+, 24.04+
