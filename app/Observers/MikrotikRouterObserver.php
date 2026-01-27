<?php

namespace App\Observers;

use App\Models\MikrotikRouter;
use App\Models\Nas;
use Illuminate\Support\Facades\Log;

class MikrotikRouterObserver
{
    /**
     * Handle the MikrotikRouter "created" event.
     * Automatically creates a corresponding NAS entry for RADIUS authentication.
     */
    public function created(MikrotikRouter $mikrotikRouter): void
    {
        // Skip if NAS is already linked
        if ($mikrotikRouter->nas_id) {
            return;
        }

        try {
            // Create a NAS entry for this router
            $nas = Nas::create([
                'tenant_id' => $mikrotikRouter->tenant_id,
                'name' => $mikrotikRouter->name . ' (Auto-created)',
                'nas_name' => $mikrotikRouter->name,
                'short_name' => $this->generateShortName($mikrotikRouter->name),
                'type' => 'mikrotik',
                'ports' => 1812, // Standard RADIUS port
                'secret' => $mikrotikRouter->radius_secret ?? 'secret',
                'server' => $mikrotikRouter->ip_address,
                'description' => 'Auto-created NAS entry for Mikrotik router: ' . $mikrotikRouter->name,
                'status' => $mikrotikRouter->status === 'active' ? 'active' : 'inactive',
            ]);

            // Link the router to the newly created NAS
            $mikrotikRouter->nas_id = $nas->id;
            $mikrotikRouter->saveQuietly(); // Use saveQuietly to avoid triggering observer again

            Log::info('Auto-created NAS entry for Mikrotik router', [
                'router_id' => $mikrotikRouter->id,
                'router_name' => $mikrotikRouter->name,
                'nas_id' => $nas->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to auto-create NAS entry for Mikrotik router', [
                'router_id' => $mikrotikRouter->id,
                'router_name' => $mikrotikRouter->name,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the MikrotikRouter "updated" event.
     * Syncs changes to the linked NAS entry.
     */
    public function updated(MikrotikRouter $mikrotikRouter): void
    {
        // Skip if no NAS is linked
        if (! $mikrotikRouter->nas_id) {
            return;
        }

        try {
            $nas = Nas::find($mikrotikRouter->nas_id);
            if ($nas) {
                // Sync relevant fields to NAS
                $nas->update([
                    'server' => $mikrotikRouter->ip_address,
                    'secret' => $mikrotikRouter->radius_secret ?? $nas->secret,
                    'status' => $mikrotikRouter->status === 'active' ? 'active' : 'inactive',
                ]);

                Log::info('Synced Mikrotik router changes to NAS entry', [
                    'router_id' => $mikrotikRouter->id,
                    'nas_id' => $nas->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync Mikrotik router changes to NAS entry', [
                'router_id' => $mikrotikRouter->id,
                'nas_id' => $mikrotikRouter->nas_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the MikrotikRouter "deleted" event.
     */
    public function deleted(MikrotikRouter $mikrotikRouter): void
    {
        // Optionally delete the associated NAS entry
        // For now, we'll keep it for historical records
        // Uncomment if you want to auto-delete NAS entries:
        // if ($mikrotikRouter->nas_id) {
        //     Nas::find($mikrotikRouter->nas_id)?->delete();
        // }
    }

    /**
     * Generate a short name from the router name.
     */
    private function generateShortName(string $name): string
    {
        // Remove common words and take first 20 chars
        $shortName = preg_replace('/\b(router|mikrotik|mt)\b/i', '', $name);
        $shortName = preg_replace('/\s+/', '-', trim($shortName));
        $shortName = substr($shortName, 0, 20);

        return $shortName ?: substr($name, 0, 20);
    }
}
