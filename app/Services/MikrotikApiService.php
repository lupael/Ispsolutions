<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MikrotikRouter;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MikroTik API Service
 *
 * Provides unified interface for interacting with MikroTik RouterOS API.
 * Supports both REST API (v7+) and Binary API (v6/v7) with automatic detection.
 *
 * SECURITY NOTE:
 * Current implementation uses HTTP for compatibility with test/dev environments.
 * For production use, configure routers to use HTTPS and update the protocol
 * in the URL construction methods below. Ensure certificate validation is enabled.
 */
class MikrotikApiService
{
    protected RouterOSBinaryApiService $binaryApiService;

    /**
     * Cache for detected API types to avoid repeated connection tests.
     * Format: ['router_id' => 'binary|rest']
     */
    private array $apiTypeCache = [];

    public function __construct(RouterOSBinaryApiService $binaryApiService)
    {
        $this->binaryApiService = $binaryApiService;
    }

    /**
     * Get rows from a MikroTik menu.
     * Automatically selects the appropriate API based on router configuration.
     *
     * @param MikrotikRouter $router The router to query
     * @param string $menu The menu path (e.g., '/ip/pool', '/ppp/profile')
     * @param array $query Optional query filters
     *
     * @return array Array of rows from the router
     * @throws \Illuminate\Http\Client\ConnectionException When connection to router fails
     * @throws \Illuminate\Http\Client\RequestException When request times out or fails
     */
    public function getMktRows(MikrotikRouter $router, string $menu, array $query = []): array
    {
        $apiType = $this->determineApiType($router);

        if ($apiType === 'binary') {
            return $this->binaryApiService->getMktRows($router, $menu, $query);
        }

        // Use REST API
        return $this->getMktRowsRest($router, $menu, $query);
    }

    /**
     * Add rows to a MikroTik menu.
     *
     * @param MikrotikRouter $router The router to modify
     * @param string $menu The menu path
     * @param array $rows Array of rows to add
     *
     * @return array Result array with success status, counts, and error details
     */
    public function addMktRows(MikrotikRouter $router, string $menu, array $rows): array
    {
        $apiType = $this->determineApiType($router);

        if ($apiType === 'binary') {
            return $this->binaryApiService->addMktRows($router, $menu, $rows);
        }

        // Use REST API
        return $this->addMktRowsRest($router, $menu, $rows);
    }

    /**
     * Add rows to a MikroTik menu (backward compatible boolean return).
     *
     * @deprecated Use addMktRows() which returns detailed array results
     *
     * @param MikrotikRouter $router The router to modify
     * @param string $menu The menu path
     * @param array $rows Array of rows to add
     *
     * @return bool True if all rows were added successfully
     */
    public function addMktRowsBool(MikrotikRouter $router, string $menu, array $rows): bool
    {
        $result = $this->addMktRows($router, $menu, $rows);

        // Handle both array and bool responses for backward compatibility
        if (is_bool($result)) {
            return $result;
        }

        if (is_array($result) && array_key_exists('success', $result)) {
            return (bool) $result['success'];
        }

        // Unexpected result: treat as failure
        return false;
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
        $apiType = $this->determineApiType($router);

        if ($apiType === 'binary') {
            return $this->binaryApiService->editMktRow($router, $menu, $row, $data);
        }

        // Use REST API
        return $this->editMktRowRest($router, $menu, $row, $data);
    }

    /**
     * Remove rows from a MikroTik menu.
     *
     * @param MikrotikRouter $router The router to modify
     * @param string $menu The menu path
     * @param array $rows Array of rows to remove
     *
     * @return bool True if successful
     */
    public function removeMktRows(MikrotikRouter $router, string $menu, array $rows): bool
    {
        $apiType = $this->determineApiType($router);

        if ($apiType === 'binary') {
            return $this->binaryApiService->removeMktRows($router, $menu, $rows);
        }

        // Use REST API
        return $this->removeMktRowsRest($router, $menu, $rows);
    }

    /**
     * Execute a command on MikroTik router.
     *
     * @param MikrotikRouter $router The router to execute command on
     * @param string $command The command to execute
     * @param array $params Optional command parameters
     *
     * @return mixed Command output or null on failure
     */
    public function ttyWrite(MikrotikRouter $router, string $command, array $params = []): mixed
    {
        $apiType = $this->determineApiType($router);

        if ($apiType === 'binary') {
            return $this->binaryApiService->ttyWrite($router, $command, $params);
        }

        return $this->ttyWriteRest($router, $command, $params);
    }

