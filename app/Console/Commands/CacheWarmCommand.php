<?php

namespace App\Console\Commands;

use App\Models\Package;
use App\Models\PaymentGateway;
use App\Models\Tenant;
use App\Services\CacheService;
use Illuminate\Console\Command;

class CacheWarmCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm 
                            {--tenant= : Specific tenant ID to warm cache for}
                            {--all : Warm cache for all tenants}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up common caches for improved performance';

    protected CacheService $cacheService;

    /**
     * Create a new command instance.
     */
    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting cache warm-up...');

        if ($this->option('all')) {
            $this->warmAllTenants();
        } elseif ($tenantId = $this->option('tenant')) {
            $this->warmTenantCache((int) $tenantId);
        } else {
            $this->error('Please specify --tenant=ID or --all option');

            return Command::FAILURE;
        }

        $this->info('Cache warm-up completed successfully!');

        return Command::SUCCESS;
    }

    /**
     * Warm cache for all tenants.
     */
    private function warmAllTenants(): void
    {
        $tenants = Tenant::select('id', 'name')->get();
        $progressBar = $this->output->createProgressBar($tenants->count());

        $this->info("Warming cache for {$tenants->count()} tenants...");

        foreach ($tenants as $tenant) {
            $this->warmTenantCache($tenant->id);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
    }

    /**
     * Warm cache for a specific tenant.
     */
    private function warmTenantCache(int $tenantId): void
    {
        $this->line("Warming cache for tenant {$tenantId}...");

        // Warm packages cache
        $packages = Package::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->select('id', 'name', 'price', 'bandwidth_upload', 'bandwidth_download')
            ->get();
        $this->cacheService->cachePackages($tenantId, $packages);
        $this->line("  - Cached {$packages->count()} packages");

        // Task 1.4: Pre-populate package customer count caches
        // Use withCount to avoid N+1 queries
        $packagesWithCounts = Package::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->withCount('users')
            ->get();
        
        foreach ($packagesWithCounts as $package) {
            // Manually populate the cache with the count
            Cache::put(
                "package_customerCount_{$package->id}",
                $package->users_count,
                150
            );
        }
        $this->line("  - Cached customer counts for {$packagesWithCounts->count()} packages");

        // Warm payment gateways cache
        $gateways = PaymentGateway::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->select('id', 'name', 'type', 'configuration')
            ->get();
        $this->cacheService->cachePaymentGateways($tenantId, $gateways);
        $this->line("  - Cached {$gateways->count()} payment gateways");

        // Warm dashboard stats (example)
        $stats = [
            'total_users' => \App\Models\NetworkUser::where('tenant_id', $tenantId)->count(),
            'active_users' => \App\Models\NetworkUser::where('tenant_id', $tenantId)
                ->where('status', 'active')->count(),
            'total_invoices' => \App\Models\Invoice::where('tenant_id', $tenantId)->count(),
            'pending_payments' => \App\Models\Payment::where('tenant_id', $tenantId)
                ->where('status', 'pending')->count(),
        ];
        $this->cacheService->cacheDashboardStats($tenantId, $stats);
        $this->line('  - Cached dashboard statistics');
    }
}
