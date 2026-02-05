# Gemini Context & Project Instructions (ISPSolution)

This file provides the essential context for the **ISPSolution** system and tracks live progress to handle session timeouts, quota limits, or connectivity issues.

## 🏗 Core Architecture
- **Framework:** Laravel (PHP 8.2+)
- **Multi-Tenancy:** Single database with `tenant_id` scoping for ISPs.
- **Role Hierarchy:** 9 levels (Developer, Super Admin, Admin, Operator, Sub-Operator, Manager, Staff, Accountant, Customer).
- **Core Entities:** `Customer`, `Invoice`, `Payment`, `ServicePackage`, `RechargeCard`, `Commission`.
- **Logic Layer:** Heavy reliance on **Services** (`app/Services/`).

## 🛠 Tech Stack Details
- **Networking:** RouterOS API (MikroTik), FreeRADIUS (AAA), and OLT integration.
- **Billing:** Automated invoices, recharge cards, and multi-level commissions.
- **Frontend:** Laravel Blade with role-based navigation.

## 🎯 AI Instructions & Context
1. **Service-First:** No business logic in Controllers. Use `app/Services/`.
2. **Tenant Scoping:** All queries must include `tenant_id` scoping.
3. **Single Source of Truth:** The `Customer` model drives hardware (RADIUS/MikroTik) status.
4. **Naming:** Use **Admin** (ISP Owner), **Operator** (Reseller), **Customer** (Subscriber).

---

## 🔄 Live Progress Tracker (Current Session)

### 🚀 Current Objective
- [ ] *Describe the main feature you are building right now*

### 📝 Task List
- [ ] Sub-task 1
- [ ] Sub-task 2

### 📍 Active State (Resume Point)
- **Last Modified File:** - **Current Logic Block:** - **Next Step:** ### 📈 Completed Summary
- *No tasks completed in the current session yet.*

---

## 📂 Key Directories
- `app/Services/`: Billing, Radius, Mikrotik logic.
- `app/Http/Controllers/`: Panel-specific controllers.
- `app/Models/`: Core data structures.

## 🧪 Testing Focus
- Feature tests: `Billing`, `Commission`, `Cards`.
- Unit tests: `MikrotikService`, `OltService`.
