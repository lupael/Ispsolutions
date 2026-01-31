<?php

declare(strict_types=1);

namespace App\Services;

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;
use Illuminate\Support\Facades\Log;

/**
 * RouterosAPI - Wrapper around evilfreelancer/routeros-api-php
 * 
 * This class provides a simplified interface following the IspBills pattern
 * for interacting with MikroTik RouterOS API.
 * 
 * Pattern Reference: IspBills ISP Billing System
 */
class RouterosAPI
{
    private ?Client $client = null;
    private array $config;
    private bool $connected = false;

    /**
     * Constructor
     * 
     * @param array $config Configuration array with keys: host, user, pass, port, attempts, debug, ssl
     */
    public function __construct(array $config)
    {
        $this->config = [
            'host' => $config['host'] ?? '',
            'user' => $config['user'] ?? 'admin',
            'pass' => $config['pass'] ?? '',
            'port' => $config['port'] ?? 8728,
            'ssl' => $config['ssl'] ?? false,  // Enable SSL/TLS support
            'attempts' => $config['attempts'] ?? 3,
            'timeout' => $config['timeout'] ?? 5,
            'debug' => $config['debug'] ?? false,
        ];
    }

    /**
     * Connect to MikroTik router
     * 
     * @param string|null $host Optional host override
     * @param string|null $user Optional user override
     * @param string|null $pass Optional password override
     * @return bool True if connected successfully
     */
    public function connect(?string $host = null, ?string $user = null, ?string $pass = null): bool
    {
        try {
            $config = new Config([
                'host' => $host ?? $this->config['host'],
                'user' => $user ?? $this->config['user'],
                'pass' => $pass ?? $this->config['pass'],
                'port' => (int) $this->config['port'],
                'ssl' => (bool) $this->config['ssl'],  // Pass SSL configuration
                'timeout' => (int) $this->config['timeout'],
                'attempts' => (int) $this->config['attempts'],
            ]);

            $this->client = new Client($config);
            $this->connected = true;

            if ($this->config['debug']) {
                Log::info('RouterosAPI: Connected successfully', [
                    'host' => $config->get('host'),
                    'port' => $config->get('port'),
                ]);
            }

            return true;
        } catch (ConnectException | ClientException | ConfigException $e) {
            $this->connected = false;
            
            Log::error('RouterosAPI: Connection failed', [
                'host' => $host ?? $this->config['host'],
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Get rows from a MikroTik menu
     * 
     * @param string $menu Menu path (e.g., 'ip_pool', 'ppp_profile', 'ppp_secret')
     * @param array $query Optional query filters (e.g., ['name' => 'default'])
     * @return array Array of rows
     */
    public function getMktRows(string $menu, array $query = []): array
    {
        if (!$this->connected || !$this->client) {
            Log::error('RouterosAPI: Not connected');
            return [];
        }

        try {
            $endpoint = $this->menuToEndpoint($menu);
            
            // Build query
            $queryObj = $this->client->query($endpoint . '/print');
            
            // Add filters
            foreach ($query as $key => $value) {
                $queryObj->where($key, $value);
            }
            
            // Execute and read response
            $response = $this->client->write($queryObj)->read();
            
            if ($this->config['debug']) {
                Log::info('RouterosAPI: getMktRows', [
                    'menu' => $menu,
                    'query' => $query,
                    'count' => count($response),
                ]);
            }
            
            return $response;
        } catch (QueryException | ClientException $e) {
            Log::error('RouterosAPI: getMktRows failed', [
                'menu' => $menu,
                'error' => $e->getMessage(),
            ]);
            
            return [];
        }
    }

    /**
     * Add rows to a MikroTik menu
     * 
     * @param string $menu Menu path
     * @param array $rows Array of rows to add
     * @return bool True if all rows added successfully
     */
    public function addMktRows(string $menu, array $rows): bool
    {
        if (!$this->connected || !$this->client) {
            Log::error('RouterosAPI: Not connected');
            return false;
        }

        try {
            $endpoint = $this->menuToEndpoint($menu);
            $allSuccess = true;

            foreach ($rows as $row) {
                try {
                    $query = new Query($endpoint . '/add');
                    
                    // Add attributes
                    foreach ($row as $key => $value) {
                        $query->equal($key, $value);
                    }
                    
                    $this->client->write($query)->read();
                    
                    if ($this->config['debug']) {
                        Log::info('RouterosAPI: Added row', [
                            'menu' => $menu,
                            'row_keys' => array_keys($row),
                        ]);
                    }
                } catch (QueryException | ClientException $e) {
                    $allSuccess = false;
                    
                    Log::error('RouterosAPI: Failed to add row', [
                        'menu' => $menu,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $allSuccess;
        } catch (\Exception $e) {
            Log::error('RouterosAPI: addMktRows failed', [
                'menu' => $menu,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Edit a row in a MikroTik menu
     * 
     * @param string $menu Menu path
     * @param array $row Row identifier (must contain .id or unique identifier)
     * @param array $data New data for the row
     * @return bool True if successful
     */
    public function editMktRow(string $menu, array $row, array $data): bool
    {
        if (!$this->connected || !$this->client) {
            Log::error('RouterosAPI: Not connected');
            return false;
        }

        try {
            $endpoint = $this->menuToEndpoint($menu);
            
            // Build set query
            $query = new Query($endpoint . '/set');
            
            // Add identifier
            if (isset($row['.id'])) {
                $query->equal('.id', $row['.id']);
            } else {
                // Try to find by other unique fields
                foreach ($row as $key => $value) {
                    $query->equal($key, $value);
                }
            }
            
            // Add new data
            foreach ($data as $key => $value) {
                $query->equal($key, $value);
            }
            
            $this->client->write($query)->read();
            
            if ($this->config['debug']) {
                Log::info('RouterosAPI: Edited row', [
                    'menu' => $menu,
                    'row_keys' => array_keys($row),
                    'data_keys' => array_keys($data),
                ]);
            }
            
            return true;
        } catch (QueryException | ClientException $e) {
            Log::error('RouterosAPI: editMktRow failed', [
                'menu' => $menu,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Remove rows from a MikroTik menu
     * 
     * @param string $menu Menu path
     * @param array $rows Array of rows to remove (each must contain .id or unique identifier)
     * @return bool True if all rows removed successfully
     */
    public function removeMktRows(string $menu, array $rows): bool
    {
        if (!$this->connected || !$this->client) {
            Log::error('RouterosAPI: Not connected');
            return false;
        }

        try {
            $endpoint = $this->menuToEndpoint($menu);
            $allSuccess = true;

            foreach ($rows as $row) {
                try {
                    $query = new Query($endpoint . '/remove');
                    
                    // Add identifier
                    if (isset($row['.id'])) {
                        $query->equal('.id', $row['.id']);
                    } else {
                        // Try to find by other unique fields
                        foreach ($row as $key => $value) {
                            $query->equal($key, $value);
                        }
                    }
                    
                    $this->client->write($query)->read();
                    
                    if ($this->config['debug']) {
                        Log::info('RouterosAPI: Removed row', [
                            'menu' => $menu,
                            'row_keys' => array_keys($row),
                        ]);
                    }
                } catch (QueryException | ClientException $e) {
                    $allSuccess = false;
                    
                    Log::error('RouterosAPI: Failed to remove row', [
                        'menu' => $menu,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $allSuccess;
        } catch (\Exception $e) {
            Log::error('RouterosAPI: removeMktRows failed', [
                'menu' => $menu,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Execute a command on MikroTik router (tty write)
     * 
     * @param string $command Command path (e.g., '/ppp/aaa/set', '/ppp/secret/export')
     * @param array $params Command parameters
     * @return mixed Command response or null on failure
     */
    public function ttyWrite(string $command, array $params = []): mixed
    {
        if (!$this->connected || !$this->client) {
            Log::error('RouterosAPI: Not connected');
            return null;
        }

        try {
            $query = new Query($command);
            
            // Add parameters
            foreach ($params as $key => $value) {
                $query->equal($key, $value);
            }
            
            $response = $this->client->write($query)->read();
            
            if ($this->config['debug']) {
                Log::info('RouterosAPI: ttyWrite executed', [
                    'command' => $command,
                    'params' => array_keys($params),
                ]);
            }
            
            return $response;
        } catch (QueryException | ClientException $e) {
            Log::error('RouterosAPI: ttyWrite failed', [
                'command' => $command,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    /**
     * Disconnect from router
     */
    public function disconnect(): void
    {
        if ($this->client) {
            $this->client = null;
            $this->connected = false;
            
            if ($this->config['debug']) {
                Log::info('RouterosAPI: Disconnected');
            }
        }
    }

    /**
     * Check if connected
     * 
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connected && $this->client !== null;
    }

    /**
     * Convert menu name to endpoint path
     * 
     * Examples:
     *  - 'ip_pool' => '/ip/pool'
     *  - 'ppp_profile' => '/ppp/profile'
     *  - 'ppp_secret' => '/ppp/secret'
     *  - 'radius' => '/radius'
     *  - 'tool_netwatch' => '/tool/netwatch'
     * 
     * @param string $menu Menu name with underscores
     * @return string Endpoint path
     */
    private function menuToEndpoint(string $menu): string
    {
        // If already starts with /, return as is
        if (str_starts_with($menu, '/')) {
            return $menu;
        }

        // Replace underscores with slashes and add leading slash
        return '/' . str_replace('_', '/', $menu);
    }
}