    /**
     * Determine which API type to use for the router.
     * Uses in-memory cache to avoid repeated connection tests.
     *
     * @param MikrotikRouter $router Router instance
     * @return string 'binary' or 'rest'
     */
    private function determineApiType(MikrotikRouter $router): string
    {
        $apiType = $router->api_type ?? 'auto';

        if ($apiType === 'binary') {
            return 'binary';
        }

        if ($apiType === 'rest') {
            return 'rest';
        }

        // Check cache first to avoid repeated connection tests
        if (isset($this->apiTypeCache[$router->id])) {
            return $this->apiTypeCache[$router->id];
        }

        // Auto-detect: prefer binary API (more reliable and compatible)
        // Try binary API first, fall back to REST if it fails
        if ($this->binaryApiService->testConnection($router)) {
            Log::info('Auto-detected binary API for router', ['router_id' => $router->id]);
            $this->apiTypeCache[$router->id] = 'binary';
            return 'binary';
        }

        Log::info('Binary API failed, using REST API for router', ['router_id' => $router->id]);
        $this->apiTypeCache[$router->id] = 'rest';
        return 'rest';
    }

    /**
     * Get rows from a MikroTik menu using REST API.
     */
    private function getMktRowsRest(MikrotikRouter $router, string $menu, array $query = []): array
    {
        $maxRetries = config('services.mikrotik.max_retries', 3);
        $retryDelay = config('services.mikrotik.retry_delay', 1000); // milliseconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $endpoint = $this->menuToEndpoint($menu);
                $scheme = config('services.mikrotik.scheme', app()->environment('production') ? 'https' : 'http');
                $url = "{$scheme}://{$router->ip_address}:{$router->api_port}/api{$endpoint}";

                // Use Laravel HTTP client with reduced timeout to prevent gateway timeouts
                // Connection timeout of 5s to fail fast, total timeout of 30s to complete within gateway limits
                /** @var Response $response */
                $response = Http::withBasicAuth($router->username, (string) $router->password)
                    ->timeout((int) config('services.mikrotik.timeout', 30))
                    ->connectTimeout((int) config('services.mikrotik.connect_timeout', 5))
                    ->throw()
                    ->get($url, $query);

                if ($response->successful()) {
                    $data = $response->json();

                    // Validate response structure
                    if (!is_array($data)) {
                        Log::warning('Invalid response structure from MikroTik', [
                            'router_id' => $router->id,
                            'menu' => $menu,
                            'response_type' => gettype($data),
                        ]);
                        return [];
                    }

                    Log::info('Successfully fetched rows from MikroTik', [
                        'router_id' => $router->id,
                        'menu' => $menu,
                        'count' => count($data),
                        'attempt' => $attempt,
                    ]);

                    return $data;
                }

                Log::warning('Failed to fetch rows from MikroTik', [
                    'router_id' => $router->id,
                    'menu' => $menu,
                    'status' => $response->status(),
                    'attempt' => $attempt,
                ]);

                if ($attempt < $maxRetries) {
                    usleep($retryDelay * 1000);
                }
            } catch (\Exception $e) {
                Log::error('Error fetching rows from MikroTik', [
                    'router_id' => $router->id,
                    'menu' => $menu,
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                ]);

                if ($attempt >= $maxRetries) {
                    // Re-throw the exception on final attempt so it can be caught by controller
                    throw $e;
                }

                usleep($retryDelay * 1000);
            }
        }

        return [];
    }

    /**
     * Add rows to a MikroTik menu.
     *
     * @param MikrotikRouter $router The router to modify
     * @param string $menu The menu path
     * @param array $rows Array of rows to add
     *
     * @return array Result array with success status, counts, and error details
     */
    private function addMktRowsRest(MikrotikRouter $router, string $menu, array $rows): array
    {
        $results = [
            'success' => true,
            'total' => count($rows),
            'succeeded' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        try {
            $endpoint = $this->menuToEndpoint($menu);
            $scheme = config('services.mikrotik.scheme', app()->environment('production') ? 'https' : 'http');
            $url = "{$scheme}://{$router->ip_address}:{$router->api_port}/api{$endpoint}/add";

            foreach ($rows as $index => $row) {
                try {
                    /** @var Response $response */
                    $response = Http::withBasicAuth($router->username, (string) $router->password)
                        ->timeout((int) config('services.mikrotik.timeout', 30))
                        ->connectTimeout((int) config('services.mikrotik.connect_timeout', 5))
                        ->throw()
                        ->post($url, $row);

                    if ($response->successful()) {
                        $results['succeeded']++;
                        Log::info('Successfully added row to MikroTik', [
                            'router_id' => $router->id,
                            'menu' => $menu,
                            'row_index' => $index,
                            'row_data' => array_keys($row),
                        ]);
                    } else {
                        $results['failed']++;
                        $errorMsg = "HTTP {$response->status()}: " . ($response->body() ?: 'Unknown error');

                        // Sanitize row data before including in errors to prevent credential exposure
                        $sanitizedRow = $this->sanitizeRowData($row);

                        $results['errors'][] = [
                            'row_index' => $index,
                            'row_data' => $sanitizedRow,
                            'error' => $errorMsg,
                        ];

                        Log::warning('Failed to add row to MikroTik', [
                            'router_id' => $router->id,
                            'menu' => $menu,
                            'row_index' => $index,
                            'row_keys' => array_keys($row),
                            'status' => $response->status(),
                            'response_body' => $response->body(),
                        ]);
                    }
                } catch (\Exception $rowException) {
                    $results['failed']++;

                    // Sanitize row data before logging to prevent credential exposure
                    $sanitizedRow = $this->sanitizeRowData($row);

                    $results['errors'][] = [
                        'row_index' => $index,
                        'row_data' => $sanitizedRow,
                        'error' => $rowException->getMessage(),
                    ];

                    Log::error('Exception while adding row to MikroTik', [
                        'router_id' => $router->id,
                        'menu' => $menu,
                        'row_index' => $index,
                        'error' => $rowException->getMessage(),
                    ]);
                }
            }

            $results['success'] = $results['failed'] === 0;

            Log::info('Batch add operation to MikroTik completed', [
                'router_id' => $router->id,
                'menu' => $menu,
                'total' => $results['total'],
                'succeeded' => $results['succeeded'],
                'failed' => $results['failed'],
            ]);

            return $results;
        } catch (\Exception $e) {
            Log::error('Error in batch add operation to MikroTik', [
                'router_id' => $router->id,
                'menu' => $menu,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'total' => count($rows),
                'succeeded' => 0,
                'failed' => count($rows),
                'errors' => [['error' => $e->getMessage()]],
            ];
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
    private function editMktRowRest(MikrotikRouter $router, string $menu, array $row, array $data): bool
    {
        try {
            $endpoint = $this->menuToEndpoint($menu);
            $scheme = config('services.mikrotik.scheme', app()->environment('production') ? 'https' : 'http');
            $url = "{$scheme}://{$router->ip_address}:{$router->api_port}/api{$endpoint}/set";

            // Merge row identifier with new data
            $payload = array_merge($row, $data);

            /** @var Response $response */
            $response = Http::withBasicAuth($router->username, (string) $router->password)
                ->timeout((int) config('services.mikrotik.timeout', 30))
                ->connectTimeout((int) config('services.mikrotik.connect_timeout', 5))
                ->throw()
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
    private function removeMktRowsRest(MikrotikRouter $router, string $menu, array $rows): bool
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

                /** @var Response $response */
                $response = Http::withBasicAuth($router->username, (string) $router->password)
                    ->timeout((int) config('services.mikrotik.timeout', 30))
                    ->connectTimeout((int) config('services.mikrotik.connect_timeout', 5))
                    ->throw()
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
    private function ttyWriteRest(MikrotikRouter $router, string $command, array $params = []): mixed
    {
        try {
            $scheme = config('services.mikrotik.scheme', app()->environment('production') ? 'https' : 'http');
            $url = "{$scheme}://{$router->ip_address}:{$router->api_port}/api/terminal";

            /** @var Response $response */
            $response = Http::withBasicAuth($router->username, (string) $router->password)
                ->timeout((int) config('services.mikrotik.timeout', 30))
                ->connectTimeout((int) config('services.mikrotik.connect_timeout', 5))
                ->throw()
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

    /**
     * Sanitize row data to remove sensitive fields before logging.
     *
     * @param array $row Row data to sanitize
     *
     * @return array Sanitized row data
     */
    private function sanitizeRowData(array $row): array
    {
        $sensitiveFields = ['password', 'secret', 'snmp-community', 'community', 'private-key'];
        $sanitized = $row;

        foreach ($sanitized as $key => &$value) {
            // Normalize key by converting underscores to hyphens for comparison
            $normalizedKey = str_replace('_', '-', (string) $key);

            if (in_array($normalizedKey, $sensitiveFields, true)) {
                $value = '***REDACTED***';
            }
        }
        unset($value);

        return $sanitized;
    }
}
