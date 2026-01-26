<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\NetworkUser;
use App\Models\RadReply;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerSpeedLimitController extends Controller
{
    /**
     * Display speed limit management for a customer.
     */
    public function show(User $customer): View
    {
        $this->authorize('editSpeedLimit', $customer);

        $networkUser = NetworkUser::with('package')->where('user_id', $customer->id)->first();
        
        // Get current speed limit from RADIUS
        $speedLimit = null;
        if ($networkUser) {
            $radReply = RadReply::where('username', $networkUser->username)
                ->where('attribute', 'Mikrotik-Rate-Limit')
                ->first();
            
            if ($radReply) {
                // Parse format: upload/download (e.g., "512k/1024k", "512/1024", "512k / 1024k")
                $parts = array_map('trim', explode('/', $radReply->value));
                if (count($parts) === 2) {
                    $upload = preg_replace('/[^0-9]/', '', $parts[0]);
                    $download = preg_replace('/[^0-9]/', '', $parts[1]);
                    
                    if (is_numeric($upload) && is_numeric($download)) {
                        $speedLimit = [
                            'upload' => (int) $upload,
                            'download' => (int) $download,
                        ];
                    }
                }
            }
        }

        // Get package default speeds
        $packageSpeed = null;
        if ($networkUser && $networkUser->package) {
            $packageSpeed = [
                'upload' => $networkUser->package->bandwidth_upload,
                'download' => $networkUser->package->bandwidth_download,
            ];
        }

        return view('panel.customers.speed-limit.show', compact('customer', 'networkUser', 'speedLimit', 'packageSpeed'));
    }

    /**
     * Update or create speed limit for a customer.
     */
    public function update(Request $request, User $customer): RedirectResponse
    {
        $this->authorize('editSpeedLimit', $customer);

        $request->validate([
            'upload_speed' => 'required|integer|min:0',
            'download_speed' => 'required|integer|min:0',
        ]);

        $networkUser = NetworkUser::where('user_id', $customer->id)->firstOrFail();

        DB::beginTransaction();
        try {
            $uploadSpeed = (int) $request->input('upload_speed');
            $downloadSpeed = (int) $request->input('download_speed');

            // If "0 = managed by router" option is selected
            if ($uploadSpeed === 0 && $downloadSpeed === 0) {
                // Remove custom rate limit, let router/package manage
                RadReply::where('username', $networkUser->username)
                    ->where('attribute', 'Mikrotik-Rate-Limit')
                    ->delete();

                $this->logAction($customer, 'Speed limit removed - managed by router');

                DB::commit();
                return back()->with('success', 'Speed limit removed. Now managed by router/package settings.');
            }

            // Validate speeds
            if ($uploadSpeed <= 0 || $downloadSpeed <= 0) {
                return back()->withErrors(['error' => 'Upload and download speeds must be greater than 0']);
            }

            // Format: upload/download (in Kbps)
            $rateLimit = "{$uploadSpeed}k/{$downloadSpeed}k";

            // Update RADIUS attribute
            RadReply::updateOrCreate(
                ['username' => $networkUser->username, 'attribute' => 'Mikrotik-Rate-Limit'],
                ['op' => ':=', 'value' => $rateLimit]
            );

            // Log action
            $this->logAction($customer, "Speed limit updated to {$uploadSpeed}Kbps upload / {$downloadSpeed}Kbps download");

            DB::commit();

            return back()->with('success', 'Speed limit updated successfully. Customer needs to reconnect for changes to take effect.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update speed limit', [
                'customer_id' => $customer->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to update speed limit. Please try again or contact support.']);
        }
    }

    /**
     * Reset speed limit to package default.
     */
    public function reset(User $customer): RedirectResponse
    {
        $this->authorize('editSpeedLimit', $customer);

        $networkUser = NetworkUser::where('user_id', $customer->id)->firstOrFail();

        DB::beginTransaction();
        try {
            if (!$networkUser->package) {
                return back()->withErrors(['error' => 'Customer has no package assigned.']);
            }

            $uploadSpeed = $networkUser->package->bandwidth_upload;
            $downloadSpeed = $networkUser->package->bandwidth_download;

            if (!$uploadSpeed || !$downloadSpeed) {
                return back()->withErrors(['error' => 'Package has no speed limits defined.']);
            }

            // Format: upload/download (in Kbps)
            $rateLimit = "{$uploadSpeed}k/{$downloadSpeed}k";

            // Update RADIUS attribute
            RadReply::updateOrCreate(
                ['username' => $networkUser->username, 'attribute' => 'Mikrotik-Rate-Limit'],
                ['op' => ':=', 'value' => $rateLimit]
            );

            // Log action
            $this->logAction($customer, "Speed limit reset to package default: {$uploadSpeed}Kbps / {$downloadSpeed}Kbps");

            DB::commit();

            return back()->with('success', 'Speed limit reset to package default successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to reset speed limit', [
                'customer_id' => $customer->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to reset speed limit. Please try again or contact support.']);
        }
    }

    /**
     * Remove speed limit (let router manage).
     */
    public function destroy(User $customer): RedirectResponse
    {
        $this->authorize('editSpeedLimit', $customer);

        $networkUser = NetworkUser::where('user_id', $customer->id)->firstOrFail();

        DB::beginTransaction();
        try {
            // Remove custom rate limit
            RadReply::where('username', $networkUser->username)
                ->where('attribute', 'Mikrotik-Rate-Limit')
                ->delete();

            // Log action
            $this->logAction($customer, 'Speed limit removed - now managed by router');

            DB::commit();

            return back()->with('success', 'Speed limit removed successfully. Router will manage bandwidth.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to remove speed limit', [
                'customer_id' => $customer->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to remove speed limit. Please try again or contact support.']);
        }
    }

    /**
     * Log action to audit log.
     */
    protected function logAction(User $customer, string $description): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'tenant_id' => $customer->tenant_id,
            'event' => 'customer.speed_limit.update',
            'auditable_type' => User::class,
            'auditable_id' => $customer->id,
            'new_values' => ['description' => $description],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
