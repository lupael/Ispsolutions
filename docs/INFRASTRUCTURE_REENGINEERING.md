# Infrastructure Re-Engineering: ISP Management System

NAS-centric, fail-safe architecture for MikroTik and OLT integration (aligned with Splynx, phpNuxBill, and i4edubd/ispsolution#165).

## 1. MikroTik: NAS-Centric Integration (v6 & v7)

- **API:** `evilfreelancer/routeros-api-php` for RouterOS v6 and v7 (binary API with optional REST).
- **Customization:**
  - Custom API port (validated 1–65535).
  - Minimum 4-character router password (RouterOS requirement; enforced in `StoreMikrotikRouterRequest` / `UpdateMikrotikRouterRequest`).
  - RADIUS ports from config: standard 1812/1813 or custom (e.g. 3612/3613 for FreeRADIUS/daloradius) via `RADIUS_AUTH_PORT` and `RADIUS_ACCT_PORT`.
  - NAS table supports arbitrary SNMP community via `community` field.
- **Automated provisioning (first connect):**
  - RADIUS client: `/radius add service=ppp,hotspot address=[Server_IP]` (ports from config).
  - PPP AAA: `/ppp aaa set use-radius=yes`, accounting, interim-update.
  - RADIUS incoming: `/radius incoming set accept=yes`.
  - Netwatch: RADIUS health monitor with up/down scripts (see §4).
  - Backup: `/system backup save` and PPP secret export (`ppp-secret-backup-by-billing-{timestamp}`).
  - NAS table: Router is associated with NAS (`nas_id`); RADIUS `nas` table is populated by your NAS/radsec logic.

## 2. OLT: Full Lifecycle

- **Multi-vendor import:** OID walks for VSOL, Huawei, ZTE, BDCOM in `OltSnmpService` (discover ONUs, status, RX/TX power).
- **ONU monitoring:** Real-time RX/TX and status via SNMP; OLT details page shows searchable ONU list with signal indicators.
- **Auto-backup:** Vendor-specific backup commands in `OltService::createBackup()` (TFTP/FTP as per vendor).

## 3. UI: Drill-Down Details

- **Routers:** Router name on Network → Routers links to `/admin/mikrotik/{id}/details` (resource monitor, active PPPoE sessions, quick links).
- **OLT:** OLT name on Network → OLT links to `/admin/olt/{id}/details` (signal summary, searchable ONU table with RX/TX).

## 4. Netwatch Fallback (RADIUS Health)

- **Controller:** `NasNetwatchController`; provisioning step in `RouterRadiusProvisioningService::configureNetwatchForRadius()`.
- **Behavior:**
  - RADIUS UP: Disable local secrets; drop non-radius sessions (`up-script`).
  - RADIUS DOWN: Enable local secrets (`down-script`).
- **Config:** `config('radius.netwatch.interval', '1m')`, `config('radius.netwatch.timeout', '1s')`; env: `RADIUS_NETWATCH_INTERVAL`, `RADIUS_NETWATCH_TIMEOUT`.

## 5. Backups

- **Router PPP export:** During import, `RouterRadiusProvisioningService::exportPppSecrets()` exports to `ppp-secret-backup-by-billing-{timestamp}`.
- **Customer backup/mirror:** `CustomerBackupController` runs when primary is not RADIUS; syncs PPP secrets to router (add or edit by name).
- **App/Server:** `config/backup.php` (Spatie placeholder); router/OLT backups are separate.

## 6. End-to-End: Configure RADIUS + Fallback

Provisioning order in `RouterRadiusProvisioningService::provisionOnFirstConnect()`:

1. Add/update RADIUS client (address, secret, service=hotspot,ppp, auth/acct ports from config).
2. `/ppp/aaa/set` (use-radius=yes, accounting=yes, interim-update).
3. `/radius/incoming/set` (accept=yes).
4. Netwatch: remove existing entry for RADIUS host, add entry with up-script and down-script.
5. `/system/backup/save` and PPP secret export.
6. Ensure router is linked to NAS (`nas_id`).

## 7. AdminController Alias

Routes in `web.php` reference `AdminController`; `App\Http\Controllers\Panel\AdminController` extends `ISPController` so all admin panel routes resolve without changing route definitions.
