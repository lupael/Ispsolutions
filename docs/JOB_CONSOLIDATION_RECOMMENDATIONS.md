# Job Consolidation Recommendations

Generated: 2026-01-31

## Overview

This document provides detailed recommendations for consolidating duplicate and overlapping jobs identified in the duplicate code audit. Jobs are background tasks that handle time-consuming operations asynchronously.

## Current Job Inventory

**Total Jobs:** 17

1. BackupRouterJob
2. CheckRouterHealth
3. CleanupExpiredTempCustomersJob
4. CollectBandwidthDataJob
5. GenerateBillingReportJob
6. ImportIpPoolsJob
7. ImportPppCustomersJob
8. ImportPppSecretsJob
9. MirrorUsersJob
10. PPPoEProfilesIpAllocationModeChangeJob
11. ProcessAutoDebitJob
12. ProcessPaymentJob
13. ProvisionUserJob
14. ReAllocateIPv4ForProfileJob
15. SendBulkSmsJob
16. SendInvoiceEmailJob
17. SyncMikrotikSessionJob

## Identified Overlaps and Recommendations

### 1. Import-Related Jobs (HIGH PRIORITY)

**Current State:**
- `ImportIpPoolsJob` (600s timeout, 1 try)
- `ImportPppSecretsJob` (600s timeout, 1 try)
- `ImportPppCustomersJob` (1800s timeout, 1 try)

**Issue:**
All three jobs share similar patterns (MikrotikImportService, logging success/failed counts). `ImportPppSecretsJob` and `ImportPppCustomersJob` both handle PPP-related imports with potential overlap.

**Analysis:**
```php
// Current pattern in all three:
public function handle()
{
    $service = app(MikrotikImportService::class);
    $result = $service->importXXX($this->router);
    
    Log::info('Import completed', [
        'router_id' => $this->router->id,
        'success_count' => $result['success'],
        'failed_count' => $result['failed']
    ]);
}
```

**Recommendation:**
Create a generic `ImportFromRouterJob` using Strategy Pattern:

```php
class ImportFromRouterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800; // Max timeout
    public $tries = 1;

    public function __construct(
        private readonly MikrotikRouter $router,
        private readonly string $importType,
        private readonly array $options = []
    ) {}

    public function handle(MikrotikImportService $service)
    {
        $result = match($this->importType) {
            'ip_pools' => $service->importIpPools($this->router),
            'ppp_secrets' => $service->importPppSecrets($this->router, $this->options),
            'ppp_customers' => $service->importPppCustomers($this->router, $this->options),
            default => throw new \InvalidArgumentException("Unknown import type: {$this->importType}")
        };

        Log::info("Import {$this->importType} completed", [
            'router_id' => $this->router->id,
            'success_count' => $result['success'] ?? 0,
            'failed_count' => $result['failed'] ?? 0,
            'duration' => $result['duration'] ?? 0
        ]);

        return $result;
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Import {$this->importType} failed", [
            'router_id' => $this->router->id,
            'error' => $exception->getMessage()
        ]);
    }
}
```

**Usage:**
```php
// Instead of:
ImportIpPoolsJob::dispatch($router);
ImportPppSecretsJob::dispatch($router);
ImportPppCustomersJob::dispatch($router);

// Use:
ImportFromRouterJob::dispatch($router, 'ip_pools');
ImportFromRouterJob::dispatch($router, 'ppp_secrets');
ImportFromRouterJob::dispatch($router, 'ppp_customers');
```

**Benefits:**
- Single job to maintain
- Consistent error handling
- Easier to add new import types
- Centralized logging
- Better monitoring

**Migration Steps:**
1. Create `ImportFromRouterJob`
2. Update dispatchers to use new job
3. Test thoroughly with all import types
4. Deprecate old jobs
5. Remove old jobs after verification

---

### 2. User Provisioning/Syncing Jobs (MEDIUM PRIORITY)

**Current State:**
- `ProvisionUserJob` - Provisions users to routers
- `MirrorUsersJob` - Syncs users to routers (uses RouterBackupService)
- `SyncMikrotikSessionJob` - Syncs router sessions

**Issue:**
`ProvisionUserJob` and `MirrorUsersJob` both handle user/router synchronization but use different services and approaches.

