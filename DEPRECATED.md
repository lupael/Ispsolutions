# Deprecated Documentation

This file lists documentation that has been consolidated, superseded, or is no longer maintained.

**Last Updated**: 2026-01-31  
**Status**: Phases 1, 2, and 3 completed. Code cleanup for deprecated methods completed. New Deprecated attribute available.

---

## 🆕 New in v3.3: Deprecated Attribute

### PHP Attribute for Marking Deprecated Code

A new `#[Deprecated]` attribute is now available for marking deprecated code elements with structured metadata:

**Location**: `App\Attributes\Deprecated`

**Features**:
- **GUID Tracking**: Unique identifier for each deprecated element (e.g., `DEP-2024-001`)
- **Documentation**: Clear message explaining why something is deprecated
- **Version Info**: Track when deprecation occurred (`since`)
- **Migration Path**: Suggest alternatives (`alternative`)
- **Removal Planning**: Indicate when removal is planned (`removeIn`)

**Usage Example**:
```php
use App\Attributes\Deprecated;

#[Deprecated(
    guid: 'DEP-2024-001',
    message: 'Use the new implementation instead',
    since: 'v1.0.0',
    alternative: 'NewClass::newMethod()',
    removeIn: 'v2.0.0'
)]
public function oldMethod(): void
{
    // deprecated implementation
}
```

**Supported Targets**:
- Classes
- Methods
- Properties
- Class Constants

**Benefits over `@deprecated` DocBlock**:
- Structured data that can be programmatically analyzed
- GUID-based tracking across codebase
- Type-safe with PHP 8 attributes
- Consistent deprecation metadata format
- Can be queried via reflection API

---

## ✅ New in v3.2: Deprecated Code Removal

The following deprecated code has been removed in v3.2:

### Removed Deprecated Methods
- ✅ **NotificationService::sendInvoiceGenerated()** - Removed (replaced by sendInvoiceGeneratedNotification)
- ✅ **NotificationService::sendPaymentReceived()** - Removed (replaced by sendPaymentReceivedNotification)
- ✅ **AdminController::mikrotikRouters()** - Removed (replaced by routers() at panel.admin.network.routers)
- ✅ **AdminController::oltDevices()** - Removed (replaced by oltList() at panel.admin.network.olt)

### Maintained for Backward Compatibility
- ⚠️ **User::networkUser()** relationship - Kept for backward compatibility (heavily used throughout codebase)
  - While network credentials are now stored directly on User model, the NetworkUser relationship is still actively used in many controllers, exports, and accessors
  - Cannot be removed without major refactoring

---

## ✅ New in v3.1: Role System Documentation

The role system has been completely updated and consolidated in v3.1:

### Primary Role System Documentation
- ✅ **[ROLE_SYSTEM.md](ROLE_SYSTEM.md)** - **NEW** Complete role system specification v3.1
  - Comprehensive role hierarchy (levels 0-100)
  - Tenancy creation rules
  - Resource and billing responsibilities
  - Demo accounts and seeding guide
  - Implementation details

### Deprecated Role Documentation
The following role-related files are now **deprecated** and replaced by ROLE_SYSTEM.md:

| Old File | Status | Replacement |
|----------|--------|-------------|
| **ROLE_HIERARCHY_CLARIFICATION.md** | ⚠️ Deprecated | ROLE_SYSTEM.md |
| **ROLE_HIERARCHY_IMPLEMENTATION.md** | ⚠️ Deprecated | ROLE_SYSTEM.md |
| **ROLE_SYSTEM_QUICK_REFERENCE.md** | ⚠️ Deprecated | ROLE_SYSTEM.md |
| **SUMMARY.md** | ⚠️ Deprecated | ROLE_SYSTEM.md |
| **DATA_ISOLATION.md** | ⚠️ Keep for now | See also ROLE_SYSTEM.md |
| **docs/PR1_TENANCY_AND_ROLES.md** | ⚠️ Deprecated | ROLE_SYSTEM.md |

**Migration Path**: Use **[ROLE_SYSTEM.md](ROLE_SYSTEM.md)** for all role system documentation.

---

## Consolidated Documentation

The following files have been consolidated into comprehensive guides. They are marked for future removal after a transition period.

### API Documentation → `docs/API.md`

The following files have been merged into the unified **[API Documentation](docs/API.md)**:

| Old File | Status | Notes |
|----------|--------|-------|
| **docs/API_DOCUMENTATION.md** | ⚠️ Deprecated | All content merged into docs/API.md |

**Migration Path**: Use `docs/API.md` as the single source of truth for all API documentation.

---

## Redundant Documentation

### Implementation Tracking Files

Multiple tracking files exist with overlapping content. These are historical and for archive only:

