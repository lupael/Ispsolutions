# Deployment Guide

This guide covers deploying the ISP Solution application to production environments.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Server Requirements](#server-requirements)
3. [Installation Steps](#installation-steps)
4. [Environment Configuration](#environment-configuration)
5. [Docker Deployment](#docker-deployment)
6. [Manual Deployment](#manual-deployment)
7. [Database Setup](#database-setup)
8. [Web Server Configuration](#web-server-configuration)
9. [SSL/TLS Setup](#ssltls-setup)
10. [Performance Optimization](#performance-optimization)
11. [Monitoring & Logging](#monitoring--logging)
12. [Backup & Recovery](#backup--recovery)
13. [Security Hardening](#security-hardening)
14. [Role-Based Access & Menu Generation](#role-based-access--menu-generation)
15. [Troubleshooting](#troubleshooting)

---

## Role-Based Access & Menu Generation

The ISP Solution implements a comprehensive role-based access control (RBAC) system with automatic menu generation based on user roles and permissions.

### Role Hierarchy

The system supports 9 distinct roles with hierarchical levels:

1. **Developer (Level 1000)** - Supreme authority
   - Source code owner with all permissions (*)
   - Can create tenancies (Super Admin/ISP)
   - Define subscription pricing
   - Access any panel across all tenancies
   - Search and view all customer details
   - View audit logs and system logs
   - Suspend/activate tenancies

2. **Super Admin (Level 100)** - Tenancy administrator
   - Add new ISP/Admin
   - Configure billing (fixed/user-based/panel-based)
   - Add and manage payment gateways
   - Add and manage SMS gateways
   - View logs within tenancy
   - Full user and role management

3. **Admin (Level 90)** - Tenant administrator
   - Full access within their tenant
   - Manage users, roles, network, and billing
   - Access all device types (MikroTik, NAS, Cisco, OLT)

4. **Manager (Level 70)** - Operational permissions
   - View and manage users
   - Network management
   - View billing and reports

5. **Reseller (Level 60)** - Customer management
   - Manage customers
   - View packages and billing
   - Access commission reports

6. **Sub-Reseller (Level 55)** - Subordinate to reseller
   - Similar to reseller with limited scope

7. **Staff (Level 50)** - Limited operational access
   - View users, network, and billing
   - Manage tickets

8. **Card Distributor (Level 40)** - Recharge card management
   - Manage and sell cards
   - View balance

9. **Customer (Level 10)** - Self-service access
   - View and update profile
   - View billing and usage
   - Create and view tickets

### Menu Generation

The system automatically generates role-appropriate menus using the `MenuService` class.

#### Using MenuService in Views

```php
@inject('menuService', 'App\Services\MenuService')

@php
    $menu = $menuService->generateMenu();
@endphp

@foreach($menu as $item)
    <div class="menu-item">
        <i class="{{ $item['icon'] }}"></i>
        <a href="{{ route($item['route']) }}">{{ $item['title'] }}</a>
        
        @if(isset($item['children']))
            <ul class="submenu">
                @foreach($item['children'] as $child)
                    <li><a href="{{ route($child['route']) }}">{{ $child['title'] }}</a></li>
                @endforeach
            </ul>
        @endif
    </div>
@endforeach
```

#### Developer Panel Routes

Developer has access to supreme functionality:

- **Dashboard**: `/panel/developer/dashboard`
- **Tenancy Management**: 
  - View all: `/panel/developer/tenancies`
  - Create new: `/panel/developer/tenancies/create`
  - Toggle status: POST `/panel/developer/tenancies/{tenant}/toggle-status`
- **System Access**:
  - Access any panel: `/panel/developer/access-panel`
  - Search customers: `/panel/developer/customers/search`
  - View all customers: `/panel/developer/customers`
- **Audit & Logs**:
  - Audit logs: `/panel/developer/audit-logs`
  - System logs: `/panel/developer/logs`
  - Error logs: `/panel/developer/error-logs`

#### Super Admin Panel Routes

Super Admin manages ISP/Admin and system configuration:

- **Dashboard**: `/panel/super-admin/dashboard`
- **ISP Management**:
  - View ISPs: `/panel/super-admin/isp`
  - Create ISP: `/panel/super-admin/isp/create`
- **Billing Configuration**:
  - Fixed billing: `/panel/super-admin/billing/fixed`
  - User-based billing: `/panel/super-admin/billing/user-base`
  - Panel-based billing: `/panel/super-admin/billing/panel-base`
- **Payment Gateway**:
  - View gateways: `/panel/super-admin/payment-gateway`
  - Add gateway: `/panel/super-admin/payment-gateway/create`
- **SMS Gateway**:
  - View gateways: `/panel/super-admin/sms-gateway`
  - Add gateway: `/panel/super-admin/sms-gateway/create`
- **Logs**: `/panel/super-admin/logs`

### Permission Checking

Check permissions in controllers:

```php
// Check if user has specific permission
if ($user->hasPermission('users.manage')) {
    // Allow action
}

// Check if user has specific role
if ($user->hasRole('super-admin')) {
    // Allow action
}

// Check if user has any of the specified roles
if ($user->hasAnyRole(['admin', 'super-admin'])) {
    // Allow action
}
```

### Middleware Usage

Routes are protected using the `role` middleware:

```php
Route::middleware(['auth', 'role:developer'])->group(function () {
    // Developer-only routes
});

Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    // Super Admin and Admin routes
});
```

### Database Seeding

Seed roles after deployment:

```bash
php artisan db:seed --class=RoleSeeder
```

This creates all 9 roles with appropriate permissions.

---

## Prerequisites

- Linux server (Ubuntu 22.04 LTS recommended)
- Root or sudo access
- Domain name pointed to server IP
- SSL certificate (Let's Encrypt recommended)
- At least 2GB RAM, 2 CPU cores, 20GB disk space

---

## Server Requirements

### Software Stack

- **PHP:** 8.2 or higher
- **Web Server:** Nginx 1.18+ or Apache 2.4+
- **Database:** MySQL 8.0 or MariaDB 10.6+
- **Cache:** Redis 7.0+
- **Node.js:** 20 LTS (for asset building)
- **Composer:** 2.6+
- **Git:** 2.x

### PHP Extensions

Required extensions:
```
php8.2-cli
php8.2-fpm
php8.2-mysql
php8.2-redis
php8.2-mbstring
php8.2-xml
php8.2-curl
php8.2-zip
php8.2-bcmath
php8.2-intl
php8.2-gd
```

---

## Installation Steps

### 1. Update System

```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install Required Packages

```bash
# Install PHP 8.2
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-redis \
    php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath \
    php8.2-intl php8.2-gd

# Install MySQL
sudo apt install -y mysql-server

# Install Redis
sudo apt install -y redis-server

# Install Nginx
sudo apt install -y nginx

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 3. Create Application User

```bash
sudo useradd -m -s /bin/bash ispsolution
sudo usermod -aG www-data ispsolution
```

### 4. Clone Repository

```bash
sudo su - ispsolution
cd /home/ispsolution
git clone https://github.com/i4edubd/ispsolution.git
cd ispsolution
```

### 5. Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies and build assets
npm ci --production
npm run build
```

---

## Environment Configuration

### 1. Create Environment File

```bash
cp .env.example .env
php artisan key:generate
```

### 2. Configure Database

Edit `.env`:

```env
APP_NAME="ISP Solution"
APP_ENV=production
APP_KEY=base64:... # Generated by key:generate
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ispsolution
DB_USERNAME=ispsolution_user
DB_PASSWORD=STRONG_RANDOM_PASSWORD

RADIUS_DB_HOST=127.0.0.1
RADIUS_DB_PORT=3306
RADIUS_DB_DATABASE=radius
RADIUS_DB_USERNAME=radius_user
RADIUS_DB_PASSWORD=STRONG_RANDOM_PASSWORD

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

MIKROTIK_HOST=192.168.88.1
MIKROTIK_PORT=8728
MIKROTIK_USERNAME=admin
MIKROTIK_PASSWORD=your_mikrotik_password
MIKROTIK_TIMEOUT=30

IPAM_DEFAULT_POOL_SIZE=254
IPAM_CLEANUP_DAYS=30
```

### 3. Set Permissions

```bash
sudo chown -R ispsolution:www-data /home/ispsolution/ispsolution
sudo chmod -R 755 /home/ispsolution/ispsolution
sudo chmod -R 775 /home/ispsolution/ispsolution/storage
sudo chmod -R 775 /home/ispsolution/ispsolution/bootstrap/cache
```

---

## Docker Deployment

### Using Docker Compose

1. **Install Docker and Docker Compose:**

```bash
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

2. **Configure Environment:**

```bash
cp .env.example .env
# Edit .env with production values
```

3. **Start Services:**

```bash
docker-compose -f docker-compose.prod.yml up -d
```

4. **Run Migrations:**

```bash
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan migrate --database=radius --path=database/migrations/radius --force
```

5. **Optimize Application:**

```bash
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

---

## Database Setup

### 1. Create Databases

```bash
sudo mysql
```

```sql
CREATE DATABASE ispsolution CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE radius CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'ispsolution_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
CREATE USER 'radius_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';

GRANT ALL PRIVILEGES ON ispsolution.* TO 'ispsolution_user'@'localhost';
GRANT ALL PRIVILEGES ON radius.* TO 'radius_user'@'localhost';

FLUSH PRIVILEGES;
EXIT;
```

### 2. Run Migrations

```bash
php artisan migrate --force
php artisan migrate --database=radius --path=database/migrations/radius --force
```

### 3. Seed Initial Data

```bash
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=ServicePackageSeeder --force
```

---

## Web Server Configuration

### Nginx Configuration

Create `/etc/nginx/sites-available/ispsolution`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    root /home/ispsolution/ispsolution/public;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    
    # Logging
    access_log /var/log/nginx/ispsolution-access.log;
    error_log /var/log/nginx/ispsolution-error.log;
    
    # Client body size
    client_max_body_size 20M;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/ispsolution /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## SSL/TLS Setup

### Using Let's Encrypt (Certbot)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test renewal
sudo certbot renew --dry-run

# Auto-renewal is configured via cron
```

---

## Performance Optimization

### 1. Laravel Optimizations

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### 2. PHP-FPM Configuration

Edit `/etc/php/8.2/fpm/pool.d/www.conf`:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.2-fpm
```

### 3. MySQL Optimization

Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
query_cache_size = 32M
query_cache_limit = 2M
```

Restart MySQL:

```bash
sudo systemctl restart mysql
```

### 4. Redis Configuration

Edit `/etc/redis/redis.conf`:

```ini
maxmemory 512mb
maxmemory-policy allkeys-lru
```

Restart Redis:

```bash
sudo systemctl restart redis-server
```

---

## Monitoring & Logging

### 1. Application Logs

View logs:

```bash
tail -f storage/logs/laravel.log
```

### 2. Web Server Logs

```bash
tail -f /var/log/nginx/ispsolution-access.log
tail -f /var/log/nginx/ispsolution-error.log
```

### 3. Queue Worker (Supervisor)

Install Supervisor:

```bash
sudo apt install -y supervisor
```

Create `/etc/supervisor/conf.d/ispsolution-worker.conf`:

```ini
[program:ispsolution-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/ispsolution/ispsolution/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=ispsolution
numprocs=2
redirect_stderr=true
stdout_logfile=/home/ispsolution/ispsolution/storage/logs/worker.log
stopwaitsecs=3600
```

Start worker:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ispsolution-worker:*
```

### 4. Scheduler (Cron)

Add to crontab:

```bash
sudo crontab -e -u ispsolution
```

Add line:

```
* * * * * cd /home/ispsolution/ispsolution && php artisan schedule:run >> /dev/null 2>&1
```

---

## Backup & Recovery

### 1. Database Backup Script

Create `/home/ispsolution/backup-db.sh`:

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/ispsolution/backups"
mkdir -p $BACKUP_DIR

# Backup application database
mysqldump -u ispsolution_user -p'PASSWORD' ispsolution | gzip > $BACKUP_DIR/ispsolution_$DATE.sql.gz

# Backup RADIUS database
mysqldump -u radius_user -p'PASSWORD' radius | gzip > $BACKUP_DIR/radius_$DATE.sql.gz

# Delete backups older than 7 days
find $BACKUP_DIR -type f -mtime +7 -delete
```

Make executable and schedule:

```bash
chmod +x /home/ispsolution/backup-db.sh
crontab -e
# Add: 0 2 * * * /home/ispsolution/backup-db.sh
```

### 2. Application Backup

```bash
tar -czf /home/ispsolution/backups/app_backup_$(date +%Y%m%d).tar.gz \
    -C /home/ispsolution ispsolution \
    --exclude=node_modules \
    --exclude=vendor \
    --exclude=storage/logs
```

---

## Security Hardening

### 1. Firewall Configuration

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 2. Disable Debug Mode

Ensure in `.env`:

```env
APP_DEBUG=false
APP_ENV=production
```

### 3. Secure File Permissions

```bash
find /home/ispsolution/ispsolution -type f -exec chmod 644 {} \;
find /home/ispsolution/ispsolution -type d -exec chmod 755 {} \;
chmod -R 775 /home/ispsolution/ispsolution/storage
chmod -R 775 /home/ispsolution/ispsolution/bootstrap/cache
```

### 4. Fail2ban

Install and configure:

```bash
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

---

## Troubleshooting

### 1. Permission Denied Errors

```bash
sudo chown -R ispsolution:www-data /home/ispsolution/ispsolution
sudo chmod -R 775 storage bootstrap/cache
```

### 2. 502 Bad Gateway

Check PHP-FPM status:

```bash
sudo systemctl status php8.2-fpm
sudo systemctl restart php8.2-fpm
```

### 3. Database Connection Errors

Verify credentials:

```bash
mysql -u ispsolution_user -p
```

Check `.env` configuration.

### 4. Queue Jobs Not Processing

Restart supervisor:

```bash
sudo supervisorctl restart ispsolution-worker:*
```

---

## Post-Deployment Checklist

- [ ] Environment file configured
- [ ] Application key generated
- [ ] Databases created and migrated
- [ ] Initial data seeded
- [ ] SSL certificate installed
- [ ] Web server configured
- [ ] Permissions set correctly
- [ ] Queue worker running
- [ ] Scheduler configured
- [ ] Backups automated
- [ ] Monitoring setup
- [ ] Firewall configured
- [ ] Debug mode disabled
- [ ] Logs rotating properly

---

## Support

For deployment issues:
- Review this guide thoroughly
- Check logs: `tail -f storage/logs/laravel.log`
- Verify configurations
- Open an issue on GitHub

For production support: contact the development team.