**Analysis:**
- `ProvisionUserJob` - Creates/updates single user on router
- `MirrorUsersJob` - Bulk sync all users as backup/fallback
- `SyncMikrotikSessionJob` - Syncs active sessions from router to database

**Recommendation:**
Keep as separate jobs but clarify naming and responsibilities:

```php
// Rename for clarity
class ProvisionSingleUserJob // Current: ProvisionUserJob
{
    // Provisions a single user to specific router
    // Use when: User created/updated, immediate provisioning needed
}

class BulkSyncUsersToRouterJob // Current: MirrorUsersJob
{
    // Syncs all users to router as backup
    // Use when: Router failover, disaster recovery, full sync needed
}

class SyncActiveSessionsJob // Current: SyncMikrotikSessionJob
{
    // Syncs active sessions from router to database
    // Use when: Monitoring, usage tracking, session management
}
```

**Benefits:**
- Clear names indicate purpose
- No confusion about which job to use
- Each job has single responsibility
- Easy to maintain separately

---

### 3. Communication Jobs (MEDIUM PRIORITY)

**Current State:**
- `SendBulkSmsJob` (tries=3, timeout=120s)
- `SendInvoiceEmailJob` (tries=3, timeout=60s)

**Issue:**
Both are communication jobs with similar structure (retries, timeouts, bulk sending).

**Recommendation:**
Create a generic `SendNotificationJob` with channel strategies:

```php
class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Exponential backoff

    public function __construct(
        private readonly string $channel, // 'sms' or 'email'
        private readonly Collection $recipients,
        private readonly string $template,
        private readonly array $data = []
    ) {}

    public function handle(NotificationService $service)
    {
        $result = match($this->channel) {
            'sms' => $service->sendBulkSms($this->recipients, $this->template, $this->data),
            'email' => $service->sendBulkEmail($this->recipients, $this->template, $this->data),
            default => throw new \InvalidArgumentException("Unknown channel: {$this->channel}")
        };

        Log::info("Notification sent via {$this->channel}", [
            'recipient_count' => $this->recipients->count(),
            'success_count' => $result['success'] ?? 0,
            'failed_count' => $result['failed'] ?? 0
        ]);
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Notification failed via {$this->channel}", [
            'recipient_count' => $this->recipients->count(),
            'error' => $exception->getMessage()
        ]);
    }
}
```

**Alternative Recommendation:**
Keep separate but add shared base class:

```php
abstract class BaseCommunicationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    protected function logSuccess(string $channel, int $count, array $details = [])
    {
        Log::info("Notification sent via {$channel}", array_merge([
            'recipient_count' => $count,
        ], $details));
    }

    protected function logFailure(string $channel, \Throwable $exception)
    {
        Log::error("Notification failed via {$channel}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    abstract public function handle();
}

class SendBulkSmsJob extends BaseCommunicationJob { ... }
class SendInvoiceEmailJob extends BaseCommunicationJob { ... }
```

**Benefits:**
- Consistent retry logic
- Shared error handling
- Easier to add new channels (push, webhook, etc.)
- Better monitoring

---

### 4. Payment Processing Jobs (MEDIUM PRIORITY)

**Current State:**
- `ProcessAutoDebitJob` - Auto-charges customers
- `ProcessPaymentJob` - Processes payment transactions

**Issue:**
Both handle payment processing but serve different triggers (automatic vs. manual).

**Analysis:**
These jobs have different concerns:
- `ProcessAutoDebitJob` - Scheduled job, batch processing, automatic charges
- `ProcessPaymentJob` - Event-driven, single payment, manual processing

**Recommendation:**
Keep separate but extract shared payment logic:

```php
// Shared service
class PaymentProcessingService
{
    public function processPayment(Payment $payment): PaymentResult
    {
        // Shared validation
        // Shared gateway interaction
        // Shared record keeping
        // Shared notification
    }
}

// Jobs use the service
class ProcessAutoDebitJob
{
    public function handle(PaymentProcessingService $service)
    {
        $dueCustomers = $this->findDueCustomers();
        
        foreach ($dueCustomers as $customer) {
            try {
                $payment = $this->createAutoDebitPayment($customer);
                $service->processPayment($payment);
            } catch (\Exception $e) {
                $this->handleFailure($customer, $e);
            }
        }
    }
}

class ProcessPaymentJob
{
    public function handle(PaymentProcessingService $service)
    {
        $service->processPayment($this->payment);
    }
}
```

