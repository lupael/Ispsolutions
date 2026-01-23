# ISP Solution - Network Services Management

This project is an ISP (Internet Service Provider) management system built with Laravel 12, featuring comprehensive billing and network monitoring capabilities.

## Project Overview

**Goal**: Provide a complete ISP management solution with RADIUS authentication, MikroTik router integration, and IP address management (IPAM), all wrapped in a modern Tailwind CSS interface based on the Metronic design system.

## Tech Stack

- **Laravel**: 12.x (Latest)
- **PHP**: 8.2+
- **Database**: MySQL 8.0 (Application + RADIUS databases)
- **Redis**: Latest (Caching and Queue management)
- **Tailwind CSS**: 4.x
- **Vite**: 7.x for asset building
- **Docker**: Containerized development environment
- **Node.js**: Latest LTS version

## Documentation

### Getting Started
- **[Installation Guide](INSTALLATION.md)** ⭐ - Complete automated installation guide
- **[Documentation Index](docs/INDEX.md)** - Complete documentation catalog
- **[Quick Start](README.md#getting-started)** - Get up and running quickly

### User Guides by Role
Choose the guide for your role:
- **[Developer Guide](docs/guides/DEVELOPER_GUIDE.md)** - Level 0: System development and administration
- **[Super Admin Guide](docs/guides/SUPERADMIN_GUIDE.md)** - Level 10: Tenant management
- **[Admin Guide](docs/guides/ADMIN_GUIDE.md)** - Level 20: ISP owner operations
- **[Operator Guide](docs/guides/OPERATOR_GUIDE.md)** - Level 30: Area/zone management
- **[Sub-Operator Guide](docs/guides/SUBOPERATOR_GUIDE.md)** - Level 40: Local customer management
- **[Manager Guide](docs/guides/MANAGER_GUIDE.md)** - Level 50: Oversight and reporting
- **[Staff Guide](docs/guides/STAFF_GUIDE.md)** - Level 80: Administrative support
- **[Customer Guide](docs/guides/CUSTOMER_GUIDE.md)** - Level 100: End user self-service

### Core Documentation
- **[Roles & Permissions Guide](docs/ROLES_AND_PERMISSIONS.md)** - Complete role hierarchy, permissions, and data isolation
- **[API Documentation](docs/API.md)** - Complete REST API reference with authentication and examples
- **[Testing Guide](docs/TESTING.md)** - How to run and write tests
- **[Deployment Guide](docs/DEPLOYMENT.md)** - Production deployment instructions
- **[Implementation Checklist](docs/TODO_REIMPLEMENT.md)** - Detailed development roadmap

### Feature Specifications
- **[Network Services Guide](docs/NETWORK_SERVICES.md)** - RADIUS, MikroTik, IPAM, and session monitoring
- **[OLT Service Guide](docs/OLT_SERVICE_GUIDE.md)** - OLT/ONU management and provisioning
- **[TODO Features A-Z](TODO_FEATURES_A2Z.md)** - Complete feature list and specifications
- **[Panel Specifications](PANELS_SPECIFICATION.md)** - Detailed panel-specific documentation
- **[Multi-Tenancy Isolation](MULTI_TENANCY_ISOLATION.md)** - Multi-tenancy architecture overview

### MikroTik Integration
- **[MikroTik Quick Start](MIKROTIK_QUICKSTART.md)** - Quick start guide for MikroTik integration
- **[MikroTik Advanced Features](MIKROTIK_ADVANCED_FEATURES.md)** - Advanced MikroTik features and configuration

## Multi-Tenancy Role System

The system now includes a comprehensive 12-role hierarchy with strict data isolation:

| Level | Role | Data Access |
|-------|------|-------------|
| 0 | Developer | All tenants (supreme authority) |
| 10 | Super Admin | Only OWN tenants |
| 20 | Admin | Own ISP data within tenancy |
| 30 | Operator | Own + sub-operator customers |
| 40 | Sub-Operator | Only own customers |
| 50-80 | Manager/Staff/Accountant | Permission-based |
| 100 | Customer | Self-service only |

### Quick Start with Roles

```php
// Check user role
if (auth()->user()->isDeveloper()) { ... }
if (auth()->user()->isAdmin()) { ... }

// Get accessible customers (auto-scoped by role)
$customers = auth()->user()->accessibleCustomers()->paginate(50);

// Check hierarchy
if (auth()->user()->canManage($otherUser)) { ... }
```

### Seed Roles
```bash
php artisan db:seed --class=RoleSeeder
```

**Documentation:**
- [SUMMARY.md](SUMMARY.md) - Executive summary (12.4 KB)
- [DATA_ISOLATION.md](DATA_ISOLATION.md) - Complete guide (15.5 KB)
- [ROLE_SYSTEM_QUICK_REFERENCE.md](ROLE_SYSTEM_QUICK_REFERENCE.md) - Quick reference (10.6 KB)

## Features

### 🎉 Major Milestone - January 23, 2026
**400 Features Completed - 96.4% Complete!**
- ✅ 400/415 features implemented (96.4% complete) - Up from 200 features
- ✅ Production readiness increased to 95% (up from 75%)
- ✅ Comprehensive A-Z feature coverage through letter W (Web)
- 🎯 Only 15 features remaining (3.6%) - nearly complete!
- 📄 See [NEXT_200_TASKS_COMPLETED.md](NEXT_200_TASKS_COMPLETED.md) for detailed breakdown
- 📄 See [CHANGELOG.md](CHANGELOG.md) for version 4.0.0 release notes

### Network Services
- **RADIUS Integration**: Authentication, accounting, and user synchronization with FreeRADIUS
- **MikroTik Management**: PPPoE user management, session monitoring, and remote disconnection via RouterOS API
- **IPAM (IP Address Management)**: IP pool creation, subnet management, IP allocation/release, and conflict detection
- **Session Monitoring**: Real-time tracking of active sessions, bandwidth usage, and connection history

### UI/UX
- Modern admin interface based on Metronic Tailwind HTML Demo1 layout
- Responsive design for desktop and mobile devices
- Real-time session monitoring dashboard
- Interactive IP allocation management

### Multi-Tenant Role Management

The platform implements a hierarchical role-based access control (RBAC) system with strict tenant boundaries.

**📖 Complete Documentation:** See [ROLE_SYSTEM.md](ROLE_SYSTEM.md) for full specification v3.1

#### Role Hierarchy (Lower level = Higher privilege)

```
Level 0:   Developer        - Supreme authority across all tenants
Level 10:  Super Admin      - Manages Admins within own tenants only
Level 20:  Admin            - ISP owner, manages Operators within ISP tenant
Level 30:  Operator         - Manages Sub-Operators and customers in segment
Level 40:  Sub-Operator     - Manages only own customers
Level 50:  Manager          - View/Edit if explicitly permitted by Admin
Level 70:  Accountant       - View-only financial access
Level 80:  Staff            - View/Edit if explicitly permitted by Admin
Level 100: Customer         - End user
```

#### Role Creation Permissions

- **Developer** → Creates/Manages Super Admins across all tenants
- **Super Admin** → Creates/Manages Admins within their own tenants only
- **Admin** → Creates/Manages Operators, Sub-Operators, Managers, Accountants, Staff within their ISP
- **Operator** → Creates/Manages Sub-Operators and Customers
- **Sub-Operator** → Creates Customers only
- **Manager/Staff/Accountant** → View-only access, cannot create users

#### Data Isolation Rules

- **Developer**: Access all tenants, all data (supreme authority)
- **Super Admin**: Access only tenants they created
- **Admin**: Access only data within their ISP tenant
- **Operator**: Access own customers + sub-operator customers
- **Sub-Operator**: Access only own customers
- **View-Only Roles**: Permission-based read access within tenant

#### Demo Accounts

All demo accounts use password: **`password`**

| Email                        | Role          | Level |
|------------------------------|---------------|-------|
| developer@ispbills.com       | Developer     | 0     |
| superadmin@ispbills.com      | Super Admin   | 10    |
| admin@ispbills.com           | Admin         | 20    |
| operator@ispbills.com        | Operator      | 30    |
| suboperator@ispbills.com     | Sub-Operator  | 40    |
| customer@ispbills.com        | Customer      | 100   |

Seed demo data with:
```bash
php artisan db:seed --class=DemoSeeder
```

#### Documentation

- **[ROLE_SYSTEM.md](ROLE_SYSTEM.md)** - Complete role system specification v3.1
- **[DATA_ISOLATION.md](DATA_ISOLATION.md)** - Complete data isolation rules
- **[CHANGELOG.md](CHANGELOG.md)** - Recent changes and updates

### ✅ Core Implementation

1. **Laravel MVC Architecture**
   - Dedicated controllers for each demo (Demo1Controller - Demo10Controller)
   - Clean routing structure with named routes
   - Blade template inheritance and components

2. **Asset Management**
   - Metronic CSS and JavaScript assets properly integrated
   - Laravel asset helpers for proper path resolution
   - Vite integration for development workflow

3. **Template System**
   - Blade layouts for each demo with proper inheritance
   - Reusable partials for headers, sidebars, and footers
   - Component-based architecture for UI elements

4. **Responsive Design**
   - Mobile-first responsive layouts
   - Touch-friendly navigation
   - Adaptive components across all screen sizes

### 🎨 Design System

- **Metronic Tailwind CSS** - Complete design system integration
- **Theme Support** - Light and dark mode switching
- **Custom Components** - Metronic-specific UI components
- **Icon System** - Comprehensive icon library integration

## Getting Started

### Automated Installation (Recommended)

For a fresh Ubuntu server, use our automated installation script:

```bash
# Download and run the installation script
wget https://raw.githubusercontent.com/i4edubd/ispsolution/main/install.sh
chmod +x install.sh
sudo bash install.sh
```

This installs everything you need (PHP, MySQL, Redis, Nginx, RADIUS, etc.) and configures the application automatically.

**📖 Full Installation Guide**: [INSTALLATION.md](INSTALLATION.md)

### Prerequisites
- Ubuntu 18.04+, 20.04+, 22.04+, or 24.04+ (for automated install)
- Docker and Docker Compose (for Docker setup)
- Git

## Production Deployment

### Build for Production
```bash
# Build optimized assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize Composer autoloader
composer install --optimize-autoloader --no-dev
```

### Environment Configuration

For production, update these critical settings in `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Use strong database passwords
DB_PASSWORD=strong_random_password
RADIUS_DB_PASSWORD=strong_random_password

# Configure real MikroTik router
MIKROTIK_HOST=your_router_ip
MIKROTIK_USERNAME=admin
MIKROTIK_PASSWORD=your_secure_password

# Enable HTTPS
SESSION_SECURE_COOKIE=true
```

## Quick Start with Docker

1. **Clone the repository**
```bash
git clone https://github.com/i4edubd/ispsolution.git
cd ispsolution
```

2. **Copy environment file**
```bash
cp .env.example .env
```

3. **Start Docker containers**
```bash
make up
```

4. **Install dependencies**
```bash
make install
```

5. **Generate application key**
```bash
docker-compose exec app php artisan key:generate
```

6. **Run migrations**
```bash
make migrate
```

7. **Seed sample data (optional)**
```bash
make seed
```

8. **Access the application**
- Application: http://localhost:8000
- Mailpit (email testing): http://localhost:8025

### Available Make Commands

```bash
make help              # Show all available commands
make up                # Start all containers
make down              # Stop all containers
make shell             # Enter app container
make logs              # Show container logs
make install           # Install composer and npm dependencies
make migrate           # Run database migrations
make seed              # Run database seeders
make test              # Run PHPUnit tests
make lint              # Run PHPStan and Pint
make build             # Build production assets
make ipam-cleanup      # Clean up expired IP allocations
make mikrotik-health   # Check MikroTik router health
```

### Manual Installation (Without Docker)

If you prefer to run without Docker:
```bash
cp .env.example .env
php artisan key:generate
```

### Manual Installation (Without Docker)

If you prefer to run without Docker:

1. **Prerequisites**: PHP 8.2+, Composer, Node.js, MySQL 8.0, Redis
2. **Install dependencies**: `composer install && npm install`
3. **Configure environment**: Copy `.env.example` to `.env` and update database credentials
4. **Generate key**: `php artisan key:generate`
5. **Run migrations**: `php artisan migrate`
6. **Build assets**: `npm run build`
7. **Start server**: `php artisan serve`

## Network Services Configuration

### RADIUS Setup

The application connects to a separate RADIUS database for authentication and accounting:

```env
RADIUS_DB_HOST=radius-db
RADIUS_DB_DATABASE=radius
RADIUS_DB_USERNAME=radius
RADIUS_DB_PASSWORD=radius_secret
RADIUS_PASSWORD_HASH=cleartext  # Options: cleartext, md5, sha1
```

**Key Features:**
- User authentication against radcheck table
- Session accounting (start/update/stop) to radacct table
- Automatic user synchronization to RADIUS database
- Support for RADIUS attributes in radreply

### MikroTik Integration

Configure your MikroTik router connection:

```env
MIKROTIK_HOST=192.168.88.1
MIKROTIK_PORT=8728
MIKROTIK_USERNAME=admin
MIKROTIK_PASSWORD=your_password
MIKROTIK_TIMEOUT=10
MIKROTIK_RETRY_ATTEMPTS=3
```

**Key Features:**
- Add/update/remove PPPoE users
- List active sessions
- Disconnect individual sessions
- Router health monitoring
- Automatic retry on connection failure

### IPAM Configuration

IP Address Management settings:

```env
IPAM_DEFAULT_POOL_SIZE=254
IPAM_CLEANUP_DAYS=30
IPAM_ALLOCATION_TTL=86400
IPAM_ALLOW_OVERLAP=false
```

**Key Features:**
- Create IP pools and subnets
- Allocate/release IP addresses
- Track allocation history
- Detect subnet overlaps
- Automatic cleanup of expired allocations

## API Endpoints

### IPAM API
```
GET    /api/v1/ipam/pools                # List all IP pools
POST   /api/v1/ipam/pools                # Create new pool
GET    /api/v1/ipam/subnets              # List all subnets
POST   /api/v1/ipam/subnets              # Create new subnet
GET    /api/v1/ipam/allocations          # List all allocations
POST   /api/v1/ipam/allocations          # Allocate IP address
DELETE /api/v1/ipam/allocations/{id}    # Release IP address
```

### RADIUS API
```
POST   /api/v1/radius/authenticate       # Authenticate user
POST   /api/v1/radius/accounting/start   # Start accounting session
POST   /api/v1/radius/accounting/update  # Update session
POST   /api/v1/radius/accounting/stop    # Stop session
GET    /api/v1/radius/users/{user}/stats # Get user statistics
```

### MikroTik API
```
GET    /api/v1/mikrotik/sessions         # List active sessions
DELETE /api/v1/mikrotik/sessions/{id}    # Disconnect session
GET    /api/v1/mikrotik/profiles         # List PPPoE profiles
GET    /api/v1/mikrotik/health           # Health check
```

## Artisan Commands

### IPAM Commands
```bash
php artisan ipam:cleanup              # Clean up expired IP allocations
```

### RADIUS Commands
```bash
php artisan radius:sync-user {userId} # Sync user to RADIUS database
```

### MikroTik Commands
```bash
php artisan mikrotik:health-check     # Check router connectivity
php artisan mikrotik:sync-sessions    # Sync active sessions from routers
```

### All Available Commands
```bash
# IPAM Management
php artisan ipam:cleanup --days=30 --force

# RADIUS Management
php artisan radius:sync-user {userId} --password=newpass
php artisan radius:sync-users --status=active --force

# MikroTik Management
php artisan mikrotik:health-check --router=1 --verbose
php artisan mikrotik:sync-sessions --router=1
```

## Scheduled Tasks

The following tasks run automatically via Laravel's scheduler:

- **IPAM Cleanup:** Daily at midnight - removes expired IP allocations
- **RADIUS Sync:** Every 5 minutes - syncs active users to RADIUS database
- **Session Sync:** Every minute - syncs active sessions from MikroTik routers
- **Health Check:** Every 15 minutes - checks MikroTik router connectivity

To enable the scheduler, add this to your crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Testing

```bash
# Run all tests
make test

# Run tests with coverage
make test-coverage

# Run specific test suite
docker-compose exec app php artisan test --testsuite=Feature

# Run specific test file
docker-compose exec app php artisan test tests/Feature/Services/IpamServiceTest.php
```

## Development Workflow

1. **Start containers**: `make up`
2. **Enter shell**: `make shell`
3. **Watch for changes**: `npm run dev`
4. **Run tests**: `make test`
5. **Check code quality**: `make lint`
6. **Fix code style**: `make fix`

## Architecture

### Service Layer

The application uses a service-oriented architecture with contracts:

- `app/Contracts/IpamServiceInterface.php` - IP address management
- `app/Contracts/RadiusServiceInterface.php` - RADIUS operations
- `app/Contracts/MikroTikServiceInterface.php` - Router management

Implementations:
- `app/Services/IpamService.php`
- `app/Services/RadiusService.php`
- `app/Services/MikroTikService.php`

### Database Schema

**Application Database:**
- `users` - System users
- `service_packages` - Bandwidth packages
- `ip_pools` - IP address pools
- `ip_subnets` - Network subnets
- `ip_allocations` - IP assignments
- `ip_allocation_histories` - Allocation tracking
- `radius_sessions` - Session cache

**RADIUS Database:**
- `radcheck` - User credentials
- `radreply` - User attributes
- `radacct` - Accounting records

## Documentation

- [Phase-by-Phase Implementation Checklist](docs/TODO_REIMPLEMENT.md)
- [Network Services Guide](docs/NETWORK_SERVICES.md)
- [API Documentation](docs/API.md)

## Docker Services

The docker-compose setup includes:

- **app**: PHP 8.2-FPM application container
- **nginx**: Web server (port 8000)
- **db**: MySQL 8.0 for application data (port 3306)
- **radius-db**: MySQL 8.0 for RADIUS data (port 3307)
- **redis**: Redis for caching and queues (port 6379)
- **mailpit**: Email testing interface (ports 1025, 8025)

## Troubleshooting

### Docker Issues

**Containers won't start:**
```bash
make down
docker system prune -f
make up
```

**Database connection errors:**
- Ensure containers are running: `docker-compose ps`
- Check database credentials in `.env`
- Wait 30 seconds after `make up` for databases to initialize

**Permission errors:**
```bash
docker-compose exec app chown -R www:www /var/www/html/storage
docker-compose exec app chmod -R 755 /var/www/html/storage
```

### MikroTik Connection

If MikroTik health check fails:
1. Verify router is accessible from Docker network
2. Check API service is enabled on router
3. Verify credentials in `.env`
4. Check firewall rules allow port 8728

### RADIUS Integration

If authentication fails:
1. Verify radius-db container is running
2. Check RADIUS database migrations ran successfully
3. Sync a test user: `php artisan radius:sync-user 1`
4. Verify radcheck table has entries



## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Quality Standards

- Run tests before committing: `make test` or `php artisan test`
- Follow PSR-12 coding standards: `vendor/bin/pint`
- Run static analysis: `vendor/bin/phpstan analyse`
- Write meaningful commit messages
- Add tests for new features
- Update documentation as needed

### Continuous Integration

All pull requests are automatically tested using GitHub Actions:

- **Test Workflow** - Runs unit, feature, and integration tests on PHP 8.2 and 8.3
- **Lint Workflow** - Checks code quality with PHPStan and Laravel Pint
- **Integration Workflow** - Tests with full Docker environment including databases

See workflow files in `.github/workflows/` for details.


## License

This project is licensed under the MIT License.

## Support

For questions and support:
- Review the [implementation checklist](docs/TODO_REIMPLEMENT.md)
- Check the troubleshooting section above
- Open an issue on GitHub
- Review Laravel 12 documentation: https://laravel.com/docs/12.x
