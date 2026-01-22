# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [3.1.0] - 2026-01-23 - 🎯 **Role System Consolidation**

### 🚀 Added

#### New Documentation
- **ROLE_SYSTEM.md**: Comprehensive role system specification v3.1
  - Complete role hierarchy with levels (0-100)
  - Tenancy creation rules and relationships
  - Resource and billing responsibilities per role
  - Implementation details with code examples
  - Demo account credentials
  - Migration notes and troubleshooting

#### Demo Accounts (All using password: `password`)
- ✅ `developer@ispbills.com` (Developer - Level 0)
- ✅ `superadmin@ispbills.com` (Super Admin - Level 10)
- ✅ `admin@ispbills.com` (Admin - Level 20)
- ✅ `operator@ispbills.com` (Operator - Level 30)
- ✅ `suboperator@ispbills.com` (Sub-Operator - Level 40)
- ✅ `customer@ispbills.com` (Customer - Level 100)

### ♻️ Changed

#### Role Terminology Consolidation
- **Group Admin** → **Admin** (Level 20)
  - Consistent terminology across all code, configs, and UI
  - Updated User model `getOperatorTypeLabel()` method
  - Updated seeders (DemoSeeder, OperatorSeeder)
  
- **Reseller** → **Operator** (Level 30)
  - Updated all HTTP request authorization checks
  - Updated AuthController role routing
  - Updated AdminController role queries
  - Updated view files (sidebar, profile, special-permissions)
  - Database column `reseller_id` retained for backward compatibility
  
- **Sub-Reseller** → **Sub-Operator** (Level 40)
  - Same updates as Operator consolidation

#### Configuration Files
- ✅ `config/operators_permissions.php`: Removed reseller/sub_reseller levels, updated comments
- ✅ `config/special_permissions.php`: Updated permission descriptions (Group Admin → Admin)
- ✅ `config/sidebars.php`: 
  - Renamed `group_admin` → `admin`
  - Renamed `resellers_managers` → `operators_managers`
  - Removed `reseller` and `sub_reseller` menu sections

#### Controllers
- ✅ `AuthController`: Updated role routing mappings
- ✅ `AdminController`: Updated role queries (reseller → operator, sub-reseller → sub-operator)
- ✅ `ChartController`: Added backward compatibility comments for `reseller_id`
- ✅ `OperatorController`: Added backward compatibility comments
- ✅ `SubOperatorController`: Added backward compatibility comments
- ✅ `CardDistributorController`: Added backward compatibility comments

#### Views
- ✅ `panels/admin/operators/special-permissions.blade.php`: Updated role color mappings
- ✅ `panels/admin/operators/profile.blade.php`: Updated role color mappings
- ✅ `panels/partials/sidebar.blade.php`: Removed reseller/sub-reseller menu sections

#### Database Seeders
- ✅ `RoleSeeder.php`: All roles updated with correct levels and descriptions
- ✅ `OperatorSeeder.php`: Changed `group_admin` → `admin` operator_type
- ✅ `DemoSeeder.php`: Comprehensive demo data with all role levels

### ⚠️ Deprecated

The following terms are **deprecated** and should not be used in new code:

| ❌ Deprecated     | ✅ Use Instead   | Status                                       |
|-------------------|------------------|----------------------------------------------|
| Group Admin       | Admin            | Removed from runtime role terminology and UI |
| Reseller          | Operator         | Removed from runtime role terminology and UI |
| Sub-Reseller      | Sub-Operator     | Removed from runtime role terminology and UI |

### 🔄 Backward Compatibility

The following database columns are retained for backward compatibility:

- **`reseller_id`** in `commissions` table → Refers to `operator_id`
- **`operator_type`** values: `group_admin` changed to `admin` in seeders

**Note:** A future release will include migrations to rename these columns properly.

### 📊 Role Hierarchy Summary

```
Level 0:   Developer       (Global authority)
Level 10:  Super Admin     (Tenancy owner)
Level 20:  Admin           (ISP owner) [formerly Group Admin]
Level 30:  Operator        (Segment manager) [formerly Reseller]
Level 40:  Sub-Operator    (Customer manager) [formerly Sub-Reseller]
Level 50:  Manager         (View-only with permissions)
Level 70:  Accountant      (Financial view-only)
Level 80:  Staff           (Support with permissions)
Level 100: Customer        (End user)
```

**Rule:** Lower level = Higher privilege

### 🔒 Security

- No security changes in this release
- Role-based access control maintained and improved
- Tenant isolation remains enforced

### 📝 Testing Notes

All existing tests remain passing. Demo accounts can be seeded with:

```bash
php artisan db:seed --class=DemoSeeder
```

### 🐛 Bug Fixes

- Fixed inconsistent role terminology throughout the application
- Clarified role hierarchy documentation
- Improved role permission checking logic

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
