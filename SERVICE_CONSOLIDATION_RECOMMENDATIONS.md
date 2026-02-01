# Service Consolidation Recommendations

Generated: 2026-01-31

## Overview

This document provides detailed recommendations for consolidating duplicate and overlapping services identified in the duplicate code audit. These recommendations are prioritized by impact and complexity.

## High Priority Consolidations

### 1. PDF Generation Services

**Current State:**
- `PdfService.php` - Generic invoice/bill/receipt generation
- `PdfExportService.php` - Enhanced version with more formatting options

**Issue:**
Both services have `generateInvoicePdf()` methods, causing confusion about which to use.

**Recommendation:**
Consolidate into a single `DocumentExportService` using the Strategy Pattern:

```php
// New unified service
class DocumentExportService
{
    public function __construct(
        private readonly PdfStrategy $pdfStrategy,
        private readonly ExcelStrategy $excelStrategy
    ) {}

    public function generateInvoice(Invoice $invoice, string $format = 'pdf')
    {
        return match($format) {
            'pdf' => $this->pdfStrategy->generate($invoice),
            'excel' => $this->excelStrategy->generate($invoice),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}")
        };
    }
}
```

**Benefits:**
- Single entry point for document generation
- Easy to add new formats (HTML, CSV, etc.)
- Consistent interface across all document types
- Better testability

**Migration Steps:**
1. Create `DocumentExportService` with format strategies
2. Update all controllers to use new service
3. Deprecate old services with notices
4. Remove old services after deprecation period

---

### 2. MikroTik API Services

**Current State:**
- `MikrotikService.php` - Main interface (35 methods)
- `MikrotikApiService.php` - Unified API with auto-detection (15 methods)
- `MikrotikImportService.php` - Import operations using both
- `RouterosAPI.php` - Legacy wrapper (IspBills pattern)

**Issue:**
Unnecessary layering and complexity. Multiple entry points for the same operations.

**Recommendation:**

**Phase 1:** Remove `RouterosAPI.php`
- This is a legacy wrapper that should be completely replaced by modern services
- Already deprecated in favor of `MikrotikApiService`

**Phase 2:** Simplify to two services:
- `MikrotikApiService` - Low-level API operations (connection, commands)
- `MikrotikOperationsService` - High-level business operations (user management, queues, etc.)

**Benefits:**
- Clear separation between API layer and business logic
- Easier to maintain and test
- Consistent API across the application
- Better error handling

**Migration Steps:**
1. Audit all usages of `RouterosAPI`
2. Replace with `MikrotikApiService`
3. Test thoroughly with real routers
4. Remove `RouterosAPI.php`
5. Refactor `MikrotikService` methods into `MikrotikOperationsService`

---

### 3. VPN Management Services

**Current State:**
- `VpnService.php` - Basic VPN account creation (9 methods)
- `VpnProvisioningService.php` - Advanced provisioning (14 methods)
- `VpnManagementService.php` - Dashboard stats (8 methods)

**Issue:**
Both `VpnService` and `VpnProvisioningService` have `createVpnAccount()` methods.

**Recommendation:**
Consolidate into two focused services:

```php
// VpnAccountService - Account CRUD operations
class VpnAccountService
{
    public function create(array $data): VpnAccount
    public function update(VpnAccount $account, array $data): VpnAccount
    public function delete(VpnAccount $account): bool
    public function list(array $filters = []): Collection
}

// VpnProvisioningService - Router provisioning operations
class VpnProvisioningService
{
    public function provision(VpnAccount $account, MikrotikRouter $router): bool
    public function deprovision(VpnAccount $account, MikrotikRouter $router): bool
    public function syncToRouter(MikrotikRouter $router): array
}
```

**Benefits:**
- Clear separation of concerns
- Easier to test independently
- Reusable components
- Better error handling per concern

---

### 4. Cache Services

**Current State:**
- `CacheService.php` - Generic tenant cache (23 methods)
- `CustomerCacheService.php` - Customer-specific (7 methods)
- `BillingProfileCacheService.php` - Billing profile cache (5 methods)
- `WidgetCacheService.php` - Widget cache (8 methods)

**Issue:**
Multiple cache services doing similar operations with different keys.

**Recommendation:**
Consolidate into single `CacheService` with namespaced operations:

```php
class CacheService
{
    private const NAMESPACE_CUSTOMER = 'customer';
    private const NAMESPACE_BILLING = 'billing';
    private const NAMESPACE_WIDGET = 'widget';

    public function customer(): CustomerCacheRepository
    public function billing(): BillingCacheRepository
    public function widget(): WidgetCacheRepository
    
    // Generic cache operations
    public function remember(string $namespace, string $key, $ttl, callable $callback)
    public function forget(string $namespace, string $key): bool
    public function flush(string $namespace): bool
}
```

**Benefits:**
- Single cache interface
- Consistent cache key naming
- Centralized cache invalidation
- Better monitoring and debugging

