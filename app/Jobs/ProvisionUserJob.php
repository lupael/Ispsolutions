<?php

namespace App\Jobs;

use App\Models\MikrotikRouter;
use App\Models\NetworkUser;
use App\Services\RouterProvisioningService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProvisionUserJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300; // 5 minutes
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public int $routerId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(RouterProvisioningService $provisioningService): void
    {
        $user = NetworkUser::find($this->userId);
        $router = MikrotikRouter::find($this->routerId);

        if (!$user) {
            Log::error('User not found for provisioning', ['user_id' => $this->userId]);
            return;
        }

        if (!$router) {
            Log::error('Router not found for provisioning', ['router_id' => $this->routerId]);
            return;
        }

        Log::info('Provisioning user to router via job', [
            'user_id' => $user->id,
            'username' => $user->username,
            'router_id' => $router->id,
            'router_name' => $router->name,
        ]);

        $result = $provisioningService->provisionUser($user, $router);

        if ($result) {
            Log::info('User provisioned successfully via job', [
                'user_id' => $user->id,
                'router_id' => $router->id,
            ]);
        } else {
            Log::error('User provisioning failed via job', [
                'user_id' => $user->id,
                'router_id' => $router->id,
            ]);
            throw new \Exception('Failed to provision user to router');
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProvisionUserJob failed', [
            'user_id' => $this->userId,
            'router_id' => $this->routerId,
            'error' => $exception->getMessage(),
        ]);
    }
}

