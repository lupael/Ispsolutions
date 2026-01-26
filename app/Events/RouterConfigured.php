<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\MikrotikRouter;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * RouterConfigured Event
 *
 * Fired when a router is successfully configured (RADIUS, PPP, Firewall, etc.).
 */
class RouterConfigured
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public MikrotikRouter $router,
        public string $configurationType,
        public bool $success,
        public array $configurationData = []
    ) {
    }

    /**
     * Get configuration details.
     */
    public function getConfigurationDetails(): array
    {
        return array_merge([
            'router_id' => $this->router->id,
            'router_name' => $this->router->name,
            'configuration_type' => $this->configurationType,
            'success' => $this->success,
            'timestamp' => now()->toDateTimeString(),
        ], $this->configurationData);
    }
}
