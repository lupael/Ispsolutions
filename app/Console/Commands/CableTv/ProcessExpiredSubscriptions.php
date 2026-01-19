<?php

namespace App\Console\Commands\CableTv;

use App\Models\CableTvSubscription;
use App\Models\Tenant;
use App\Services\CableTvBillingService;
use Illuminate\Console\Command;

class ProcessExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cabletv:process-expired {--tenant= : Process for specific tenant ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expired cable TV subscriptions and update their status';

    /**
     * Execute the console command.
     */
    public function handle(CableTvBillingService $billingService)
    {
        $this->info('Processing expired cable TV subscriptions...');

        $tenantId = $this->option('tenant');
        
        if ($tenantId) {
            $tenants = Tenant::where('id', $tenantId)->get();
            if ($tenants->isEmpty()) {
                $this->error("Tenant with ID {$tenantId} not found.");
                return 1;
            }
        } else {
            $tenants = Tenant::all();
        }

        $totalExpired = 0;

        foreach ($tenants as $tenant) {
            $this->info("Processing tenant: {$tenant->name} (ID: {$tenant->id})");
            
            $expiredCount = $billingService->processExpiredSubscriptions($tenant->id);
            $totalExpired += $expiredCount;

            $this->line("  - Expired subscriptions: {$expiredCount}");

            // Get expiring soon subscriptions for notifications
            $expiringSoon = $billingService->getExpiringSubscriptions($tenant->id, 7);
            if ($expiringSoon->count() > 0) {
                $this->warn("  - Subscriptions expiring in 7 days: {$expiringSoon->count()}");
                
                foreach ($expiringSoon as $subscription) {
                    $daysRemaining = $subscription->daysRemaining();
                    $this->line("    • {$subscription->subscriber_id} - {$subscription->customer_name} ({$daysRemaining} days remaining)");
                }
            }
        }

        $this->info("Total expired subscriptions processed: {$totalExpired}");
        $this->newLine();
        $this->info('Done!');

        return 0;
    }
}

