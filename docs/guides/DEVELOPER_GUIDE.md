# Developer Guide - ISP Solution

## Role Overview

**Level**: 0 (Supreme Authority)  
**Access**: All tenants and all data across the entire platform

As a Developer, you have the highest level of access in the ISP Solution platform. You have supreme authority across all tenants and can manage all aspects of the system.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Development Environment](#development-environment)
3. [System Architecture](#system-architecture)
4. [Key Responsibilities](#key-responsibilities)
5. [Managing Super Admins](#managing-super-admins)
6. [Database Access](#database-access)
7. [API Development](#api-development)
8. [Debugging & Troubleshooting](#debugging--troubleshooting)
9. [Security Best Practices](#security-best-practices)
10. [Deployment](#deployment)

## Getting Started

### Initial Setup

```bash
# Clone the repository
git clone https://github.com/i4edubd/ispsolution.git
cd ispsolution

# Copy environment file
cp .env.example .env

# Install dependencies
composer install
npm install

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database with demo data
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=DemoSeeder
```

### Access Credentials

- **Email**: developer@ispbills.com
- **Password**: password (change immediately in production!)

## Development Environment

### Local Development with Docker

```bash
# Start containers
make up

# Install dependencies
make install

# Run migrations
make migrate

# Access shell
make shell
```

### Without Docker

```bash
# Start development server
php artisan serve

# Watch for asset changes
npm run dev

# Run queue worker
php artisan queue:work
```

## System Architecture

### Tech Stack

- **Backend**: Laravel 12.x (PHP 8.2+)
- **Frontend**: Tailwind CSS 4.x, Vite 7.x
- **Database**: MySQL 8.0 (App + RADIUS)
- **Cache/Queue**: Redis
- **Network Services**: FreeRADIUS, MikroTik RouterOS API

### Core Services

1. **IPAM Service** - IP Address Management
2. **RADIUS Service** - Authentication & Accounting
3. **MikroTik Service** - Router Management
4. **Billing Service** - Payment & Invoice Management
5. **Monitoring Service** - Network Monitoring

### Directory Structure

```
app/
├── Contracts/          # Service interfaces
├── Services/           # Business logic
├── Http/
│   ├── Controllers/    # Request handlers
│   └── Middleware/     # HTTP middleware
├── Models/             # Eloquent models
└── Helpers/            # Helper functions

resources/
├── views/              # Blade templates
└── js/                 # Frontend assets

database/
├── migrations/         # Database migrations
├── seeders/           # Database seeders
└── factories/         # Model factories

tests/
├── Unit/              # Unit tests
├── Feature/           # Feature tests
└── Integration/       # Integration tests
```

## Key Responsibilities

### System Administration

- Create and manage Super Admin accounts across all tenants
- Monitor system health and performance
- Manage system-wide configurations
- Deploy updates and patches
- Handle critical issues and escalations

### Database Management

```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Refresh database (development only!)
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_example_table
```

### Code Quality

```bash
# Run tests
php artisan test

# Static analysis
vendor/bin/phpstan analyse

# Code style
vendor/bin/pint

# Run all quality checks
make lint
```

## Managing Super Admins

### Creating Super Admins

```php
use App\Models\User;
use App\Models\Role;

$superAdmin = User::create([
    'name' => 'Super Admin Name',
    'email' => 'superadmin@example.com',
    'password' => bcrypt('secure-password'),
    'role_id' => Role::SUPER_ADMIN,
]);
```

### Via Artisan Command

```bash
# Create super admin interactively
php artisan user:create-admin

# With parameters
php artisan user:create-admin --name="John Doe" --email="john@example.com" --role=super_admin
```

## Database Access

### Application Database

```bash
# Access MySQL CLI
mysql -u ispsolution -p ispsolution

# Common queries
SELECT * FROM users WHERE role_id = 0;  # All developers
SELECT * FROM users WHERE role_id = 10; # All super admins
```

### RADIUS Database

```bash
# Access RADIUS database
mysql -u radius -p radius

# View active sessions
SELECT * FROM radacct WHERE acctstoptime IS NULL;

# View user credentials
SELECT * FROM radcheck;
```

### Database Backups

```bash
# Backup application database
mysqldump -u root -p ispsolution > backup_$(date +%Y%m%d).sql

# Backup RADIUS database
mysqldump -u root -p radius > radius_backup_$(date +%Y%m%d).sql

# Restore database
mysql -u root -p ispsolution < backup_20260123.sql
```

## API Development

### Creating New Endpoints

```php
// routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/api/v1/example', [ExampleController::class, 'index']);
    Route::post('/api/v1/example', [ExampleController::class, 'store']);
});
```

### API Documentation

API documentation is available at:
- **Local**: http://localhost:8000/api/documentation
- **Reference**: [docs/API.md](../API.md)

### Testing API Endpoints

```bash
# Using curl
curl -X GET http://localhost:8000/api/v1/example \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Using HTTPie
http GET localhost:8000/api/v1/example \
  Authorization:"Bearer YOUR_TOKEN"
```

## Debugging & Troubleshooting

### Laravel Telescope

```bash
# Install Telescope (development only)
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Access at: http://localhost:8000/telescope

### Debug Mode

```env
# .env
APP_DEBUG=true
APP_LOG_LEVEL=debug
```

### Common Issues

#### Permission Errors

```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### Cache Issues

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### Database Connection Issues

```bash
# Test database connection
php artisan db:show

# Verify credentials in .env
cat .env | grep DB_
```

### Logging

```php
// In code
use Illuminate\Support\Facades\Log;

Log::debug('Debug message', ['context' => $data]);
Log::info('Info message');
Log::warning('Warning message');
Log::error('Error message', ['exception' => $e]);
```

```bash
# View logs
tail -f storage/logs/laravel.log

# Laravel Pail (real-time)
php artisan pail
```

## Security Best Practices

### Authentication

- Never commit .env files to version control
- Use strong passwords for database and admin accounts
- Implement 2FA for developer accounts
- Regularly rotate API tokens and passwords

### Code Security

```bash
# Run security audit
composer audit

# Check for vulnerable dependencies
npm audit

# Static security analysis
vendor/bin/phpstan analyse --level=8
```

### Database Security

- Use parameterized queries (Eloquent does this automatically)
- Never store passwords in plain text
- Regularly backup databases
- Implement proper access controls

### HTTPS/SSL

```bash
# Install Let's Encrypt certificate
sudo certbot --nginx -d yourdomain.com
```

## Deployment

### Production Checklist

```bash
# 1. Update dependencies
composer install --optimize-autoloader --no-dev
npm run build

# 2. Clear and cache configs
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Run migrations
php artisan migrate --force

# 4. Set proper permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 755 storage bootstrap/cache

# 5. Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

### Environment Configuration

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Use strong passwords
DB_PASSWORD=strong_random_password_here

# Configure real services
MIKROTIK_HOST=actual_router_ip
RADIUS_DB_PASSWORD=strong_radius_password
```

### Monitoring

```bash
# Setup cron for scheduler
* * * * * cd /var/www/ispsolution && php artisan schedule:run >> /dev/null 2>&1

# Setup supervisor for queues
sudo apt-get install supervisor
```

### Performance Optimization

```bash
# Enable OPcache
# Edit /etc/php/8.2/fpm/php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000

# Optimize Composer autoloader
composer dump-autoload --optimize

# Use Redis for sessions and cache
# In .env
SESSION_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

## Useful Commands

### Artisan Commands

```bash
# List all commands
php artisan list

# Clear everything
php artisan optimize:clear

# Create new components
php artisan make:controller ExampleController
php artisan make:model Example -m
php artisan make:migration create_examples_table
php artisan make:seeder ExampleSeeder
php artisan make:test ExampleTest

# Database operations
php artisan db:show
php artisan db:monitor
php artisan db:table users

# IPAM operations
php artisan ipam:cleanup

# MikroTik operations
php artisan mikrotik:health-check
php artisan mikrotik:sync-sessions

# RADIUS operations
php artisan radius:sync-user 1
```

### Docker Commands

```bash
make help              # Show all available commands
make up                # Start all containers
make down              # Stop all containers
make shell             # Enter app container
make logs              # Show container logs
make test              # Run tests
make lint              # Run code quality checks
```

## Additional Resources

### Documentation

- [API Documentation](../API.md)
- [Testing Guide](../TESTING.md)
- [Deployment Guide](../DEPLOYMENT.md)
- [Network Services Guide](../NETWORK_SERVICES.md)

### External Resources

- [Laravel Documentation](https://laravel.com/docs/12.x)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [FreeRADIUS Documentation](https://freeradius.org/documentation/)
- [MikroTik Wiki](https://wiki.mikrotik.com/)

## Support

For technical issues:
1. Check the logs: `storage/logs/laravel.log`
2. Review documentation in `docs/` folder
3. Check GitHub issues
4. Contact senior developers

## License

This project is licensed under the MIT License.
