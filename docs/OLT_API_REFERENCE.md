# OLT Service Quick Reference

## Available Methods

### Connection Management

```php
// Test connection and measure latency
testConnection(int $oltId): array
// Returns: ['success' => bool, 'message' => string, 'latency' => int]

// Manually connect to OLT
connect(int $oltId): bool

// Disconnect from OLT
disconnect(int $oltId): bool
```

### ONU Discovery & Management

```php
// Discover all ONUs on the OLT
discoverOnus(int $oltId): array
// Returns: array of ONUs with serial_number, pon_port, onu_id, status

// Sync discovered ONUs to database
syncOnus(int $oltId): int
// Returns: count of synced ONUs

// Get detailed ONU status
getOnuStatus(int $onuId): array
// Returns: status, signal_rx, signal_tx, distance, uptime, last_update

// Refresh ONU status from OLT
refreshOnuStatus(int $onuId): bool
```

### ONU Operations

```php
// Authorize an ONU
authorizeOnu(int $onuId): bool

// Unauthorize an ONU
unauthorizeOnu(int $onuId): bool

// Reboot an ONU
rebootOnu(int $onuId): bool
```

### Backup & Configuration

```php
// Create backup of OLT configuration
createBackup(int $oltId): bool

// Get list of backups
getBackupList(int $oltId): array
// Returns: array of backups with id, file_path, file_size, backup_type, created_at

// Export backup file
exportBackup(int $oltId, string $backupId): ?string
// Returns: file path or null

// Apply configuration commands
applyConfiguration(int $oltId, array $config): bool
```

### Statistics & Monitoring

```php
// Get OLT statistics
getOltStatistics(int $oltId): array
// Returns: uptime, temperature, cpu_usage, memory_usage, total_onus, online_onus, offline_onus

// Get port utilization data
getPortUtilization(int $oltId): array
// Returns: array of ports with rx_bytes, tx_bytes, rx_packets, tx_packets, utilization

// Get bandwidth usage for period
getBandwidthUsage(int $oltId, string $period = 'hourly'): array
// Period: hourly, daily, weekly, monthly
// Returns: array of data points with timestamp, rx_bytes, tx_bytes, rx_rate, tx_rate
```

## Artisan Commands

```bash
# Health Check
php artisan olt:health-check              # Check all active OLTs
php artisan olt:health-check --olt=1      # Check specific OLT
php artisan olt:health-check --details    # Show detailed information

# Sync ONUs
php artisan olt:sync-onus                 # Sync all active OLTs
php artisan olt:sync-onus --olt=1         # Sync specific OLT
php artisan olt:sync-onus --force         # Force sync inactive OLTs

# Backup
php artisan olt:backup                    # Backup all active OLTs
php artisan olt:backup --olt=1            # Backup specific OLT
php artisan olt:backup --force            # Force backup inactive OLTs
```

## Schedule

```php
Schedule::command('olt:health-check')->everyFifteenMinutes();
Schedule::command('olt:sync-onus')->hourly();
Schedule::command('olt:backup')->daily()->at('02:00');
```

## Example Usage

```php
use App\Contracts\OltServiceInterface;

class OltController extends Controller
{
    public function __construct(
        private OltServiceInterface $oltService
    ) {}

    public function dashboard(int $oltId)
    {
        // Test connection
        $connection = $this->oltService->testConnection($oltId);
        
        if (!$connection['success']) {
            return back()->with('error', 'Cannot connect to OLT: ' . $connection['message']);
        }

        // Get statistics
        $stats = $this->oltService->getOltStatistics($oltId);

        return view('olt.dashboard', compact('stats', 'connection'));
    }

    public function syncOnus(int $oltId)
    {
        $count = $this->oltService->syncOnus($oltId);
        
        return back()->with('success', "Synced {$count} ONUs");
    }

    public function createBackup(int $oltId)
    {
        if ($this->oltService->createBackup($oltId)) {
            return back()->with('success', 'Backup created successfully');
        }

        return back()->with('error', 'Failed to create backup');
    }

    public function rebootOnu(int $onuId)
    {
        if ($this->oltService->rebootOnu($onuId)) {
            return back()->with('success', 'ONU reboot initiated');
        }

        return back()->with('error', 'Failed to reboot ONU');
    }
}
```

## Models

### OLT Model
```php
// Relationships
$olt->onus;                 // HasMany Onu
$olt->backups;              // HasMany OltBackup
$olt->tenant;               // BelongsTo Tenant

// Scopes
Olt::active()->get();       // Only active OLTs
Olt::inactive()->get();     // Only inactive OLTs
Olt::maintenance()->get();  // Only OLTs in maintenance

// Helpers
$olt->isActive();           // Check if active
$olt->canConnect();         // Check if can connect
```

### OltBackup Model
```php
// Relationships
$backup->olt;               // BelongsTo Olt

// Methods
$backup->getSize();         // Human-readable size (e.g., "145.23 KB")
$backup->download();        // Download response
$backup->exists();          // Check if file exists
$backup->deleteFile();      // Delete file from storage
```

### ONU Model
```php
// Relationships
$onu->olt;                  // BelongsTo Olt
$onu->tenant;               // BelongsTo Tenant
$onu->networkUser;          // BelongsTo NetworkUser

// Scopes
Onu::online()->get();       // Only online ONUs
Onu::offline()->get();      // Only offline ONUs
Onu::byOlt($oltId)->get();  // Filter by OLT

// Helpers
$onu->isOnline();           // Check if online
$onu->getFullPonPath();     // "OLT-Name / PON Port / ONU ID"
```

## Error Handling

All service methods handle errors gracefully:
- Return `false` for boolean methods on error
- Return empty arrays for array methods on error
- Return `null` for nullable return types on error
- All errors are logged to Laravel log

```php
try {
    $result = $oltService->syncOnus($oltId);
} catch (\Exception $e) {
    Log::error('OLT operation failed: ' . $e->getMessage());
}
```

## Testing

```bash
# Run tests
php artisan test --filter OltServiceTest

# Run specific test
php artisan test --filter test_sync_onus_returns_count

# Run with coverage
php artisan test --filter OltServiceTest --coverage
```
