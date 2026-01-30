# CSP (Content Security Policy) Fix Summary

## Problem Statement
The admin dashboard was experiencing CSP violations that prevented charts from loading. The browser console showed errors like:

```
dashboard:935 Executing inline script violates the following Content Security Policy directive 'script-src 'self' 'unsafe-eval' 'unsafe-hashes' 'nonce-eDKts8u/vDm6nv4LZvFfXQ==' cdn.jsdelivr.net cdnjs.cloudflare.com cdn.tailwindcss.com static.cloudflareinsights.com'. Either the 'unsafe-inline' keyword, a hash ('sha256-...'), or a nonce ('nonce-...') is required to enable inline execution.
```

## Root Cause
Inline `<script>` tags in Blade templates were missing the required `nonce` attribute. The SecurityHeaders middleware generates a unique nonce for each request and includes it in the CSP header, but the inline scripts weren't using this nonce.

## Solution Implemented
Added `nonce="{{ $cspNonce ?? '' }}"` attribute to all inline `<script>` tags across the entire project (20 files total).

## Files Modified

### Chart Components (Dashboard-Critical)
1. `resources/views/components/revenue-trend-chart.blade.php`
2. `resources/views/components/customer-growth-chart.blade.php`
3. `resources/views/components/service-type-distribution-chart.blade.php`
4. `resources/views/components/revenue-mrc-widget.blade.php`

### Other Components
5. `resources/views/components/package-upgrade-wizard.blade.php`
6. `resources/views/components/customer-address-display.blade.php`

### Admin Panel Views
7. `resources/views/panels/admin/customers/pppoe-import.blade.php`
8. `resources/views/panels/admin/customers/show.blade.php`
9. `resources/views/panels/admin/billing-profiles/index.blade.php`

### Customer Panel
10. `resources/views/panels/customer/auto-debit/index.blade.php`

### Operator Panel
11. `resources/views/panels/operator/sms-payments/index.blade.php`
12. `resources/views/panels/operator/sms-payments/create.blade.php`
13. `resources/views/panels/operator/subscriptions/index.blade.php`
14. `resources/views/panels/operator/subscriptions/bills.blade.php`
15. `resources/views/panels/operator/subscriptions/show.blade.php`

### Payment Methods
16. `resources/views/panels/payment-methods/index.blade.php`
17. `resources/views/panels/payment-methods/create.blade.php`

### Developer Panel
18. `resources/views/panels/developer/audit-logs.blade.php`

### Shared Components
19. `resources/views/panels/shared/widgets/sms-balance.blade.php`
20. `resources/views/panel/sms/broadcast/create.blade.php`

## Technical Details

### How CSP Nonce Works
1. The `SecurityHeaders` middleware (`app/Http/Middleware/SecurityHeaders.php`) generates a cryptographically secure random nonce for each request:
   ```php
   $nonce = base64_encode(random_bytes(16));
   ```

2. The nonce is shared with all views:
   ```php
   view()->share('cspNonce', $nonce);
   ```

3. The nonce is included in the CSP header:
   ```php
   "script-src 'self' 'unsafe-eval' 'unsafe-hashes' 'nonce-{$nonce}' ..."
   ```

4. Inline scripts must include this nonce to execute:
   ```html
   <script nonce="{{ $cspNonce ?? '' }}">
       // Script code here
   </script>
   ```

### CSP Directives in Use
- `'self'` - Allow scripts from same origin
- `'unsafe-eval'` - Required for Alpine.js (evaluates expressions in attributes)
- `'unsafe-hashes'` - Allows inline event handlers (onclick, onerror, etc.)
- `'nonce-{$nonce}'` - Allows inline scripts with matching nonce
- External CDNs: cdn.jsdelivr.net, cdnjs.cloudflare.com, cdn.tailwindcss.com

### Why Not 'unsafe-inline'?
Using `'unsafe-inline'` would allow ALL inline scripts, including potentially malicious ones injected via XSS attacks. Using nonces provides strong security while still allowing our trusted inline scripts.

## Dashboard Charts

### ApexCharts Loading
- ApexCharts is imported in `resources/js/app.js` (line 4)
- Made globally available via `window.ApexCharts = ApexCharts` (line 104)
- Chart components check for ApexCharts availability before rendering

### Widget Links
- The `info-box` component supports clickable widgets via the `link` parameter
- Dashboard correctly uses `route('panel.admin.customers.online')` for the "Online Now" widget
- Route exists in `routes/web.php` within the `panel.admin` route group

## Verification
All inline scripts across the project now have the nonce attribute. Run this command to verify:

```bash
# This should return 0 files without nonce
find resources/views -name "*.blade.php" -exec grep -l "<script>" {} \; | \
  while read file; do 
    if ! grep -q 'nonce="{{' "$file" 2>/dev/null; then 
      echo "$file"; 
    fi; 
  done
```

## Expected Results
After these changes:
1. ✅ All dashboard charts should load correctly
2. ✅ No CSP violations in browser console
3. ✅ Widget links navigate to correct pages
4. ✅ All inline JavaScript functionality works as expected
5. ✅ Strong XSS protection maintained via CSP

## Testing
To test the fix:
1. Clear browser cache
2. Navigate to admin dashboard
3. Open browser DevTools console
4. Verify no CSP errors appear
5. Confirm charts render correctly
6. Click on widget links to verify navigation works

## Security Benefits
- Prevents XSS attacks by blocking unauthorized inline scripts
- Uses cryptographically secure nonces (regenerated on each request)
- Maintains strict CSP policy while allowing necessary functionality
- Inline event handlers work via 'unsafe-hashes' directive
