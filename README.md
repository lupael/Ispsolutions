# ISP Solution - Network Services Management

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://www.php.net/)

A comprehensive ISP (Internet Service Provider) management system built with Laravel 12, featuring multi-tenancy, RADIUS authentication, MikroTik router integration, and IP address management (IPAM).

## 🎯 Project Overview

**ISP Solution** is a modern, production-ready platform designed to manage every aspect of an Internet Service Provider's operations. Built with Laravel 12 and wrapped in a beautiful Metronic Tailwind CSS interface, it provides:

- 🔐 **Multi-tenant architecture** with 12-level role hierarchy
- 📡 **RADIUS integration** for authentication and accounting
- 🌐 **MikroTik RouterOS API** integration for network management
- 📊 **Real-time monitoring** of sessions and bandwidth usage
- 💼 **Comprehensive billing** and customer management
- 🎨 **Modern UI/UX** with responsive design
- 🚀 **96.4% feature complete** (400/415 features implemented)

## 🛠️ Tech Stack

| Technology | Version | Purpose |
|------------|---------|---------|
| **Laravel** | 12.x | Backend framework |
| **PHP** | 8.2+ | Programming language |
| **MySQL** | 8.0 | Application & RADIUS databases |
| **Redis** | Latest | Caching and queue management |
| **Tailwind CSS** | 4.x | Frontend styling |
| **Vite** | 7.x | Asset building |
| **Docker** | Latest | Containerized environment |
| **Node.js** | LTS | JavaScript runtime |

## 🚀 Quick Start

### Option 1: Automated Installation (Recommended)

For a fresh Ubuntu server (18.04+, 20.04+, 22.04+, or 24.04+):

```bash
# Download and run the installation script
wget https://raw.githubusercontent.com/i4edubd/ispsolution/main/install.sh
chmod +x install.sh
sudo bash install.sh
```

This script installs PHP, MySQL, Redis, Nginx, RADIUS, and configures everything automatically.

📖 **Complete guide**: [INSTALLATION.md](INSTALLATION.md)

### Option 2: Docker Setup

```bash
# 1. Clone the repository
git clone https://github.com/i4edubd/ispsolution.git
cd ispsolution

# 2. Setup environment
cp .env.example .env

# 3. Start containers
make up

# 4. Install dependencies
make install

# 5. Setup application
docker-compose exec app php artisan key:generate
make migrate
make seed  # Optional: Load demo data

# 6. Access application
# → Application: http://localhost:8000
# → Mailpit: http://localhost:8025
```

### Option 3: Manual Installation

**Prerequisites**: PHP 8.2+, Composer, Node.js, MySQL 8.0, Redis

```bash
# Install dependencies
composer install && npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate

# Build assets
npm run build

# Start development server
php artisan serve
```

## ✨ Key Features

### 🎉 Major Milestone - January 2026
**400/415 Features Complete (96.4%)**

- ✅ **400 features implemented** - Up from 200 features
- ✅ **95% production ready** - Up from 75%
- ✅ **A-Z feature coverage** through letter W (Web)
- 🎯 **Only 15 features remaining** (3.6%)

📄 See [FEATURE_IMPLEMENTATION_STATUS.md](FEATURE_IMPLEMENTATION_STATUS.md) | [CHANGELOG.md](CHANGELOG.md)

### 📡 Network Services

- **RADIUS Integration**
  - FreeRADIUS authentication and accounting
  - User synchronization to RADIUS database
  - Support for RADIUS attributes (radcheck, radreply, radacct)
  - Session tracking and statistics

- **MikroTik Management**
  - PPPoE user management via RouterOS API
  - Real-time session monitoring
  - Remote session disconnection
  - Router health monitoring
  - Automatic retry on connection failure

- **IPAM (IP Address Management)**
  - IP pool and subnet creation
  - IP allocation and release
  - Allocation history tracking
  - Subnet overlap detection
  - Automatic cleanup of expired allocations

- **Session Monitoring**
  - Real-time active session tracking
  - Bandwidth usage monitoring
  - Connection history
  - Session statistics and reports

