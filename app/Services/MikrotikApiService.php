<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MikrotikRouter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MikroTik API Service
 *
 * Provides generic methods for interacting with MikroTik RouterOS API.
 * This service acts as an adapter providing the expected interface
 * for router operations, wrapping HTTP API calls to the router.
 *
 * SECURITY NOTE:
 * Current implementation uses HTTP for compatibility with test/dev environments.
 * For production use, configure routers to use HTTPS and update the protocol
 * in the URL construction methods below. Ensure certificate validation is enabled.
 */
class MikrotikApiService
{
    /**
     * Get rows from a MikroTik menu.
     *
     * @param MikrotikRouter $router The router to query
     * @param string $menu The menu path (e.g., '/ip/pool', '/ppp/profile')
     * @param array $query Optional query filters
     *
     * @return array Array of rows from the router
     */
    public function getMktRows(MikrotikRouter $router, string $menu, array $query = []): array
    {
        try {
            $endpoint = $this->menuToEndpoint($menu);
            $scheme = config('services.mikrotik.scheme', app()->environment('production') ? 'https' : 'http');
            $url = "{$scheme}://{$router->ip_address}:{$router->api_port}/api{$endpoint}";

            $response = Http::withBasicAuth($router->username, $router->password)
                ->timeout(config('services.mikrotik.timeout', 30))
                ->get($url, $query);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Successfully fetched rows from MikroTik', [
                    'router_id' => $router->id,
                    'menu' => $menu,
                    'count' => is_array($data) ? count($data) : 0,
                ]);

