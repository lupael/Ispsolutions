# Sidebar Route Reference Fixes

**Issue:** Sidebar menu items reference route names that don't match the actual registered routes.
**Impact:** Menu items don't navigate to the correct pages, making features appear "missing" from UI.
**Status:** Identified - Requires systematic fixes

---

## Root Cause Analysis

The sidebar menu in `resources/views/panels/partials/sidebar.blade.php` references routes using one naming convention, but the routes in `routes/web.php` are registered with different names inside the `panel.admin.` prefix group.

### Pattern Issues

1. **OLT Routes**
   - Sidebar: `panel.admin.olt` 
   - Actual: `panel.admin.network.olt`
   - Issue: Missing `network.` in path

2. **MikroTik Routes**
   - Sidebar: `panel.admin.mikrotik`
   - Actual: `panel.admin.network.routers` (with redirect from `panel.admin.mikrotik`)
   - Issue: Redirect exists but sidebar should point directly

3. **IP Pool Routes**
   - Sidebar: Multiple references need verification
   - Actual: Routes exist under `network.ipv4-pools`, `network.ipv6-pools`

4. **SMS Gateway Routes**
   - Need to verify if sidebar references match actual routes

---

## Detailed Findings

### Admin Panel Routes (Line 42-161 in sidebar.blade.php)

#### Network Devices Section (Lines 63-75)
```php
[
    'label' => 'Network Devices',
    'icon' => 'server',
    'children' => [
        ['label' => 'MikroTik Routers', 'route' => 'panel.admin.mikrotik'], // ❌ Should be network.routers
        ['label' => 'Router Provisioning', 'route' => 'panel.admin.routers.provision.index'], // ✅ Correct
        ['label' => 'NAS Devices', 'route' => 'panel.admin.nas'], // ❌ Should be network.nas
        ['label' => 'Cisco Devices', 'route' => 'panel.admin.cisco'], // ❌ Should be check
        ['label' => 'All Devices', 'route' => 'panel.admin.network.devices'], // ✅ Correct
        ['label' => 'Device Monitors', 'route' => 'panel.admin.network.device-monitors'], // ✅ Correct
        ['label' => 'Devices Map', 'route' => 'panel.admin.network.devices.map'], // ✅ Correct
    ]
]
```

#### Network Section (Lines 76-86)
```php
[
    'label' => 'Network',
    'icon' => 'network',
    'children' => [
        ['label' => 'IPv4 Pools', 'route' => 'panel.admin.network.ipv4-pools'], // ✅ Correct
        ['label' => 'IPv6 Pools', 'route' => 'panel.admin.network.ipv6-pools'], // ✅ Correct
        ['label' => 'IP Pool Migration', 'route' => 'panel.admin.ip-pools.migrate'], // ✅ Correct
        ['label' => 'PPPoE Profiles', 'route' => 'panel.admin.network.pppoe-profiles'], // ✅ Correct
        ['label' => 'Ping Test', 'route' => 'panel.admin.network.ping-test'], // ✅ Correct
    ]
]
```

#### OLT Management Section (Lines 87-98)
```php
[
    'label' => 'OLT Management',
    'icon' => 'lightning',
    'children' => [
        ['label' => 'OLT Devices', 'route' => 'panel.admin.olt'], // ❌ Should be network.olt
        ['label' => 'OLT Dashboard', 'route' => 'panel.admin.olt.dashboard'], // ✅ Correct
        ['label' => 'Templates', 'route' => 'panel.admin.olt.templates'], // ✅ Correct
        ['label' => 'SNMP Traps', 'route' => 'panel.admin.olt.snmp-traps'], // ✅ Correct
        ['label' => 'Firmware', 'route' => 'panel.admin.olt.firmware'], // ✅ Correct
        ['label' => 'Backups', 'route' => 'panel.admin.olt.backups'], // ✅ Correct
    ]
]
```

---

## Verification of Actual Routes

From `routes/web.php` analysis:

### MikroTik/Router Routes
```php
// Line 272-274: Legacy redirect
Route::get('/mikrotik', function () {
    return redirect()->route('panel.admin.network.routers');
})->name('mikrotik');

// Line 395-402: Actual router routes
Route::get('/network/routers', [AdminController::class, 'routers'])->name('network.routers');
Route::get('/network/routers/create', [AdminController::class, 'routersCreate'])->name('network.routers.create');
// ... etc
```

### OLT Routes
```php
// Line 277-279: Legacy redirect
Route::get('/olt', function () {
    return redirect()->route('panel.admin.network.olt');
})->name('olt');

// Line 434-441: Actual OLT routes
Route::get('/network/olt', [AdminController::class, 'oltList'])->name('network.olt');
Route::get('/network/olt/create', [AdminController::class, 'oltCreate'])->name('network.olt.create');
// ... etc
```

### NAS Routes
```php
// Line 275: Direct route (no redirect)
Route::get('/nas', [AdminController::class, 'nasDevices'])->name('nas');

// Line 424-431: Network NAS routes
Route::get('/network/nas', [AdminController::class, 'nasList'])->name('network.nas');
// ... etc
```

---

## Required Fixes

### Priority 1: Navigation-Breaking Issues

1. **MikroTik Routes** (Line 67 in sidebar)
   - Change: `'route' => 'panel.admin.mikrotik'`
   - To: `'route' => 'panel.admin.network.routers'`

2. **OLT Devices Route** (Line 91 in sidebar)
   - Change: `'route' => 'panel.admin.olt'`
   - To: `'route' => 'panel.admin.network.olt'`

3. **NAS Devices Route** (Line 69 in sidebar)
   - Change: `'route' => 'panel.admin.nas'`
   - To: `'route' => 'panel.admin.network.nas'`

4. **Cisco Devices Route** (Line 70 in sidebar)
   - Verify actual route exists
   - Likely should be: `'route' => 'panel.admin.network.cisco'`

### Priority 2: Other Potential Issues

Need to verify these sections:
- Customers section (Lines 48-62)
- Accounting section (Lines 100-115)
- Operators section (Lines 117-125)
- SMS Management section (Lines 127-137)
- Analytics section (Lines 150-158)
- Payment Gateways (Line 159)

---

## Testing Plan

1. Apply route name fixes to sidebar
2. Test each menu item in Admin panel
3. Verify redirects work for legacy routes
4. Test operator and sub-operator menus
5. Verify customer panel routes
6. Document any additional mismatches

---

## Additional Issues to Check

Based on @lupael's report, also verify:
- [ ] SMS gateway menu visibility in Admin panel
- [ ] Today's update widget on dashboard
- [ ] Prepaid card generation, download, and mapping views
- [ ] Device status charts and statistics
- [ ] Real-time bandwidth usage button for customers
- [ ] Operator fund management visibility
- [ ] Operator/sub-operator customer management access
- [ ] Package mapping with PPP profile and IP pool UI visibility
- [ ] Apply configuration to MikroTik router functionality

---

## Next Steps

1. Fix sidebar route references (Priority 1 items)
2. Test navigation in browser
3. Fix any additional route mismatches discovered
4. Update documentation
5. Create comprehensive test for all menu items

---

**Document Created:** January 24, 2026
**Status:** Analysis Complete - Ready for Implementation