### 🎨 User Interface

- Modern admin interface based on Metronic Tailwind HTML
- Fully responsive design (desktop, tablet, mobile)
- Light and dark mode support
- Real-time monitoring dashboards
- Interactive IP allocation management
- Component-based architecture

## 🔐 Multi-Tenancy & Role System

The platform implements a comprehensive **12-role hierarchy** with strict data isolation and permission-based access control.

### Role Hierarchy

| Level | Role | Access Scope | Can Manage |
|-------|------|--------------|------------|
| **0** | Developer | All tenants (supreme authority) | Super Admins |
| **10** | Super Admin | Own tenants only | Admins |
| **20** | Admin | Own ISP data | Operators, Sub-Operators, Staff |
| **30** | Operator | Own + sub-operator customers | Sub-Operators, Customers |
| **40** | Sub-Operator | Own customers only | Customers |
| **50** | Manager | Permission-based (view/edit) | None |
| **70** | Accountant | Financial data (view-only) | None |
| **80** | Staff | Permission-based (view/edit) | None |
| **100** | Customer | Self-service only | None |

### Data Isolation

- **Developer**: Access all tenants and data (system-wide authority)
- **Super Admin**: Only tenants they created
- **Admin**: Only data within their ISP tenant
- **Operator**: Own customers + sub-operator customers
- **Sub-Operator**: Only their own customers
- **View-Only Roles**: Permission-based read access within tenant

### Quick Code Examples

```php
// Check user role
if (auth()->user()->isDeveloper()) { /* ... */ }
if (auth()->user()->isAdmin()) { /* ... */ }

// Get accessible customers (automatically scoped by role)
$customers = auth()->user()->accessibleCustomers()->paginate(50);

// Check hierarchy permission
if (auth()->user()->canManage($otherUser)) { /* ... */ }
```

### Demo Accounts

All demo accounts use password: **`password`**

| Email | Role | Level |
|-------|------|-------|
| developer@ispbills.com | Developer | 0 |
| superadmin@ispbills.com | Super Admin | 10 |
| admin@ispbills.com | Admin | 20 |
| operator@ispbills.com | Operator | 30 |
| suboperator@ispbills.com | Sub-Operator | 40 |
| customer@ispbills.com | Customer | 100 |

```bash
# Seed demo data
php artisan db:seed --class=DemoSeeder

# Seed roles
php artisan db:seed --class=RoleSeeder
```

**📖 Documentation:**
- [ROLE_SYSTEM.md](docs/technical/ROLE_SYSTEM.md) - Complete role system specification v3.1
- [DATA_ISOLATION.md](docs/technical/DATA_ISOLATION.md) - Data isolation rules
- [ROLES_AND_PERMISSIONS.md](docs/ROLES_AND_PERMISSIONS.md) - Detailed permissions guide

## ⚙️ Configuration

### Production Deployment

**Build for production:**
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

**Production environment settings (`.env`):**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Strong database passwords
DB_PASSWORD=strong_random_password
RADIUS_DB_PASSWORD=strong_random_password

# MikroTik router configuration
MIKROTIK_HOST=your_router_ip
MIKROTIK_USERNAME=admin
MIKROTIK_PASSWORD=your_secure_password

