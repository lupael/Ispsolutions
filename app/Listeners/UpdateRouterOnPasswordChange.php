<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\RouterProvisioningService;
use Illuminate\Support\Facades\Log;

/**
 * UpdateRouterOnPasswordChange Listener
 *
 * Listens to PasswordChanged event and updates the PPP secret on the router.
 * This ensures that when a customer's password is changed, their router credentials
 * are automatically updated.
 *
 * Note: The PasswordChanged event would need to be created separately if automatic
 * router updates on password change is desired. For now, this is a placeholder
 * demonstrating the pattern for event-driven router updates.
 */
class UpdateRouterOnPasswordChange
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private RouterProvisioningService $provisioningService
    ) {
    }

    /**
     * Handle the event.
     *
     * @param  object  $event  PasswordChanged event (to be created)
     */
    public function handle($event): void
    {
        // Check if the customer exists and has a router assignment
        if (! isset($event->customer)) {
            return;
        }

        $customer = $event->customer;

        // Only update if auto-update is enabled
        if (! config('mikrotik.provisioning.update_on_password_change', true)) {
            Log::info('Auto-update on password change disabled, skipping', [
                'customer_id' => $customer->id,
            ]);

            return;
        }

        // Get the router assigned to this customer
        $router = $customer->assignedRouter;

        if (! $router) {
            Log::info('No router assigned to customer, skipping password update', [
                'customer_id' => $customer->id,
            ]);

            return;
        }

        try {
            // Update the PPP secret on the router with the new password
            Log::info('Updating router PPP secret after password change', [
                'customer_id' => $customer->id,
                'router_id' => $router->id,
            ]);

            $this->provisioningService->updateUserPassword(
                $customer,
                $router,
                $event->newPassword ?? $customer->password
            );

            Log::info('Router PPP secret updated successfully', [
                'customer_id' => $customer->id,
                'router_id' => $router->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update router PPP secret', [
                'customer_id' => $customer->id,
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
