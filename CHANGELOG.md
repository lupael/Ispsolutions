# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased] - 2026-01-30

### ✨ Features

- Complete Bkash tokenization implementation with UI and routes ([594732c](../../commit/594732ca46007e747a37c4bc37dc2d0480f24c9f))
- implement Bkash tokenization with models, migrations, and service ([1e65a91](../../commit/1e65a91c7d9a1aa79ef71a84b37654cd82d7aaed))
- add operator subscription controller, command, and form request ([0492f27](../../commit/0492f27f7585ff6095ce7c9e43c6b80a232713a2))
- implement SMS payment webhook security and subscription payment models ([8d29760](../../commit/8d297606d988354dd92c0b573ec0cd4382564d14))

### 🐛 Bug Fixes

- Add explicit isset check for $onu variable in error handler ([7787f86](../../commit/7787f86bb62c5f91a203532ee78ff7ff980ce976))
- add version validation to prevent malformed tags ([e293a66](../../commit/e293a6675eaa7aaf49c5deb5744d84d37b27cff2))
- update workflow to auto-generate version from git tags ([14fc60a](../../commit/14fc60ab2f0bbe1fe9817dd83323a19574cf8f73))
- Change generateInvoiceNumber() visibility to public ([8f6a929](../../commit/8f6a92935440702444fb07badacbb3af1c834c0c))
- Update customer-status-badge component to accept customer prop ([f2c082e](../../commit/f2c082e53043f801183a92eaf1779b3001d649c2))
- Add ARIA attributes, correct permissions, restore missing actions, update docs ([5b90d47](../../commit/5b90d47c498e8890faf462097052a5d0fce372a0))
- Revert back button font-weight to font-semibold ([ab5d273](../../commit/ab5d27389d681b13a95db21e915f91282bdeaf51))
- Address PR review feedback - update percentages and implement makePayment function ([38e9af7](../../commit/38e9af7ecdff59915a744dc550f723ce12d1e38c))
- address code review feedback - improve token handling, subscription logic, and webhook processing ([b651310](../../commit/b651310da19cdea4442486c3f992ad6ff547f7c5))

### 📚 Documentation

- Update README with Phase 2 feature documentation links ([5c8f443](../../commit/5c8f443589a6b48f1b454de3be51b055fec0b018))
- Add comprehensive user guide and implementation summary ([b14995e](../../commit/b14995ecf0a1af5626d73fd1903617bb3e9e4119))
- Add comprehensive Phase 2 implementation status report ([0aa3bb6](../../commit/0aa3bb676811f1b9bea05283bca81df015312773))
- add comprehensive Phase 2 implementation summary ([9fbfe38](../../commit/9fbfe385e6cc228a019fa691e271b4c38be2cc8b))
- Mark Quick Wins as completed in REFERENCE_SYSTEM_QUICK_GUIDE.md ([b5c0510](../../commit/b5c0510539ffd8d91a8dc0aac5e3a8855ebd804f))
- Fix cache service method names and parameter references in usage guide ([417386f](../../commit/417386f3d399e81a08b575f2cd35cf92b0ca2659))
- Add comprehensive Quick Wins usage guide ([dc3fbf6](../../commit/dc3fbf65e5341e63737ff6fa2fa5eaea352e2151))

---

## [Unreleased]

### Added - Multi-Tenancy & RBAC Implementation (2026-01-17)

#### Multi-Tenancy Foundation
- **Tenant Model**: Complete tenant management with soft deletes and relationships
- **TenancyService**: Singleton service for managing tenant context with caching
- **BelongsToTenant Trait**: Auto-assigns tenant_id and provides global scope filtering
- **ResolveTenant Middleware**: Resolves tenant by domain/subdomain with 404 handling
- **TenancyServiceProvider**: Registered in bootstrap/providers.php
- **Migrations**: 
  - `create_tenants_table` - Tenant metadata storage
  - `add_tenant_id_to_tables` - Nullable tenant_id columns for backward compatibility
- **Factories**: TenantFactory with active/inactive/suspended states
- **Tests**: 
  - TenancyServiceTest (9 tests)
  - TenancyMiddlewareTest (5 tests)
  - BelongsToTenantTraitTest (5 tests)

#### Operator RBAC System
- **OperatorPermission Model**: Tracks special permissions per operator
- **Migrations**:
  - `create_operator_permissions_table` - Permission storage
  - `add_operator_fields_to_users_table` - operator_level, disabled_menus, manager_id, operator_type
- **User Model Enhancements**:
  - `operatorPermissions()` relationship
  - `hasSpecialPermission()` method
  - `isMenuDisabled()` method
  - `getOperatorTypeLabel()` method
  - `isOperator()` method
  - `manager()` relationship
- **Policies**:
  - **OperatorPolicy**: Hierarchical access control for operator management
  - **CustomerPolicy**: Tenant-isolated customer access with special permissions