# Enable HTTPS
SESSION_SECURE_COOKIE=true
```

### Network Services

#### RADIUS Setup

Configure RADIUS database connection:

```env
RADIUS_DB_HOST=radius-db
RADIUS_DB_DATABASE=radius
RADIUS_DB_USERNAME=radius
RADIUS_DB_PASSWORD=radius_secret
RADIUS_PASSWORD_HASH=cleartext  # Options: cleartext, md5, sha1
```

**Capabilities:**
- User authentication (radcheck table)
- Session accounting (radacct table)
- Automatic user synchronization
- RADIUS attributes support (radreply)

#### MikroTik Integration

Configure RouterOS API connection:

```env
MIKROTIK_HOST=192.168.88.1
MIKROTIK_PORT=8728
MIKROTIK_USERNAME=admin
MIKROTIK_PASSWORD=your_password
MIKROTIK_TIMEOUT=10
MIKROTIK_RETRY_ATTEMPTS=3
```

**Capabilities:**
- PPPoE user management (add/update/remove)
- Active session listing
- Session disconnection
- Router health monitoring
- Automatic connection retry

#### IPAM Configuration

IP Address Management settings:

```env
IPAM_DEFAULT_POOL_SIZE=254
IPAM_CLEANUP_DAYS=30
IPAM_ALLOCATION_TTL=86400
IPAM_ALLOW_OVERLAP=false
```

**Capabilities:**
- IP pool and subnet creation
- IP address allocation/release
- Allocation history tracking
- Subnet overlap detection
- Automatic cleanup of expired allocations

## 🔌 API Reference

### IPAM API
```http
GET    /api/v1/ipam/pools                 # List all IP pools
POST   /api/v1/ipam/pools                 # Create new pool
GET    /api/v1/ipam/subnets               # List all subnets
POST   /api/v1/ipam/subnets               # Create new subnet
GET    /api/v1/ipam/allocations           # List all allocations
POST   /api/v1/ipam/allocations           # Allocate IP address
DELETE /api/v1/ipam/allocations/{id}     # Release IP address
```

### RADIUS API
```http
POST   /api/v1/radius/authenticate        # Authenticate user
POST   /api/v1/radius/accounting/start    # Start accounting session
POST   /api/v1/radius/accounting/update   # Update session
POST   /api/v1/radius/accounting/stop     # Stop session
GET    /api/v1/radius/users/{user}/stats  # Get user statistics
```

### MikroTik API
```http
GET    /api/v1/mikrotik/sessions          # List active sessions
DELETE /api/v1/mikrotik/sessions/{id}     # Disconnect session
GET    /api/v1/mikrotik/profiles          # List PPPoE profiles
GET    /api/v1/mikrotik/health            # Health check
```

📖 **Complete API documentation**: [docs/API.md](docs/API.md)

## 🛠️ Artisan Commands

### IPAM Management
```bash
php artisan ipam:cleanup              # Clean up expired IP allocations
php artisan ipam:cleanup --days=30    # Clean up allocations older than 30 days
php artisan ipam:cleanup --force      # Skip confirmation prompt
```

### RADIUS Management
```bash
php artisan radius:sync-user {userId}         # Sync user to RADIUS database
php artisan radius:sync-user 1 --password=newpass  # Sync with new password
php artisan radius:sync-users --status=active      # Sync all active users
php artisan radius:sync-users --force              # Force sync all users
```

### MikroTik Management
```bash
php artisan mikrotik:health-check              # Check router connectivity
php artisan mikrotik:health-check --router=1   # Check specific router
php artisan mikrotik:health-check --verbose    # Detailed output
php artisan mikrotik:sync-sessions             # Sync active sessions
php artisan mikrotik:sync-sessions --router=1  # Sync specific router
```

### Scheduled Tasks

The following tasks run automatically via Laravel's scheduler:

| Task | Frequency | Description |
|------|-----------|-------------|
| IPAM Cleanup | Daily (midnight) | Remove expired IP allocations |
| RADIUS Sync | Every 5 minutes | Sync active users to RADIUS |
| Session Sync | Every minute | Sync sessions from MikroTik |
| Health Check | Every 15 minutes | Check MikroTik connectivity |

**Enable scheduler:** Add to crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 🧪 Testing & Development

### Running Tests

```bash
# Run all tests
make test

# Run with coverage
make test-coverage

# Run specific test suite
docker-compose exec app php artisan test --testsuite=Feature

