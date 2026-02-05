# Instruction Guide

## 📐 Architecture Rules
- Use **Laravel 10+ with PHP 8.2**.
- Multi-tenancy: always scope queries by `tenant_id`.
- Business logic must live in `app/Services/`.
- Controllers handle only request/response, never core logic.

## 👥 Role Hierarchy
1. Developer  
2. Super Admin  
3. Admin (ISP Owner)  
4. Operator (Reseller)  
5. Sub-Operator  
6. Manager  
7. Staff  
8. Accountant  
9. Customer (Subscriber)

## 🔧 Core Entities
- `Customer` → single source of truth for RADIUS/MikroTik status.
- `Invoice` → automated billing.
- `Payment` → gateway + manual.
- `ServicePackage` → bandwidth, quota, validity.
- `RechargeCard` → prepaid top-ups.
- `Commission` → reseller/agent earnings.

## 📜 Naming Conventions
- **Admin** = ISP Owner  
- **Operator** = Reseller  
- **Customer** = Subscriber  
- Always use consistent terminology across UI, docs, and code.

---

## 🔄 Refactor Guidelines
- **REST API Cleanup:**
  - Remove deprecated endpoints.
  - Consolidate logic into `app/Services/`.
- **Role Hierarchy Standardization:**
  - Map legacy roles to the new 9-level hierarchy.
  - Purge unused roles from DB, UI, and docs.
- **MikroTik/NAS Integration:**
  - Merge duplicated connection logic.
  - Use unified `MikrotikService`.
- **Gateway Logic:**
  - Standardize payment gateway handling.
  - Replace inline logic with reusable service methods.
- **Documentation:**
  - Update Markdown checklists after each refactor.
  - Ensure ERD diagrams reflect new structure.

---

## 🗑️ Deprecation Rules
- **Deprecate `network_users`:**
  - Replace all references with `Customer`.
  - Drop `network_user_id` in favor of `customer_id`.
  - Ensure `Customer` is flagged as `is_subscriber` instead of role-based.
- **Legacy REST API:**
  - Remove all references to deprecated endpoints.
- **Obsolete Roles:**
  - Delete legacy roles not in the new hierarchy.
- **UI Components:**
  - Remove unused Blade templates tied to deprecated roles.
- **Database Fields:**
  - Drop columns no longer used (e.g., `legacy_status`, `old_role_id`).
- **Docs:**
  - Mark deprecated features in changelog.
  - Provide migration notes for developers.

---

## 🎨 Panel View Management
- **Adding Views:**
  - Create new Blade templates under `resources/views/panel/`.
  - Ensure role-based access control (RBAC) is applied.
  - Register routes in `web.php` with middleware for tenant and role checks.
  - Update navigation menus dynamically based on role hierarchy.
- **Removing Views:**
  - Identify unused or deprecated Blade templates.
  - Remove associated routes and controller methods.
  - Clean up navigation links and sidebar entries.
  - Document removal in changelog for traceability.

---

## 🔍 Duplicate Check Rules
- **Customer Records:**
  - Prevent duplicate entries by validating `email`, `phone`, and `username` before creation.
  - Enforce unique constraints at DB level (`unique index`).
- **Invoices & Payments:**
  - Ensure invoice numbers are unique per tenant.
  - Prevent duplicate payment entries by checking transaction ID.
- **Network Sessions:**
  - Disallow multiple active sessions for the same `customer_id` unless explicitly allowed.
  - Validate against duplicate PPPoE/DHCP sessions.
- **Recharge Cards:**
  - Enforce unique card codes.
  - Prevent re-use of already redeemed cards.
- **Panel Views:**
  - Avoid duplicate menu entries when adding/removing views.
  - Validate role-based visibility before rendering.

---

## ✅ Implementation Checklist
- [ ] Refactor legacy REST API endpoints.
- [ ] Merge Mikrotik/NAS infrastructure.
- [ ] Add tenant scoping to all queries.
- [ ] Write unit tests for `MikrotikService` and `OltService`.
- [ ] Update invoice generation logic.
- [ ] Remove deprecated roles and DB fields.
- [ ] Purge unused Blade templates.
- [ ] Replace `network_users` with `Customer`.
- [ ] Add/remove panel views with RBAC enforcement.
- [ ] Implement duplicate checks for customers, invoices, sessions, and recharge cards.

---

## 🚧 Current Objective
- Refactor legacy REST API and unify role hierarchy.

## 🟢 Active State
- **Deprecation in Progress:** `network_users` → replaced by `Customer`.
- Working on `CustomerService.php`.
- Next step: enforce tenant scoping in `getActiveCustomers()`.
- **Upcoming Task:** Integrate **Mikrotik, RADIUS, and OLT/ONU services** into unified `NetworkIntegrationService`.
- **UI Task:** Add/remove panel views with proper role-based restrictions.
- **Data Integrity Task:** Implement duplicate checks across core entities.

---

## 📘 Completed Summary
- Deprecated roles removed.
- Global terminology mapping finalized.
- Invoice generation logic updated.

## 🧪 Testing Focus
- **Feature Tests:** Billing, Commission, Recharge Cards, Panel Views, Duplicate Prevention.
- **Unit Tests:** `MikrotikService`, `OltService`, `CustomerService`.
