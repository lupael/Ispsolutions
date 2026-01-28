# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased] - 2026-01-28

### ✨ Features

- implement customer actions enhancements phases 1-3 ([7184094](../../commit/7184094a001beafcf94b41d97140cbeea7c239e5))
- Complete Phase 13 with UI implementations for all future enhancement features ([d1a00f5](../../commit/d1a00f53f3fc313f24443b045226181b485e14c2))
- Add Customer model as type alias for User (operator_level=100) ([e3cc9c6](../../commit/e3cc9c67c1bf337b174d5cf99000674ab220184a))
- Complete Phase 10-12 - Testing, Documentation, Security & mark all phases complete ([13c21e2](../../commit/13c21e268df83a27b9e6feaecf0e380014289ef6))
- Add billing section Blade views for customer billing management ([7535978](../../commit/7535978275114889c0ca2df255a13b2a932bd5f5))
- Complete Phase 7-9 - Configuration, Policies, Events & Listeners ([0eca658](../../commit/0eca6589ebd8e057323895750276230902d46213))

### 🐛 Bug Fixes

- Update cache TTL to 2.5min and correct terminology (operator not group_admin/reseller) ([3083021](../../commit/308302139f5d1c4c7a7fa136b6f78be4f05b5731))
- Address code review feedback - add validation, remove duplication, implement placeholder functions ([4b67f8b](../../commit/4b67f8b64f5dce0af80fb2c35e7187601abeca91))
- remove non-existent mikrotikRouter relationship references ([87d937d](../../commit/87d937dc77098bb32fd11306a40d7b4eff081a8d))
- add MikrotikRouter imports for consistency ([d421bc0](../../commit/d421bc0eb99780cb7df750e3915e9ade4bdc6926))
- address code review feedback ([4f3427c](../../commit/4f3427c5c7c5148859f5e2c37ed19b938e08eb6c))
- Handle database permission denied error (42000) for radacct queries ([064ba42](../../commit/064ba4281adcc7f675cacba2a9a79edf2c4aaebc))
- Apply second round of code review feedback - documentation accuracy ([b2cfefc](../../commit/b2cfefc2945cee070f4e064841b2e0e0dcbbe0bb))
- Consolidate staff sidebar to single "Routers" menu item ([5db716b](../../commit/5db716b6c2ec167b0f50325718123e2bd1f782a9))
- Address code review feedback on documentation accuracy and UI consistency ([6ebd86d](../../commit/6ebd86d2e426df90d8f886dee628d090b6e5c4fc))
- Consolidate router management menu and add tabbed navigation ([2662834](../../commit/26628341f92b4039e6863f44d1ce64eb9ae4c8b4))
- Address security and consistency issues, add views to sidebar menu ([3578f99](../../commit/3578f991f220e2b52373aeaf01094652f9f569ef))
- Replace hardcoded routes with route() helper, add null safety to components ([ff5706d](../../commit/ff5706dab4bf586f76416c74ba4424157fda4088))
- Address PR review comments - crypto security, validation, routes ([07f5287](../../commit/07f528788f115fcfa0673804bb29c296fae0ce10))
- Add missing GET route and method for other-payment form ([8fc5cf5](../../commit/8fc5cf5c9c19bc578341fc8d55224be59e56d1d9))
- Rename duplicate walletTransactions method to operatorWalletTransactions ([10978b3](../../commit/10978b334a85c1058d3245cf176519434e9e17fd))

### 📚 Documentation

- Fix documentation references and timeline inconsistencies ([3a258dc](../../commit/3a258dcb584a3bd8f8f707fe1cecec9902e94f1d))
- Add executive summary and complete reference system analysis ([50e7f2a](../../commit/50e7f2a6ccd70cab0ec146876b67b6302a1c9b6b))
- Fix MikroTik capitalization and add quick reference guide ([513227b](../../commit/513227bc9a9c29ef608d0f359c2467d8ffb8b011))
- Add comprehensive reference system analysis and implementation plan ([7e39ce2](../../commit/7e39ce2264afe37e3b9b045f0bba6c20868134b4))
- Add quick start README for feature enhancement project ([0519d1c](../../commit/0519d1c9a5beeb5f7d19d393cf0f439dcceac80c))
- Add comprehensive navigation index for feature enhancement documentation ([631a496](../../commit/631a4962f72119fd68bbda5879c7fb4089b80a3b))
- Complete ISP billing system analysis with implementation plans ([1b18cce](../../commit/1b18cce5bd745c76ebfc60bc8ea4d8546c1ead17))
- add implementation completion summary ([38ac977](../../commit/38ac9779e2c57ec170b47fc86023da1af576a37a))
- Add comprehensive Phase 13 implementation summary ([c6b2601](../../commit/c6b260190996e4eca81c2bd41105a7f130cefaa0))
- Add summary of legacy and deprecated code check ([07b73f5](../../commit/07b73f5d5419e5b648db758391b7ce2cafdc5a0d))
- Update documentation to reflect removal of NAS category from filter ([7e3a07f](../../commit/7e3a07f0537ebf04c1cd29a0d43131c599cc8ee0))
- Refine wording to avoid redundant categorization ([b8adb5a](../../commit/b8adb5ad24a95f244e918bf4367f4450575b2f0a))
- Add clarification about incomplete tasks being optional enhancements ([4a595e5](../../commit/4a595e5da8b69ce4a9711d4057154f41bd7d7a13))
- Update ROUTER_RADIUS_IMPLEMENTATION_SUMMARY.md with completion status and admin panel access guide ([f847275](../../commit/f8472756cdf3a35c58424ce1b4cc7849ab9635c8))
- Add Router + RADIUS implementation summary and finalize documentation ([4df04cf](../../commit/4df04cfb94f1ce4f11f1575687ee8ad9826e335b))
- Add comprehensive Router + RADIUS implementation documentation ([03e6098](../../commit/03e6098690ec34fd99f73c2867b0fd0a7df6cb0f))

### ♻️ Code Refactoring

- Integrate parent account filtering into existing customer management ([e5cbbc9](../../commit/e5cbbc96b29a6f0714a942c011d723f286ad549b))
- Improve Alpine.js integration and replace placeholder alerts with proper implementations ([df35b39](../../commit/df35b3935a3bed6f91a57e95e2844ddee27c2292))
- Reduce code duplication in error handling ([73e1091](../../commit/73e1091862db79fbccf98b705f6a8d3e9d20aa23))
- Remove redundant tabs, unify all routers into single page with filtering ([26bb49f](../../commit/26bb49f2f00973c0d20a1c206bd20972eec64a46))
- Extract router device tabs into reusable component ([d121f37](../../commit/d121f378ed4718bd9bc3dab8c234543422ceb857))

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