                return is_array($data) ? $data : [];
            }

            Log::warning('Failed to fetch rows from MikroTik', [
                'router_id' => $router->id,
                'menu' => $menu,
                'status' => $response->status(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching rows from MikroTik', [
                'router_id' => $router->id,
                'menu' => $menu,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Add rows to a MikroTik menu.
     *
     * @param MikrotikRouter $router The router to modify
     * @param string $menu The menu path
     * @param array $rows Array of rows to add
     *
     * @return bool True if successful
     */
    public function addMktRows(MikrotikRouter $router, string $menu, array $rows): bool
    {
        try {
            $endpoint = $this->menuToEndpoint($menu);
            $scheme = config('services.mikrotik.scheme', app()->environment('production') ? 'https' : 'http');
            $url = "{$scheme}://{$router->ip_address}:{$router->api_port}/api{$endpoint}/add";

            $successCount = 0;
            $failedCount = 0;

            foreach ($rows as $row) {
                $response = Http::withBasicAuth($router->username, $router->password)
                    ->timeout(config('services.mikrotik.timeout', 30))
                    ->post($url, $row);

                if ($response->successful()) {
                    $successCount++;
                } else {
                    $failedCount++;
                    Log::warning('Failed to add row to MikroTik', [
                        'router_id' => $router->id,
                        'menu' => $menu,
                        'row_keys' => array_keys($row),
                        'status' => $response->status(),
                    ]);
                }
            }

            Log::info('Added rows to MikroTik', [
                'router_id' => $router->id,
                'menu' => $menu,
                'success' => $successCount,
                'failed' => $failedCount,
            ]);

            return $failedCount === 0;
        } catch (\Exception $e) {
            Log::error('Error adding rows to MikroTik', [
                'router_id' => $router->id,
                'menu' => $menu,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Edit a row in a MikroTik menu.
     *
     * @param MikrotikRouter $router The router to modify
     * @param string $menu The menu path
     * @param array $row Identifier for the row to edit
     * @param array $data New data for the row
     *
     * @return bool True if successful
     */
    public function editMktRow(MikrotikRouter $router, string $menu, array $row, array $data): bool
    {
        try {
            $endpoint = $this->menuToEndpoint($menu);
            $scheme = config('services.mikrotik.scheme', app()->environment('production') ? 'https' : 'http');
            $url = "{$scheme}://{$router->ip_address}:{$router->api_port}/api{$endpoint}/set";

            // Merge row identifier with new data
            $payload = array_merge($row, $data);

            $response = Http::withBasicAuth($router->username, $router->password)
                ->timeout(config('services.mikrotik.timeout', 30))
                ->put($url, $payload);

            if ($response->successful()) {
                Log::info('Successfully edited row on MikroTik', [
                    'router_id' => $router->id,
                    'menu' => $menu,
                    'row_keys' => array_keys($row),
                ]);

                return true;
            }

            Log::warning('Failed to edit row on MikroTik', [
                'router_id' => $router->id,
                'menu' => $menu,
                'row_keys' => array_keys($row),
                'status' => $response->status(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error editing row on MikroTik', [
                'router_id' => $router->id,
                'menu' => $menu,
                'row_keys' => array_keys($row),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Remove rows from a MikroTik menu.
     *
     * @param MikrotikRouter $router The router to modify
     * @param string $menu The menu path
     * @param array $rows Array of rows to remove (typically containing 'id' or 'name' identifiers)
     *
     * @return bool True if successful
     */
    public function removeMktRows(MikrotikRouter $router, string $menu, array $rows): bool
    {
        try {
            $endpoint = $this->menuToEndpoint($menu);
            $scheme = config('services.mikrotik.scheme', app()->environment('production') ? 'https' : 'http');
            $baseUrl = "{$scheme}://{$router->ip_address}:{$router->api_port}/api{$endpoint}/remove";

            $successCount = 0;
            $failedCount = 0;

            foreach ($rows as $row) {
                // Build query string from row identifiers
                $queryParams = http_build_query($row);
                $url = $baseUrl.'?'.$queryParams;

                $response = Http::withBasicAuth($router->username, $router->password)
                    ->timeout(config('services.mikrotik.timeout', 30))
                    ->delete($url);

                if ($response->successful()) {
                    $successCount++;
                } else {
                    $failedCount++;
                    Log::warning('Failed to remove row from MikroTik', [
                        'router_id' => $router->id,
                        'menu' => $menu,
                        'row_keys' => array_keys($row),
                        'status' => $response->status(),
                    ]);
                }
            }

            Log::info('Removed rows from MikroTik', [
                'router_id' => $router->id,
                'menu' => $menu,
                'success' => $successCount,
                'failed' => $failedCount,
            ]);

            return $failedCount === 0;
        } catch (\Exception $e) {
            Log::error('Error removing rows from MikroTik', [
                'router_id' => $router->id,
                'menu' => $menu,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Execute a command via TTY (terminal).
     *
     * @param MikrotikRouter $router The router to execute command on
     * @param string $command The command to execute
     * @param array $params Optional command parameters
     *
     * @return mixed Command output or null on failure
     */
    public function ttyWrite(MikrotikRouter $router, string $command, array $params = []): mixed
    {
        try {
            $scheme = config('services.mikrotik.scheme', app()->environment('production') ? 'https' : 'http');
            $url = "{$scheme}://{$router->ip_address}:{$router->api_port}/api/terminal";

            $response = Http::withBasicAuth($router->username, $router->password)
                ->timeout(config('services.mikrotik.timeout', 30))
                ->post($url, [
                    'command' => $command,
                    'params' => $params,
                ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('Successfully executed TTY command on MikroTik', [
                    'router_id' => $router->id,
                    'command' => $command,
                ]);

                return $result;
            }

            Log::warning('Failed to execute TTY command on MikroTik', [
                'router_id' => $router->id,
                'command' => $command,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error executing TTY command on MikroTik', [
                'router_id' => $router->id,
                'command' => $command,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Convert MikroTik menu path to API endpoint.
     *
     * @param string $menu Menu path (e.g., '/ip/pool', '/ppp/profile')
     *
     * @return string API endpoint path
     */
    private function menuToEndpoint(string $menu): string
    {
        // Normalize menu path: remove any leading/trailing slashes
        $menu = trim($menu, '/');

        // Ensure a single leading slash in the API endpoint
        return $menu === '' ? '/' : '/'.$menu;
    }
}
