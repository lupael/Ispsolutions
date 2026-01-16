# ISP Solution - Network Services Management

This project is an ISP (Internet Service Provider) management system built with Laravel 12, featuring comprehensive billing and network monitoring capabilities.

## Project Overview

**Goal**: Provide a complete ISP management solution with RADIUS authentication, MikroTik router integration, and IP address management (IPAM), all wrapped in a modern Tailwind CSS interface based on the Metronic design system.

## Features

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

## Tech Stack

- **Laravel**: 12.x (Latest)
- **PHP**: 8.2+
- **Database**: MySQL 8.0 (Application + RADIUS databases)
- **Redis**: Latest (Caching and Queue management)
- **Tailwind CSS**: 4.x
- **Vite**: 7.x for asset building
- **Docker**: Containerized development environment
- **Node.js**: Latest LTS version

## Project Structure

```
app/Http/Controllers/
├── Demo1Controller.php
├── Demo2Controller.php
├── ...
└── Demo10Controller.php

resources/views/
├── layouts/
│   ├── partials/
│   │   ├── head.blade.php
│   │   └── scripts.blade.php
│   ├── demo1/
│   │   ├── base.blade.php
│   │   └── partials/
│   ├── demo2/
│   │   ├── base.blade.php
│   │   └── partials/
│   └── ... (demo3-demo10)
├── pages/
│   ├── demo1/
│   │   └── index.blade.php
│   ├── demo2/
│   │   └── index.blade.php
│   └── ... (demo3-demo10)
└── components/
    ├── demo1/
    ├── demo2/
    ├── ... (demo3-demo10)
    └── shared/

public/assets/
├── css/
│   └── styles.css
├── js/
│   ├── core.bundle.js
│   └── layouts/
│       ├── demo1.js
│       ├── demo2.js
│       └── ... (demo3-demo10.js)
├── media/
└── vendors/
```

## Demo Layouts

This integration includes 10 complete demo layouts, each showcasing different UI patterns:

- **Demo 1**: Sidebar Layout - Traditional admin dashboard with sidebar navigation
- **Demo 2**: Header Layout - Modern dashboard with top navigation
- **Demo 3**: Minimal Layout - Clean, minimalist design approach
- **Demo 4**: Creative Layout - Creative and artistic dashboard design
- **Demo 5**: Modern Layout - Contemporary UI with modern elements
- **Demo 6**: Professional Layout - Business-focused professional design
- **Demo 7**: Corporate Layout - Enterprise-grade corporate dashboard
- **Demo 8**: Executive Layout - Executive-level dashboard interface
- **Demo 9**: Premium Layout - Premium design with advanced components
- **Demo 10**: Ultimate Layout - Most comprehensive layout with all features

## Features

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

### Prerequisites
- Docker and Docker Compose
- Git

### Quick Start with Docker

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
- [Network Services Guide](docs/NETWORK_SERVICES.md) (coming soon)
- [API Documentation](docs/API.md) (coming soon)

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

## Production Deployment

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

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Quality Standards

- Run tests before committing: `make test`
- Follow PSR-12 coding standards: `make lint`
- Write meaningful commit messages
- Add tests for new features

## License

This project is licensed under the MIT License.

## Support

For questions and support:
- Review the [implementation checklist](docs/TODO_REIMPLEMENT.md)
- Check the troubleshooting section above
- Open an issue on GitHub
- Review Laravel 12 documentation: https://laravel.com/docs/12.x
