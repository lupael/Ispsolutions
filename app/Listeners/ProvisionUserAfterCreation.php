<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserProvisioned;
use Illuminate\Support\Facades\Log;

/**
 * ProvisionUserAfterCreation Listener
 *
 * Listens to UserCreated event and automatically provisions the user to their assigned router.
 * This listener is triggered after a customer is created with an active package.
 *
 * Note: The UserCreated event would need to be created separately if automatic
 * provisioning on user creation is desired. For now, this is a placeholder
 * demonstrating the pattern for event-driven provisioning.
 */
class ProvisionUserAfterCreation
{
    /**
     * Handle the event.
     *
     * @param  object  $event  UserCreated event (to be created)
     */
    public function handle($event): void
    {
        // Check if the customer has an active package and assigned router
        if (! isset($event->customer)) {
            return;
        }

        $customer = $event->customer;

        // Only provision if auto-provisioning is enabled
        if (! config('mikrotik.provisioning.auto_provision_on_create', true)) {
            Log::info('Auto-provisioning disabled, skipping', [
                'customer_id' => $customer->id,
            ]);

            return;
        }

        // Check if customer has an active package
        if (! $customer->activePackage) {
            Log::info('Customer has no active package, skipping provisioning', [
                'customer_id' => $customer->id,
            ]);

            return;
        }

        // Get the router assigned to this customer (via package or direct assignment)
        $router = $customer->assignedRouter;

        if (! $router) {
            Log::info('No router assigned to customer, skipping provisioning', [
                'customer_id' => $customer->id,
            ]);

            return;
        }

        // Dispatch provisioning job
        Log::info('Dispatching user provisioning job', [
            'customer_id' => $customer->id,
            'router_id' => $router->id,
        ]);

        \App\Jobs\ProvisionUserJob::dispatch($customer, $router)
            ->onQueue('provisioning');
    }
}
