<?php

namespace App\Jobs;

use App\Models\MikrotikRouter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMikrotikSessionJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 90;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $routerId,
        public ?string $username = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $router = MikrotikRouter::findOrFail($this->routerId);

            Log::info('Syncing MikroTik session', [
                'router_id' => $this->routerId,
                'router_name' => $router->name,
                'username' => $this->username,
            ]);

            $mikrotikService = app(\App\Services\MikrotikService::class);

            // Get active sessions from the router
            $sessions = $mikrotikService->getActiveSessions($router->id);

            $syncedCount = 0;

            if ($this->username) {
                // Filter sessions for specific username
                foreach ($sessions as $session) {
                    $sessionUsername = null;

                    if (is_array($session)) {
                        $sessionUsername = $session['username'] ?? $session['user'] ?? $session['name'] ?? null;
                    } elseif (is_object($session)) {
                        $sessionUsername = $session->username ?? $session->user ?? $session->name ?? null;
                    }

                    if ($sessionUsername === $this->username) {
                        $syncedCount++;
                        Log::debug('Found session for specific user', [
                            'username' => $this->username,
                            'session' => $session,
                        ]);
                    }
                }
            } else {
                // All sessions
                $syncedCount = is_countable($sessions) ? count($sessions) : 0;
                Log::debug('Retrieved all active sessions', [
                    'session_count' => $syncedCount,
                ]);
            }

            Log::info('MikroTik session synced successfully', [
                'router_id' => $this->routerId,
                'username' => $this->username,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync MikroTik session', [
                'router_id' => $this->routerId,
                'username' => $this->username,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('SyncMikrotikSessionJob failed permanently', [
            'router_id' => $this->routerId,
            'username' => $this->username,
            'error' => $exception?->getMessage(),
        ]);
    }
}