---

### 5. Billing Services

**Current State:**
- `BillingService.php` - Core invoice generation
- `SubscriptionBillingService.php` - Subscription bills
- `CableTvBillingService.php` - CableTV billing
- `StaticIpBillingService.php` - Static IP billing

**Issue:**
Highly duplicated logic across specialized billing services.

**Recommendation:**
Use Strategy Pattern with pluggable billing type handlers:

```php
// Core billing service
class BillingService
{
    public function __construct(
        private readonly BillingStrategyFactory $strategyFactory
    ) {}

    public function generateInvoice(Billable $entity): Invoice
    {
        $strategy = $this->strategyFactory->forEntity($entity);
        return $strategy->generateInvoice($entity);
    }
}

// Strategy factory
class BillingStrategyFactory
{
    public function forEntity(Billable $entity): BillingStrategyInterface
    {
        return match($entity->getBillingType()) {
            'subscription' => new SubscriptionBillingStrategy(),
            'cable_tv' => new CableTvBillingStrategy(),
            'static_ip' => new StaticIpBillingStrategy(),
            default => new DefaultBillingStrategy()
        };
    }
}
```

**Benefits:**
- DRY principle - shared logic in base class
- Easy to add new billing types
- Consistent interface
- Better testability

---

### 6. RADIUS Services

**Current State:**
- `RadiusService.php` - User/account management (10 methods)
- `RadiusSyncService.php` - Sync wrapper (13 methods, mostly delegates)
- `RouterRadiusProvisioningService.php` - Router-specific provisioning

**Issue:**
`RadiusSyncService` is a thin wrapper that delegates to `RadiusService`.

**Recommendation:**
Remove `RadiusSyncService` and merge its unique functionality into `RadiusService`:

```php
class RadiusService
{
    // Keep all existing methods
    
    // Add sync-specific methods directly
    public function syncUserToRadius(User $user): bool
    public function syncBulkUsers(Collection $users): array
    public function syncRouterUsers(MikrotikRouter $router): array
}
```

**Benefits:**
- Eliminates unnecessary indirection
- Simpler call chains
- Easier to understand flow
- Better performance (fewer method calls)

---

## Medium Priority Consolidations

### 7. Router Management Services

**Current State:**
- `RouterManager.php` - Facade/dispatcher
- `RouterProvisioningService.php` - Zero-touch provisioning
- `RouterConfigurationService.php` - Configuration management

**Recommendation:**
Keep services but clarify responsibilities:
- `RouterManager` - High-level orchestration only
- `RouterProvisioningService` - Initial setup and templates
- `RouterConfigurationService` - Ongoing configuration changes

Add clear documentation about when to use each service.

---

### 8. Health Monitoring Services

**Current State:**
- `RouterHealthCheckService.php` - Router health monitoring
- `MonitoringService.php` - General monitoring

**Recommendation:**
Merge into single `MonitoringService` with different monitoring strategies:

```php
class MonitoringService
{
    public function checkRouter(MikrotikRouter $router): HealthStatus
    public function checkRadius(Nas $nas): HealthStatus
    public function checkOlt(Olt $olt): HealthStatus
    public function checkAll(): array
}
```

---

## Implementation Timeline

### Phase 1 (Sprint 1): High Priority
1. PDF Services consolidation
2. Remove RouterosAPI legacy wrapper
3. Fix RADIUS services delegation

**Estimated Effort:** 3-5 days

### Phase 2 (Sprint 2): Medium Priority
1. MikroTik services simplification
2. VPN services consolidation
3. Cache services unification

**Estimated Effort:** 5-7 days

### Phase 3 (Sprint 3): Complex Refactoring
1. Billing services strategy pattern
2. Router management clarification
3. Monitoring services merge

**Estimated Effort:** 7-10 days

## Testing Strategy

For each consolidation:
1. Write comprehensive unit tests for new services
2. Run integration tests with real routers/systems
3. Perform load testing for critical paths
4. Deploy to staging environment first
5. Monitor for issues before production rollout

## Risk Mitigation

1. **Backward Compatibility:** Keep old services deprecated but functional during transition
2. **Feature Flags:** Use feature flags to toggle between old and new implementations
3. **Rollback Plan:** Document steps to revert if issues arise
4. **Monitoring:** Add detailed logging during transition period
5. **Gradual Migration:** Migrate one module at a time, not all at once

## Success Metrics

- [ ] Reduced number of service classes by 30%
- [ ] Improved test coverage to >80%
- [ ] Reduced cyclomatic complexity
- [ ] Faster response times (fewer method calls)
- [ ] Fewer bugs related to service confusion
- [ ] Better developer onboarding experience

## Notes

- All consolidations should maintain or improve existing functionality
- No breaking changes to public APIs without major version bump
- Document all changes in CHANGELOG.md
- Update developer documentation
- Provide migration guides for custom integrations
