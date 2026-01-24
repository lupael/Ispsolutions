<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\CustomerImport;
use App\Models\MikrotikRouter;
use App\Models\NetworkUser;
use App\Models\User;
use App\Services\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportPppCustomersJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 1800; // 30 minutes
    public int $tries = 1; // Don't retry to avoid duplicates

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $operatorId,
        public int $nasId,
        public array $options
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(MikrotikService $mikrotikService): void
    {
        // Check for duplicate import
        $existingImport = CustomerImport::where('operator_id', $this->operatorId)
            ->where('nas_id', $this->nasId)
            ->whereDate('created_at', today())
            ->where('status', 'in_progress')
            ->first();

        if ($existingImport) {
            Log::warning('Duplicate import detected, skipping', [
                'operator_id' => $this->operatorId,
                'nas_id' => $this->nasId,
            ]);
            return;
        }

        // Create import tracking record
        $import = CustomerImport::create([
            'operator_id' => $this->operatorId,
            'nas_id' => $this->nasId,
            'status' => 'in_progress',
            'total_count' => 0,
            'success_count' => 0,
            'failed_count' => 0,
            'options' => $this->options,
        ]);

        try {
            // Get router from NAS
            $router = MikrotikRouter::where('nas_id', $this->nasId)->first();
            if (!$router) {
                throw new \Exception('Router not found for NAS');
            }

            // Connect to router
            if (!$mikrotikService->connectRouter($router->id)) {
                throw new \Exception('Failed to connect to router');
            }

            // Fetch PPP secrets from router
            $secrets = $this->fetchPppSecretsFromRouter($router->id, $mikrotikService);
            
            $import->update(['total_count' => count($secrets)]);

            // Resolve tenant from operator
            $operator = User::find($this->operatorId);
            if (!$operator) {
                throw new \Exception('Operator not found');
            }
            $tenantId = $operator->tenant_id;

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            // Process each secret
            foreach ($secrets as $secret) {
                try {
                    $this->processSecret($secret, $tenantId);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = [
                        'username' => $secret['username'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];
                    Log::error('Failed to import customer', [
                        'username' => $secret['username'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                }

                // Update progress every 10 records
                if (($successCount + $failedCount) % 10 === 0) {
                    $import->update([
                        'success_count' => $successCount,
                        'failed_count' => $failedCount,
                    ]);
                }
            }

            // Update final status
            $import->update([
                'status' => 'completed',
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => $errors,
                'completed_at' => now(),
            ]);

            Log::info('PPP customers import completed', [
                'operator_id' => $this->operatorId,
                'nas_id' => $this->nasId,
                'total' => count($secrets),
                'success' => $successCount,
                'failed' => $failedCount,
            ]);
        } catch (\Exception $e) {
            $import->update([
                'status' => 'failed',
                'errors' => [['error' => $e->getMessage()]],
                'completed_at' => now(),
            ]);

            Log::error('PPP customers import job failed', [
                'operator_id' => $this->operatorId,
                'nas_id' => $this->nasId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Fetch PPP secrets from router.
     */
    private function fetchPppSecretsFromRouter(int $routerId, MikrotikService $mikrotikService): array
    {
        // In production, this would use actual RouterOS API
        // For now, return mock data for demonstration
        
        // Example implementation:
        // return $mikrotikService->getPppSecrets($routerId, $this->options['filter_disabled']);
        
        return [];
    }

    /**
     * Process a single secret.
     */
    private function processSecret(array $secret, int $tenantId): void
    {
        DB::beginTransaction();
        try {
            // Create or find user
            $user = User::firstOrCreate(
                [
                    'mobile' => $secret['mobile'] ?? $secret['username'],
                    'tenant_id' => $tenantId,
                ],
                [
                    'name' => $secret['name'] ?? $secret['username'],
                    'email' => $secret['email'] ?? null,
                    'password' => bcrypt($secret['password']),
                    'role_id' => $this->getCustomerRoleId(),
                    'is_active' => !($secret['disabled'] ?? false),
                ]
            );

            // Check if network user already exists
            $existingNetworkUser = NetworkUser::where('username', $secret['username'])
                ->where('tenant_id', $tenantId)
                ->first();

            if ($existingNetworkUser) {
                // Skip if already exists
                DB::rollBack();
                return;
            }

            // Create network user
            NetworkUser::create([
                'username' => $secret['username'],
                'password' => $secret['password'],
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'service_type' => 'pppoe',
                'package_id' => $this->options['package_id'] ?? null,
                'status' => ($secret['disabled'] ?? false) ? 'inactive' : 'active',
                'is_active' => !($secret['disabled'] ?? false),
            ]);

            // Generate initial bill if requested
            if ($this->options['generate_bills'] && !($secret['disabled'] ?? false)) {
                // Call billing service to generate bill
                // This would be implemented based on your billing logic
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get customer role ID.
     */
    private function getCustomerRoleId(): int
    {
        return DB::table('roles')->where('slug', 'customer')->value('id') ?? 1;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('PPP customers import job failed permanently', [
            'operator_id' => $this->operatorId,
            'nas_id' => $this->nasId,
            'error' => $exception->getMessage(),
        ]);
    }
}
