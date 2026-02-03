<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MikrotikRouter;
use Illuminate\Support\Facades\Log;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

/**
 * RouterOS Binary API Adapter
 * 
 * Provides an adapter for the RouterOS binary API protocol (port 8728)
 * Compatible with RouterOS v6 and v7
 */
class RouterOSBinaryApiService
{
    /**
     * Get rows from a MikroTik menu using binary API.
     *
     * @param MikrotikRouter $router The router to query
     * @param string $menu The menu path (e.g., '/ip/pool', '/ppp/profile')
     * @param array $query Optional query filters
     *
     * @return array Array of rows from the router
     * @throws \Exception When connection or query fails
     */
    public function getMktRows(MikrotikRouter $router, string $menu, array $query = []): array
    {
        try {
            $client = $this->connect($router);
            
            // Build query
            $routerQuery = new Query($menu . '/print');
            
            // Add filters if provided
            foreach ($query as $key => $value) {
                $routerQuery->where($key, $value);
            }
            
            // Execute query
            $response = $client->query($routerQuery)->read();
            
            Log::info('Successfully fetched rows from MikroTik via binary API', [
                'router_id' => $router->id,
                'menu' => $menu,
                'count' => count($response),
            ]);
            
            return $this->normalizeResponse($response);
            
        } catch (\Exception $e) {
            Log::error('Error fetching rows from MikroTik via binary API', [
                'router_id' => $router->id,
                'menu' => $menu,
                'error' => $e->getMessage(),
            ]);
            
            // Re-throw the exception so it can be handled by the controller
            throw $e;
        }
    }

