<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Customer;
use App\Models\MikrotikRouter;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * UserProvisioned Event
 *
 * Fired when a user is successfully provisioned to a router.
 */
class UserProvisioned
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Customer $customer,
        public MikrotikRouter $router,
        public string $username,
        public array $provisioningData = []
    ) {
    }

    /**
     * Get provisioning details.
     */
    public function getProvisioningDetails(): array
    {
        return array_merge([
            'customer_id' => $this->customer->id,
            'router_id' => $this->router->id,
            'router_name' => $this->router->name,
            'username' => $this->username,
            'timestamp' => now()->toDateTimeString(),
        ], $this->provisioningData);
    }
}