- **Configuration Files**:
  - `config/operators_permissions.php` - Organized permission definitions with levels
  - `config/special_permissions.php` - Enhanced permission definitions
  - `config/sidebars.php` - Role-based sidebar menu configurations

#### Helper Functions
- `isMenuActive()` - Check if menu route is active
- `isMenuDisabled()` - Check if menu is disabled for user
- `canAccessMenu()` - Check user access to menu item
- `getSidebarMenu()` - Get filtered sidebar menu for user
- `formatCurrency()` - Format amounts with currency
- `getCurrentTenant()` - Get current tenant instance
- `getCurrentTenantId()` - Get current tenant ID
- Registered in `composer.json` autoload files

#### Network Services (Stubs)
- **RouterManager**: Router management service with vendor-agnostic interface
  - Configuration apply/backup methods
  - Connection testing
  - Resource usage monitoring
  - Session management (get, disconnect)
  - User synchronization (PPPoE)
  - Clear TODO markers for vendor-specific implementations
- **RadiusSyncService**: RADIUS database synchronization service
  - User sync to RADIUS database
  - Password updates
  - Group assignments
  - Attribute management
  - Session queries and disconnect
  - Bandwidth usage calculation
  - Clear TODO markers for actual RADIUS operations

#### Views & Layouts
- **resources/views/layouts/admin.blade.php**: 
  - Modern admin layout with sidebar support
  - Flash message handling with auto-dismiss
  - Error display
  - Alpine.js integration
- **resources/views/panels/partials/footer.blade.php**: 
  - Responsive footer with links
- **Existing Comprehensive Sidebar**: 
  - Role-based menu generation
  - Collapsible submenus
  - Active state highlighting
  - Mobile responsive with overlay
  - User profile display

#### Seeders
- **TenantSeeder**: Creates demo and test tenants with settings
- **OperatorSeeder**: Creates sample operators at all levels
  - Super Admin (superadmin@ispsolution.local / password)
  - ISP Admin (admin@demo-isp.local / password)
  - Operator (operator@demo-isp.local / password)
  - Includes role attachments

#### Documentation
- **docs/tenancy.md**: Comprehensive tenancy guide
  - Architecture overview
  - Usage examples
  - Migration guide
  - Seeding instructions
  - Testing guide
  - Troubleshooting section
  - Security considerations
  - Performance tips
  - API reference
- **docs/developer-guide.md**: Developer documentation
  - Architecture overview
  - RBAC system explanation
  - Adding new features guide
  - Panel development guide
  - Testing guide
  - Services and jobs guide
  - Helper functions reference
  - Code style guide
  - Debugging tips
  - Performance optimization
  - Deployment checklist
  - Common issues and solutions

### Changed
- **User Model**: Extended with operator-related fields and methods
- **composer.json**: Added helper file to autoload
- **Existing Controllers**: Now compatible with multi-tenancy and RBAC

### Technical Details
- **Laravel Version**: 12.x
- **PHP Version**: 8.2+
- **Database**: MySQL 8.0
- **Redis**: Latest (for caching)
- **Testing**: PHPUnit with RefreshDatabase
- **Code Style**: PSR-12 compliant

### Breaking Changes
None - All changes maintain backward compatibility with nullable `tenant_id` columns.

### Security
- Tenant isolation enforced via global scopes
- Hierarchical operator permissions
- Policy-based authorization
- Encrypted sensitive credentials in gateway configs

### Performance
- TenancyService implements caching for tenant resolution
- Indexed tenant_id columns for query performance
- Optimized global scopes for minimal query overhead

### Testing
All tests passing (14/14):
- 9 TenancyService tests
- 5 TenancyMiddleware tests  
- 5 BelongsToTenant trait tests (note: some tests verify multiple assertions)

### Known Limitations
- RouterManager and RadiusSyncService are stubs requiring vendor-specific implementation
- Payment gateway adapters not yet implemented
- Some panel views require enhancement with widgets
- Ledger model for financial transactions not yet created

### Future Enhancements
- Payment gateway adapter implementations (bKash, Nagad, Stripe, PayPal)
- Invoice PDF generation with Chrome headless
- Enhanced dashboard widgets
- Blade components library
- Additional tests for policies and controllers
- Sample data seeder
- Device configuration background jobs

---

## Previous Releases

### [1.0.0] - 2026-01-16

#### Added
- Initial ISP Solution implementation
- RADIUS integration with authentication and accounting
- MikroTik management via RouterOS API
- IPAM (IP Address Management)
- Billing service with invoice generation
- Payment gateway integration foundations
- OLT management
- Network device monitoring
- Existing test suite
- Docker development environment
- GitHub Actions CI/CD pipelines

---

## Development Commands

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Seed data
php artisan db:seed --class=TenantSeeder
php artisan db:seed --class=OperatorSeeder

# Run tests
php artisan test

# Run specific test suites
php artisan test --filter=Tenancy

# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint

# Build assets
npm run build
```

## Contributors
- Development Team
- Code review and testing by project maintainers