    /**
     * Add rows to a MikroTik menu using binary API.
     *
     * @param MikrotikRouter $router The router to modify
     * @param string $menu The menu path
     * @param array $rows Array of rows to add
     *
     * @return array Result array with success status and details
     */
    public function addMktRows(MikrotikRouter $router, string $menu, array $rows): array
    {
        $results = [
            'success' => true,
            'total' => count($rows),
            'succeeded' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        try {
            $client = $this->connect($router);
            
            foreach ($rows as $index => $row) {
                try {
                    $query = new Query($menu . '/add');
                    
                    // Add parameters
                    foreach ($row as $key => $value) {
                        // Convert underscores to hyphens for RouterOS format
                        $rosKey = str_replace('_', '-', $key);
                        $query->equal($rosKey, $value);
                    }
                    
                    $client->query($query)->read();
                    $results['succeeded']++;
                    
                    Log::info('Successfully added row to MikroTik via binary API', [
                        'router_id' => $router->id,
                        'menu' => $menu,
                        'row_index' => $index,
                    ]);
                    
                } catch (\Exception $rowException) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row_index' => $index,
                        'row_data' => $this->sanitizeRowData($row),
                        'error' => $rowException->getMessage(),
                    ];
                    
                    Log::error('Exception while adding row to MikroTik via binary API', [
                        'router_id' => $router->id,
                        'menu' => $menu,
                        'row_index' => $index,
                        'error' => $rowException->getMessage(),
                    ]);
                }
            }
            
            $results['success'] = $results['failed'] === 0;
            
            Log::info('Batch add operation to MikroTik via binary API completed', [
                'router_id' => $router->id,
                'menu' => $menu,
                'total' => $results['total'],
                'succeeded' => $results['succeeded'],
                'failed' => $results['failed'],
            ]);
            
            return $results;
            
        } catch (\Exception $e) {
            Log::error('Error in batch add operation to MikroTik via binary API', [
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
     * Edit a row in a MikroTik menu using binary API.
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
            $client = $this->connect($router);
            
            $query = new Query($menu . '/set');
            
            // Add row identifier (usually .id)
            foreach ($row as $key => $value) {
                $rosKey = str_replace('_', '-', $key);
                $query->equal($rosKey, $value);
            }
            
            // Add new data
            foreach ($data as $key => $value) {
                $rosKey = str_replace('_', '-', $key);
                $query->equal($rosKey, $value);
            }
            
            $client->query($query)->read();
            
            Log::info('Successfully edited row on MikroTik via binary API', [
                'router_id' => $router->id,
                'menu' => $menu,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error editing row on MikroTik via binary API', [
                'router_id' => $router->id,
                'menu' => $menu,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Remove rows from a MikroTik menu using binary API.
     *
     * @param MikrotikRouter $router The router to modify
     * @param string $menu The menu path
     * @param array $rows Array of rows to remove
     *
     * @return bool True if successful
     */
    public function removeMktRows(MikrotikRouter $router, string $menu, array $rows): bool
    {
        try {
            $client = $this->connect($router);
            
            foreach ($rows as $row) {
                $query = new Query($menu . '/remove');
                
                foreach ($row as $key => $value) {
                    $rosKey = str_replace('_', '-', $key);
                    $query->equal($rosKey, $value);
                }
                
                $client->query($query)->read();
            }
            
            Log::info('Successfully removed rows from MikroTik via binary API', [
                'router_id' => $router->id,
                'menu' => $menu,
                'count' => count($rows),
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error removing rows from MikroTik via binary API', [
                'router_id' => $router->id,
                'menu' => $menu,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Execute a TTY-style command (e.g. /ppp/aaa/set, /system/backup/save) via binary API.
     * Used for RouterOS v6/v7 provisioning when REST terminal is not available.
     *
     * @param MikrotikRouter $router The router to run the command on
     * @param string $command Command path (e.g. '/ppp/aaa/set', '/radius/incoming/set')
     * @param array $params Key-value parameters (e.g. ['use-radius' => 'yes', 'interim-update' => '5m'])
     *
     * @return mixed Response array from router or null on failure
     */
    public function ttyWrite(MikrotikRouter $router, string $command, array $params = []): mixed
    {
        try {
            $client = $this->connect($router);

            $command = trim($command, '/');
            $query = new Query('/' . $command);

            foreach ($params as $key => $value) {
                $rosKey = str_replace('_', '-', (string) $key);
                $query->equal($rosKey, (string) $value);
            }

            $response = $client->query($query)->read();

            Log::info('Binary API ttyWrite executed', [
                'router_id' => $router->id,
                'command' => $command,
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Binary API ttyWrite failed', [
                'router_id' => $router->id,
                'command' => $command ?? '',
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Test connection to router using binary API.
     *
     * @param MikrotikRouter $router The router to test
     *
     * @return bool True if connection successful
     */
    public function testConnection(MikrotikRouter $router): bool
    {
        try {
            $client = $this->connect($router);
            
            // Try a simple query to test connection
            $query = new Query('/system/identity/print');
            $client->query($query)->read();
            
            return true;
            
        } catch (\Exception $e) {
            Log::warning('Binary API connection test failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Connect to MikroTik router using binary API.
     *
     * @param MikrotikRouter $router Router instance
     *
     * @return Client Connected client
     */
    private function connect(MikrotikRouter $router): Client
    {
        $config = (new Config())
            ->set('host', $router->ip_address)
            ->set('user', $router->username)
            ->set('pass', $router->password)
            ->set('port', $router->api_port)
            ->set('timeout', config('services.mikrotik.timeout', 30));
        
        return new Client($config);
    }

    /**
     * Normalize response from binary API to match REST API format.
     *
     * @param array $response Raw response from binary API
     *
     * @return array Normalized response
     */
    private function normalizeResponse(array $response): array
    {
        return array_map(function ($item) {
            // Binary API returns arrays with dot-prefixed keys (.id, etc.)
            // Convert to match REST API format
            $normalized = [];
            
            foreach ($item as $key => $value) {
                // Keep original key
                $normalized[$key] = $value;
                
                // Also add hyphenated version for compatibility
                if (strpos($key, '.') === 0) {
                    // Skip dot-prefixed keys like .id
                    continue;
                }
                
                // Convert hyphen to underscore if not already present
                $underscoreKey = str_replace('-', '_', $key);
                if ($underscoreKey !== $key) {
                    $normalized[$underscoreKey] = $value;
                }
            }
            
            return $normalized;
        }, $response);
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
        
        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                $sanitized[$field] = '***REDACTED***';
            }
            
            // Also check underscore version
            $underscoreField = str_replace('-', '_', $field);
            if (isset($sanitized[$underscoreField])) {
                $sanitized[$underscoreField] = '***REDACTED***';
            }
        }
        
        return $sanitized;
    }
}