**Benefits:**
- Shared logic in service layer
- Jobs focus on orchestration
- Easier to test payment logic
- Consistent payment processing

---

### 5. Router Configuration Jobs (LOW PRIORITY)

**Current State:**
- `BackupRouterJob` - Backs up router configs
- `PPPoEProfilesIpAllocationModeChangeJob` - Modifies PPPoE profiles
- `ReAllocateIPv4ForProfileJob` - Reallocates IPs for profiles

**Analysis:**
These jobs handle different aspects of router configuration:
- Backup - Read-only operation, scheduled
- Profile modification - Write operation, admin-triggered
- IP reallocation - Write operation, admin-triggered

**Recommendation:**
Keep separate - they serve different purposes. However, add shared base class for router operations:

```php
abstract class BaseRouterOperationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 1;

    protected function validateRouterConnection(MikrotikRouter $router): bool
    {
        // Shared connection validation
    }

    protected function logRouterOperation(MikrotikRouter $router, string $operation, array $details = [])
    {
        // Shared logging
    }

    abstract public function handle();
}
```

---

## Jobs That Should NOT Be Consolidated

These jobs serve unique purposes and should remain independent:

1. **CheckRouterHealth** - Unique health monitoring logic
2. **CleanupExpiredTempCustomersJob** - Unique cleanup task
3. **GenerateBillingReportJob** - Unique financial reporting
4. **CollectBandwidthDataJob** - Unique bandwidth collection

## Implementation Priority

### Phase 1: High Priority (Sprint 1)
- [ ] Consolidate import jobs → `ImportFromRouterJob`
- [ ] Create base communication job class

**Estimated Effort:** 2-3 days

### Phase 2: Medium Priority (Sprint 2)
- [ ] Clarify provisioning job naming
- [ ] Extract shared payment logic
- [ ] Update communication jobs

**Estimated Effort:** 3-4 days

### Phase 3: Low Priority (Sprint 3)
- [ ] Create base router operation job
- [ ] Add comprehensive job monitoring
- [ ] Document job usage patterns

**Estimated Effort:** 2-3 days

## Testing Strategy

For each job consolidation:
1. **Unit Tests:** Test job dispatch and handling
2. **Integration Tests:** Test with real services
3. **Queue Tests:** Verify retry logic and failures
4. **Performance Tests:** Ensure no degradation
5. **Monitoring:** Add metrics for job execution

## Queue Configuration

Update queue configuration for consolidated jobs:

```php
// config/queue.php
'jobs' => [
    'import' => [
        'connection' => 'redis',
        'queue' => 'imports',
        'retry_after' => 1800,
    ],
    'notifications' => [
        'connection' => 'redis',
        'queue' => 'notifications',
        'retry_after' => 300,
    ],
    'provisioning' => [
        'connection' => 'redis',
        'queue' => 'provisioning',
        'retry_after' => 600,
    ],
],
```

## Monitoring Recommendations

1. Add job metrics to dashboard
2. Set up alerts for failed jobs
3. Track job execution time
4. Monitor queue depth
5. Log all job transitions (queued → processing → completed/failed)

## Migration Checklist

For each job consolidation:
- [ ] Create new job class
- [ ] Update all dispatchers
- [ ] Update tests
- [ ] Deploy to staging
- [ ] Monitor for issues
- [ ] Deploy to production
- [ ] Deprecate old jobs
- [ ] Remove old jobs after verification period

## Success Metrics

- [ ] Reduced number of job classes by 20-30%
- [ ] Improved code reusability
- [ ] Consistent error handling across jobs
- [ ] Better job monitoring and alerts
- [ ] Easier to add new job types
- [ ] Reduced maintenance burden

## Notes

- Keep job execution idempotent when possible
- Use database transactions where appropriate
- Implement proper error recovery
- Add comprehensive logging
- Consider job chaining for complex workflows
- Use job middleware for cross-cutting concerns