| File | Status | Notes |
|------|--------|-------|
| **TASK_COMPLETION_SUMMARY.md** | ℹ️ Archive Only | Historical task tracking |
| **TASK_COMPLETION_SUMMARY_OLD.md** | ℹ️ Archive Only | Historical task tracking |
| **COMPLETED_TASKS_SUMMARY.md** | ℹ️ Archive Only | Historical completion summary |
| **COMPLETED_DEVELOPMENT_SUMMARY.md** | ℹ️ Archive Only | Historical development summary |
| **IMPLEMENTATION_SUMMARY.md** | ℹ️ Archive Only | Historical implementation notes |
| **IMPLEMENTATION_SUMMARY_PANELS.md** | ℹ️ Archive Only | Historical panel implementation |
| **IMPLEMENTATION_SUMMARY_PAGINATION_ROUTING.md** | ℹ️ Archive Only | Historical pagination implementation |
| **IMPLEMENTATION_COMPLETE_SUMMARY.md** | ℹ️ Archive Only | Historical completion summary |
| **BILLING_IMPLEMENTATION_SUMMARY.md** | ℹ️ Archive Only | Historical billing implementation |
| **ANALYTICS_IMPLEMENTATION_COMPLETE.md** | ℹ️ Archive Only | Historical analytics implementation |
| **DEVELOPMENT_COMPLETION_*.md** | ℹ️ Archive Only | Multiple historical completion files |
| **FEATURE_COMPLETION_REPORT.md** | ℹ️ Archive Only | Historical feature completion |
| **FINAL_IMPLEMENTATION_SUMMARY.md** | ℹ️ Archive Only | Historical final summary |

**Current Status**: Reference **[CHANGELOG.md](CHANGELOG.md)** for version history and **[IMPLEMENTATION_STATUS.md](IMPLEMENTATION_STATUS.md)** for current implementation tracking.

---

## Scheduled for Removal

The following files have been successfully removed or archived:

### Phase 1 (v3.2) - ✅ COMPLETED
- ✅ `ROLE_HIERARCHY_CLARIFICATION.md` - Removed, superseded by ROLE_SYSTEM.md
- ✅ `ROLE_HIERARCHY_IMPLEMENTATION.md` - Removed, superseded by ROLE_SYSTEM.md
- ✅ `ROLE_HIERARCHY_COMPLETION.md` - Removed, archived
- ✅ `ROLE_SYSTEM_QUICK_REFERENCE.md` - Removed from docs/archived/
- ✅ `SUMMARY.md` - Removed from docs/archived/
- ✅ `docs/PR1_TENANCY_AND_ROLES.md` - Removed, historical PR documentation
- ✅ `docs/API_DOCUMENTATION.md` - Removed, content fully merged into docs/API.md

### Phase 2 (v3.2) - ✅ COMPLETED
- ✅ Historical implementation/completion/summary files archived:
  - `COMPLETION_SUMMARY.md` → `docs/archived/COMPLETION_SUMMARY_ROOT.md`
  - `FIX_SUMMARY.md` → `docs/archived/FIX_SUMMARY_ROOT.md`
  - `IMPLEMENTATION_SUMMARY.md` → `docs/archived/IMPLEMENTATION_SUMMARY_ROOT.md`
  - `DEPLOYMENT_GUIDE.md` → `docs/archived/DEPLOYMENT_GUIDE.md`
  - `QUICK_REFERENCE.md` → `docs/archived/QUICK_REFERENCE.md`
  - `PHASE_7_QUICK_REFERENCE.md` → `docs/archived/PHASE_7_QUICK_REFERENCE.md`
  - `QUICK_REFERENCE_PAGINATION_ROUTING.md` → `docs/archived/QUICK_REFERENCE_PAGINATION_ROUTING.md`
  - `NEXT_200_TASKS_COMPLETED.md` → `docs/archived/NEXT_200_TASKS_COMPLETED.md`
  - `NEXT_STEPS.md` → `docs/archived/NEXT_STEPS.md`
  - `DOCUMENTATION_CHANGES.md` → `docs/archived/DOCUMENTATION_CHANGES.md`

---

## Migration Guide

### For Documentation Readers

**Old Reference** → **New Reference**

| Old | New |
|-----|-----|
| ROLE_HIERARCHY_CLARIFICATION.md | ROLE_SYSTEM.md |
| ROLE_HIERARCHY_IMPLEMENTATION.md | ROLE_SYSTEM.md |
| ROLE_SYSTEM_QUICK_REFERENCE.md | ROLE_SYSTEM.md |
| SUMMARY.md | ROLE_SYSTEM.md |
| DATA_ISOLATION.md | ROLE_SYSTEM.md (Data Isolation section) |
| docs/API_DOCUMENTATION.md | docs/API.md |
| docs/PR1_TENANCY_AND_ROLES.md | ROLE_SYSTEM.md |

### For Documentation Links

Update any links to deprecated files:

```markdown
<!-- Old -->
[Role System](ROLE_HIERARCHY_CLARIFICATION.md)
[Quick Reference](ROLE_SYSTEM_QUICK_REFERENCE.md)
[Data Isolation](DATA_ISOLATION.md)

<!-- New -->
[Role System](ROLE_SYSTEM.md)
[Quick Reference](ROLE_SYSTEM.md#role-hierarchy)
[Data Isolation](DATA_ISOLATION.md)
```

