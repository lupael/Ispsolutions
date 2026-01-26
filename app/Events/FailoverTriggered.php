<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\MikrotikRouter;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * FailoverTriggered Event
 *
 * Fired when a router failover is triggered (switching between RADIUS and router authentication).
 */
class FailoverTriggered
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public MikrotikRouter $router,
        public string $fromMode,
        public string $toMode,
        public string $reason,
        public array $failoverData = []
    ) {
    }

    /**
     * Get failover details.
     */
    public function getFailoverDetails(): array
    {
        return array_merge([
            'router_id' => $this->router->id,
            'router_name' => $this->router->name,
            'from_mode' => $this->fromMode,
            'to_mode' => $this->toMode,
            'reason' => $this->reason,
            'timestamp' => now()->toDateTimeString(),
        ], $this->failoverData);
    }
}
