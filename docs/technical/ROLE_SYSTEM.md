# Multi-Tenant ISP Role System Specification

**Version:** 3.1  
**Last Updated:** 2026-01-23  
**Status:** ✅ Implemented

This document defines the role hierarchy, tenancy creation rules, resource access, and billing responsibilities for the ISP billing and monitoring platform.

---

## Table of Contents

1. [Tenancy Definition](#tenancy-definition)
2. [Role Hierarchy](#role-hierarchy)
3. [Role Consolidation](#role-consolidation)
4. [Tenancy Creation Rules](#tenancy-creation-rules)
5. [Resource & Billing Responsibilities](#resource--billing-responsibilities)
6. [Implementation Details](#implementation-details)
7. [Demo Accounts](#demo-accounts)

---

## Tenancy Definition

### Key Concepts

- **A Tenancy is represented by a single Super Admin account**
- **Tenancy and Super Admin are effectively the same entity**
- Each tenancy contains multiple ISPs, represented by Admin accounts
- **Admin and Group Admin are the same role** → Use "Admin" consistently everywhere

### Relationship Structure

```
Developer (Global)
    └── Super Admin (Tenancy Owner)
            ├── Admin (ISP 1)
            │   ├── Operator 1
            │   │   └── Sub-Operator 1
            │   └── Operator 2
            └── Admin (ISP 2)
                └── Operator 3
```

---

## Role Hierarchy

### Hierarchy Table

| Level | Role           | Description                                    | Can Create            |
|-------|----------------|------------------------------------------------|-----------------------|
| 0     | Developer      | Global authority – creates/manages Super Admins | Super Admins          |
| 10    | Super Admin    | Tenancy owner – creates/manages Admins         | Admins                |
| 20    | Admin          | ISP owner – manages Operators, Staff, Managers | Operators, Sub-Operators, Managers, Accountants, Staff, Customers |
| 30    | Operator       | Manages Sub-Operators + Customers in segment   | Sub-Operators, Customers |
| 40    | Sub-Operator   | Manages only own customers                     | Customers             |
| 50    | Manager        | View/Edit if explicitly permitted by Admin     | None                  |
| 70    | Accountant     | View-only financial access                     | None                  |
| 80    | Staff          | View/Edit if explicitly permitted by Admin     | None                  |
| 100   | Customer       | End user                                       | None                  |

**Rule:** Lower level = Higher privilege

---

## Role Consolidation

### Removed Roles

The following deprecated roles have been removed from code, migrations, and UI:

| ❌ Deprecated Role | ✅ Replaced By  | Notes                                      |
|--------------------|-----------------|---------------------------------------------|
| Group Admin        | Admin           | Admin is the consistent term               |
| Reseller           | Operator        | Operator (level 30) replaces Reseller      |
| Sub-Reseller       | Sub-Operator    | Sub-Operator (level 40) replaces Sub-Reseller |

### Custom Labels

Super Admin and Admins can rename Operator and Sub-Operator to custom labels (e.g., Partner, Agent, POP) without breaking role logic. This is done via the `role_label_settings` table.

**Examples:**
- Operator → "Partner", "Agent", "POP Manager"
- Sub-Operator → "Sub-Partner", "Sub-Agent", "Local POP"
- Admin → "ISP", "Main POP" (configurable by Super Admin)

---

## Tenancy Creation Rules

### Rule 1: Developer Creates Tenancy

When a Developer creates a new tenancy:
1. A **Super Admin** account is automatically provisioned
2. The Super Admin becomes the owner (`created_by`) of the tenant
3. Creating a Super Admin without a tenancy is **not allowed**

### Rule 2: Super Admin Creates ISP

When a Super Admin creates a new ISP under their tenancy:
1. An **Admin** account is automatically provisioned
2. The Admin represents the ISP owner within that tenancy
3. Each Admin can manage multiple Operators

### Rule 3: Hierarchy Enforcement

- Each **Admin** represents multiple **Operators**
- Each **Operator** represents multiple **Sub-Operators**
- Each **Sub-Operator** manages only their own **Customers**

---

## Resource & Billing Responsibilities

### Developer (Level 0)

#### Resource Access
- ✅ View and edit all Mikrotik, OLTs, Routers, NAS **across all tenancies**
- ✅ Configure/manage Payment Gateway and SMS Gateway **across all tenancies**
- ✅ Search and view all logs and all customers **across multiple tenancies**

#### Billing Responsibilities
- Defines monthly subscription charges for each tenancy
- Defines add-on charges (one-time)
- Defines SMS charges (if tenancy/Super Admin uses Developer-provided SMS gateway)
- Defines custom development charges for tenancy/Super Admin

#### Gateway Setup
- Must set his own SMS and Payment Gateway for collecting charges from Super Admins
- Can configure SMS/Payment Gateway for Super Admins and Admins across all tenancies
- Can setup NAS, Mikrotik, OLT for Admins across all tenancies

---

### Super Admin (Level 10)

#### Resource Access
- ✅ View and edit Mikrotik, OLTs, Routers, NAS **within own tenancy only**
- ✅ Configure/manage Payment Gateway and SMS Gateway **across all Admins (ISPs) within tenancy**
- ✅ Search and view logs and customers **within tenancy**
- ❌ Cannot view or manage resources from other tenancies

#### Billing Responsibilities
- Defines monthly subscription charges for Admins within tenancy
- Defines add-on charges (one-time)
- Defines SMS charges (if Admin uses Super Admin-provided SMS gateway)
- Defines custom development charges for Admins

#### Gateway Setup
- Must set his own SMS and Payment Gateway for collecting charges from Admins
- Alternatively, can use Developer-provided SMS/Payment Gateway

---

### Admin (Level 20)

#### Resource Access
- ✅ View and manage Mikrotik, OLTs, Routers, NAS **within own account**
- ✅ Add/manage:
  - NAS
  - OLT
  - Router
  - PPP profiles
  - Pools
  - Packages
  - Package Prices
- ✅ Configure/manage Payment Gateway and SMS Gateway **within own account**
- ✅ Search and view logs and customers **within own account**
- ❌ Cannot view or create other Admin accounts

#### Delegated Permissions
If Admin grants explicit permission, Staff/Manager can view/edit/manage these resources.

#### Billing Responsibilities
- Must set his own SMS and Payment Gateway for collecting charges from Customers and Operators
- If Operators/Sub-Operators use Admin-provided SMS gateway, Admin can configure cost coverage:
  - Operator pays Admin for SMS usage, OR
  - Admin absorbs SMS cost

#### Gateway Setup & Fund Management
- Operators can add funds to their account by paying Admins via Payment Gateway
- After successful payment, funds are automatically credited to the Operator's account

---

### Operator & Sub-Operator (Levels 30–40)

#### Operator (Level 30)
- Manages Sub-Operators and Customers in their segment
- Can set prices for their Sub-Operators only
- Cannot override or manage pricing set by Admin
- Can add Customers and Sub-Operators

#### Sub-Operator (Level 40)
- Manages only their own Customers
- Cannot create Operators or Admins
- Can only create Customers

---

## Implementation Details

### Database Schema

#### Roles Table
```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(255) UNIQUE,
    slug VARCHAR(255) UNIQUE,
    description TEXT,
    permissions JSON,
    level INT DEFAULT 0,
    timestamps
);
```

#### Users Table (Key Fields)
```sql
- operator_level: INT (0-100, lower = higher privilege)
- operator_type: VARCHAR (developer, super_admin, admin, operator, sub_operator, manager, accountant, staff, customer)
- tenant_id: BIGINT (NULL for Developer/Super Admin)
- created_by: BIGINT (User ID who created this user)
```

#### Role Label Settings Table
```sql
CREATE TABLE role_label_settings (
    id BIGINT UNSIGNED PRIMARY KEY,
    tenant_id BIGINT,
    role_slug VARCHAR(255),
    custom_label VARCHAR(255),
    timestamps
);
```

### Important Code Files

| File Path                                  | Purpose                                      |
|--------------------------------------------|----------------------------------------------|
| `app/Models/User.php`                      | User model with role hierarchy methods       |
| `app/Models/Role.php`                      | Role model with permission handling          |
| `database/seeders/RoleSeeder.php`          | Seeds all system roles                       |
| `database/seeders/DemoSeeder.php`          | Seeds demo accounts for all role levels      |
| `config/operators_permissions.php`         | Permission definitions and level mappings    |
| `config/special_permissions.php`           | Special permissions for operators            |
| `config/sidebars.php`                      | Role-based sidebar menu configurations       |

### Permission Checking

```php
// Check role
if ($user->isDeveloper()) { ... }
if ($user->isSuperAdmin()) { ... }
if ($user->isAdmin()) { ... }
if ($user->isOperatorRole()) { ... }
if ($user->isSubOperator()) { ... }

// Check creation rights
if ($user->canCreateSuperAdmin()) { ... }
if ($user->canCreateAdmin()) { ... }
if ($user->canCreateOperator()) { ... }

// Check management rights
if ($user->canManage($otherUser)) { ... }

// Get accessible customers
$customers = $user->accessibleCustomers()->get();
```

### Backward Compatibility

The following database columns are retained for backward compatibility:

- `reseller_id` in `commissions` table → Refers to `operator_id`

These will be migrated in a future version (v2.0) with proper database migrations.

---

## Demo Accounts

For testing and demonstration purposes, the following demo accounts are available:

### Credentials

All demo accounts use password: **`password`**

| Email                        | Role          | Level | Description                    |
|------------------------------|---------------|-------|--------------------------------|
| developer@ispbills.com       | Developer     | 0     | Global system administrator    |
| superadmin@ispbills.com      | Super Admin   | 10    | Tenancy owner                  |
| admin@ispbills.com           | Admin         | 20    | ISP owner                      |
| operator@ispbills.com        | Operator      | 30    | Operator with sub-operators    |
| suboperator@ispbills.com     | Sub-Operator  | 40    | Manages own customers          |
| customer@ispbills.com        | Customer      | 100   | End user                       |

### Seeding Demo Data

To seed demo accounts:

```bash
php artisan db:seed --class=DemoSeeder
```

This will create:
- Demo tenant (Demo ISP)
- Demo accounts for all role levels
- Demo packages (Basic, Standard, Premium)
- Demo network resources (MikroTik, NAS, OLT, IP pools)

---

## Migration Notes

### From Previous Version

If migrating from an older version that used Group Admin, Reseller, or Sub-Reseller:

1. **Code Updates:** All references have been updated to use Admin, Operator, and Sub-Operator
2. **Database:** New installations use the updated role slugs, but existing databases are **not** migrated automatically. You must manually update any legacy `roles.slug` values and related `role_user` assignments from `group-admin` / `reseller` / `sub-reseller` to the new slugs to match your environment.
3. **UI:** All views and forms have been updated with new terminology
4. **Configuration:** Menu and permission configs have been updated
5. **Backward Compatibility:** Legacy `operator_type` values like `group_admin` are still supported with automatic mapping

### Future Enhancements

Planned for future versions:

- Migrate `reseller_id` column to `operator_id` in commissions table
- Add migration script for legacy data
- Enhanced role-based analytics dashboard
- Multi-language support for role labels

---

## Support & Troubleshooting

### Common Issues

**Q: I can't login with demo accounts**  
A: Make sure you've run `php artisan db:seed --class=DemoSeeder`

**Q: Role permissions not working**  
A: Clear cache with `php artisan cache:clear` and `php artisan config:clear`

**Q: Custom role labels not showing**  
A: Check `role_label_settings` table and ensure tenant_id matches

### Getting Help

For issues or questions:
- Review this documentation
- Check CHANGELOG.md for recent changes
- Open an issue on GitHub with detailed description

---

**Last Updated:** 2026-01-23  
**Version:** 3.1  
**Status:** ✅ Fully Implemented