### For Code Comments

Update code comments referencing old documentation:

```php
// Old
// See ROLE_HIERARCHY_CLARIFICATION.md for role hierarchy

// New
// See ROLE_SYSTEM.md for role hierarchy
```

### Migrating from @deprecated to #[Deprecated] Attribute

**Old Style** (DocBlock annotation):
```php
/**
 * @deprecated since version 1.0 (2026-01-30). Use is_subscriber flag instead.
 *             Will be removed in version 2.0.
 */
public const OPERATOR_LEVEL_CUSTOMER = 100;
```

**New Style** (PHP 8 Attribute):
```php
use App\Attributes\Deprecated;

#[Deprecated(
    guid: 'DEP-2026-001',
    message: 'Use is_subscriber flag instead',
    since: 'v1.0.0 (2026-01-30)',
    removeIn: 'v2.0.0'
)]
public const OPERATOR_LEVEL_CUSTOMER = 100;
```

**Benefits of Migration**:
- Searchable by GUID for tracking
- Programmatic access via Reflection API
- Type-safe and validated at parse time
- Consistent structure across codebase
- Can generate deprecation reports

**Migration Strategy**:
1. New deprecations should use `#[Deprecated]` attribute
2. Existing `@deprecated` can be migrated gradually
3. Both styles are acceptable during transition period
4. Update `@deprecated` to attribute when touching that code

---

## Removed Files

Files that have been completely removed (not just deprecated):

| File | Removed Date | Reason |
|------|-------------|--------|
| *(None yet)* | - | - |

---

## Files Kept As-Is

The following files are **NOT** deprecated and should continue to be used:

### Core Documentation
- ✅ **README.md** - Main project documentation
- ✅ **CHANGELOG.md** - Version history (updated with v3.1.0)
- ✅ **ROLE_SYSTEM.md** - **NEW v3.1** Role system specification
- ✅ **docs/INDEX.md** - Documentation index
- ✅ **docs/ROLES_AND_PERMISSIONS.md** - Detailed permissions guide
- ✅ **docs/API.md** - API documentation
- ✅ **docs/DEPLOYMENT.md** - Deployment guide
- ✅ **docs/TESTING.md** - Testing guide
- ✅ **docs/USER_GUIDES.md** - User guides
- ✅ **docs/developer-guide.md** - Developer guide

### Feature Documentation
- ✅ **TODO.md** - Current TODO list
- ✅ **TODO_FEATURES_A2Z.md** - Feature specifications
- ✅ **Feature.md** - Feature requests
- ✅ **PANELS_SPECIFICATION.md** - Panel specs
- ✅ **MULTI_TENANCY_ISOLATION.md** - Multi-tenancy overview
- ✅ **IMPLEMENTATION_STATUS.md** - Current implementation status
- ✅ **DATA_ISOLATION.md** - Data isolation rules (keep for now)

### MikroTik Documentation
- ✅ **MIKROTIK_QUICKSTART.md** - Quick start guide
- ✅ **MIKROTIK_ADVANCED_FEATURES.md** - Advanced features

### Network & Service Guides
- ✅ **docs/NETWORK_SERVICES.md** - Network services guide
- ✅ **docs/OLT_SERVICE_GUIDE.md** - OLT guide
- ✅ **docs/OLT_API_REFERENCE.md** - OLT API reference
- ✅ **docs/MONITORING_SYSTEM.md** - Monitoring guide
- ✅ **docs/ROLE_BASED_MENU.md** - Menu system

### Payment & Hotspot Guides
- ✅ **PAYMENT_GATEWAY_GUIDE.md** - Payment gateway integration
- ✅ **HOTSPOT_SELF_SIGNUP_GUIDE.md** - Hotspot self-signup
- ✅ **ANALYTICS_DASHBOARD_GUIDE.md** - Analytics dashboard

---

## Deprecation Policy

### Criteria for Deprecation
1. Content is fully covered in another document
2. Information is outdated or no longer accurate
3. File causes confusion due to redundancy
4. Content has been superseded by better documentation

### Deprecation Process
1. **Mark as Deprecated** - Add to this file with ⚠️ status
2. **Add Redirect** - Add note at top of deprecated file pointing to new location
3. **Transition Period** - Keep file for at least one release cycle
4. **Archive** - Move to archive folder if needed for history
5. **Remove** - Remove file and update all references

### Status Indicators
- ⚠️ **Deprecated** - Scheduled for removal, use new location
- ℹ️ **Archive Only** - Historical reference, not actively maintained
- ✅ **Keep** - Current and actively maintained
- ❌ **Removed** - File has been deleted

---

## Questions?

If you have questions about deprecated documentation:
1. Check **[ROLE_SYSTEM.md](ROLE_SYSTEM.md)** for role system documentation
2. Check the **[Documentation Index](docs/INDEX.md)** for current docs
3. Review the migration guide above
4. Open an issue on GitHub if you need clarification

---

**Note**: Deprecated files will remain in the repository for at least one release cycle (approximately 3 months) to allow for smooth transition.