# Run specific test file
docker-compose exec app php artisan test tests/Feature/Services/IpamServiceTest.php
```

### Development Workflow

```bash
make up                # Start containers
make shell             # Enter app container
npm run dev            # Watch for changes (hot reload)
make test              # Run tests
make lint              # Check code quality
make fix               # Fix code style issues
```

### Code Quality

- **PSR-12 Coding Standards**: Use `vendor/bin/pint` to check/fix
- **Static Analysis**: Run `vendor/bin/phpstan analyse`
- **Commit Format**: Follow [Conventional Commits](https://www.conventionalcommits.org/)

📖 **Testing guide**: [docs/TESTING.md](docs/TESTING.md)

## 🏗️ Architecture

### Service Layer

Service-oriented architecture with contracts and implementations:

**Contracts:**
- `app/Contracts/IpamServiceInterface.php` - IP address management
- `app/Contracts/RadiusServiceInterface.php` - RADIUS operations
- `app/Contracts/MikroTikServiceInterface.php` - Router management

**Implementations:**
- `app/Services/IpamService.php`
- `app/Services/RadiusService.php`
- `app/Services/MikroTikService.php`

### Database Schema

#### Application Database
| Table | Purpose |
|-------|---------|
| `users` | System users |
| `service_packages` | Bandwidth packages |
| `ip_pools` | IP address pools |
| `ip_subnets` | Network subnets |
| `ip_allocations` | IP assignments |
| `ip_allocation_histories` | Allocation tracking |
| `radius_sessions` | Session cache |

#### RADIUS Database
| Table | Purpose |
|-------|---------|
| `radcheck` | User credentials |
| `radreply` | User attributes |
| `radacct` | Accounting records |

### Docker Services

| Service | Description | Port |
|---------|-------------|------|
| **app** | PHP 8.2-FPM application | - |
| **nginx** | Web server | 8000 |
| **db** | MySQL 8.0 (application) | 3306 |
| **radius-db** | MySQL 8.0 (RADIUS) | 3307 |
| **redis** | Caching and queues | 6379 |
| **mailpit** | Email testing | 1025, 8025 |

## 🔧 Troubleshooting

### Docker Issues

**Containers won't start:**
```bash
make down
docker system prune -f
make up
```

**Database connection errors:**
- Check containers: `docker-compose ps`
- Verify `.env` credentials
- Wait 30 seconds after startup for database initialization

**Permission errors:**
```bash
docker-compose exec app chown -R www:www /var/www/html/storage
docker-compose exec app chmod -R 755 /var/www/html/storage
```

### MikroTik Connection Issues

If health check fails:
1. Verify router accessibility from Docker network
2. Enable API service on router
3. Check credentials in `.env`
4. Verify firewall allows port 8728

### RADIUS Integration Issues

If authentication fails:
1. Check `radius-db` container is running
2. Verify RADIUS database migrations ran
3. Test sync: `php artisan radius:sync-user 1`
4. Confirm entries in `radcheck` table

## 📚 Documentation

### Getting Started
- **[INSTALLATION.md](INSTALLATION.md)** - Complete automated installation guide
- **[POST_DEPLOYMENT_STEPS.md](POST_DEPLOYMENT_STEPS.md)** - Essential post-deployment steps
- **[DOCUMENTATION_INDEX.md](DOCUMENTATION_INDEX.md)** - Complete documentation index
- **[PROJECT_STATUS.md](PROJECT_STATUS.md)** - Current status and roadmap

### User Guides by Role
- **[Developer Guide](docs/guides/DEVELOPER_GUIDE.md)** - Level 0: System development
- **[Super Admin Guide](docs/guides/SUPERADMIN_GUIDE.md)** - Level 10: Tenant management
- **[Admin Guide](docs/guides/ADMIN_GUIDE.md)** - Level 20: ISP operations
- **[Operator Guide](docs/guides/OPERATOR_GUIDE.md)** - Level 30: Area management
- **[Sub-Operator Guide](docs/guides/SUBOPERATOR_GUIDE.md)** - Level 40: Local management
- **[Manager Guide](docs/guides/MANAGER_GUIDE.md)** - Level 50: Oversight
- **[Staff Guide](docs/guides/STAFF_GUIDE.md)** - Level 80: Support
- **[Customer Guide](docs/guides/CUSTOMER_GUIDE.md)** - Level 100: Self-service

### Technical Documentation
- **[API Documentation](docs/API.md)** - Complete REST API reference
- **[Testing Guide](docs/TESTING.md)** - How to run and write tests
- **[ROLES_AND_PERMISSIONS.md](docs/ROLES_AND_PERMISSIONS.md)** - Role system details
- **[ROLE_SYSTEM.md](docs/technical/ROLE_SYSTEM.md)** - Complete specification v3.1
- **[DATA_ISOLATION.md](docs/technical/DATA_ISOLATION.md)** - Data isolation rules
- **[MULTI_TENANCY_ISOLATION.md](docs/technical/MULTI_TENANCY_ISOLATION.md)** - Multi-tenancy architecture

### Feature Documentation
- **[Network Services Guide](docs/NETWORK_SERVICES.md)** - RADIUS, MikroTik, IPAM
- **[OLT Service Guide](docs/OLT_SERVICE_GUIDE.md)** - OLT/ONU management
- **[FEATURE_IMPLEMENTATION_STATUS.md](FEATURE_IMPLEMENTATION_STATUS.md)** - Feature status
- **[PANELS_SPECIFICATION.md](PANELS_SPECIFICATION.md)** - Panel specifications

### MikroTik Integration
- **[MIKROTIK_QUICKSTART.md](MIKROTIK_QUICKSTART.md)** - Quick start guide
- **[MIKROTIK_ADVANCED_FEATURES.md](MIKROTIK_ADVANCED_FEATURES.md)** - Advanced features

### Contributing
- **[CONTRIBUTING.md](CONTRIBUTING.md)** - Contribution guidelines
- **[CHANGELOG.md](CHANGELOG.md)** - Version history
- **[Changelog Guide](docs/CHANGELOG_GUIDE.md)** - Commit message format

## 🤝 Contributing

We welcome contributions! Here's how to get started:

### Quick Start

1. **Fork** the repository
2. **Create** a feature branch: `git checkout -b feature/amazing-feature`
3. **Commit** using [Conventional Commits](https://www.conventionalcommits.org/):
   ```bash
   git commit -m "feat(billing): add PayPal integration"
   ```
4. **Push** to your branch: `git push origin feature/amazing-feature`
5. **Open** a Pull Request

### Commit Message Format

We use [Conventional Commits](https://www.conventionalcommits.org/) for automatic changelog generation:

```
<type>(<scope>): <description>

Examples:
feat(auth): add two-factor authentication
fix(billing): resolve invoice calculation bug
docs: update API documentation
```

**Types:** `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `chore`

### Code Quality Standards

Before committing:
- ✅ Run tests: `make test` or `php artisan test`
- ✅ Check style: `vendor/bin/pint`
- ✅ Run analysis: `vendor/bin/phpstan analyse`
- ✅ Write tests for new features
- ✅ Update documentation

### Continuous Integration

All PRs are automatically tested:
- **Test Workflow** - PHP 8.2 and 8.3 unit/feature/integration tests
- **Lint Workflow** - PHPStan and Laravel Pint checks
- **Integration Workflow** - Full Docker environment testing
- **Changelog Workflow** - Automatic changelog generation

📖 **Full guidelines**: [CONTRIBUTING.md](CONTRIBUTING.md) | [Changelog Guide](docs/CHANGELOG_GUIDE.md)

## 📄 License

This project is licensed under the MIT License.

## 💬 Support

Need help? Here's where to find it:

- 📖 **[Documentation Index](DOCUMENTATION_INDEX.md)** - All available guides
- 📊 **[Implementation Status](docs/IMPLEMENTATION_STATUS.md)** - Current progress
- 🔍 **Troubleshooting** - See section above
- 🐛 **[GitHub Issues](https://github.com/i4edubd/ispsolution/issues)** - Report bugs
- 📚 **[Laravel Docs](https://laravel.com/docs/12.x)** - Laravel 12 documentation

---

<div align="center">

**[Documentation](DOCUMENTATION_INDEX.md)** • **[Installation](INSTALLATION.md)** • **[Contributing](CONTRIBUTING.md)** • **[Changelog](CHANGELOG.md)**

Made with ❤️ by the ISP Solution team

</div>
