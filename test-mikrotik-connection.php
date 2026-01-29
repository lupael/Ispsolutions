#!/usr/bin/env php
<?php

/**
 * Test script to connect to Mikrotik router and fetch PPP profiles
 * This script tests the connection to 103.138.147.185:8777 with provided credentials
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "========================================\n";
echo "Mikrotik Connection Test\n";
echo "========================================\n\n";

// Test connection parameters
$host = '103.138.147.185';
$port = 8777;
$username = 'ispsolution1213';
$password = 'ispsolution1213';

echo "Testing connection to Mikrotik:\n";
echo "  Host: {$host}\n";
echo "  Port: {$port}\n";
echo "  Username: {$username}\n";
echo "  Timeout: 60 seconds (increased from default 30)\n\n";

// Test 1: Basic connectivity check
echo "Test 1: Checking if router is reachable...\n";
$startTime = microtime(true);

try {
    $response = Http::timeout(60)
        ->withBasicAuth($username, $password)
        ->get("http://{$host}:{$port}/api");
    
    $elapsed = round((microtime(true) - $startTime) * 1000, 2);
    
    if ($response->successful()) {
        echo "✓ Router is reachable! (Response time: {$elapsed}ms)\n";
        echo "  Status: {$response->status()}\n";
        echo "  Response: " . substr($response->body(), 0, 200) . "...\n\n";
    } else {
        echo "✗ Router responded with error status: {$response->status()}\n";
        echo "  Response: {$response->body()}\n\n";
    }
} catch (\Exception $e) {
    $elapsed = round((microtime(true) - $startTime) * 1000, 2);
    echo "✗ Connection failed after {$elapsed}ms\n";
    echo "  Error: {$e->getMessage()}\n\n";
}

// Test 2: Fetch PPP Profiles
echo "Test 2: Fetching PPP Profiles from /ppp/profile...\n";
$startTime = microtime(true);

try {
    $response = Http::timeout(60)
        ->withBasicAuth($username, $password)
        ->get("http://{$host}:{$port}/api/ppp/profile");
    
    $elapsed = round((microtime(true) - $startTime) * 1000, 2);
    
    if ($response->successful()) {
        $profiles = $response->json();
        $count = is_array($profiles) ? count($profiles) : 0;
        
        echo "✓ Successfully fetched PPP profiles! (Response time: {$elapsed}ms)\n";
        echo "  Total profiles: {$count}\n\n";
        
        if ($count > 0) {
            echo "Sample profile (first one):\n";
            print_r(array_slice($profiles, 0, 1));
            echo "\n";
            
            echo "Available profile names:\n";
            foreach (array_slice($profiles, 0, 5) as $profile) {
                $name = $profile['name'] ?? 'unnamed';
                $localAddr = $profile['local-address'] ?? 'N/A';
                $remoteAddr = $profile['remote-address'] ?? 'N/A';
                echo "  - {$name} (local: {$localAddr}, remote: {$remoteAddr})\n";
            }
            if ($count > 5) {
                echo "  ... and " . ($count - 5) . " more profiles\n";
            }
        }
    } else {
        echo "✗ Failed to fetch profiles\n";
        echo "  Status: {$response->status()}\n";
        echo "  Response: {$response->body()}\n";
    }
} catch (\Illuminate\Http\Client\ConnectionException $e) {
    $elapsed = round((microtime(true) - $startTime) * 1000, 2);
    echo "✗ Connection timeout or network error after {$elapsed}ms\n";
    echo "  Error: {$e->getMessage()}\n";
    echo "  Suggestion: The router may be unreachable or behind a firewall.\n";
} catch (\Exception $e) {
    $elapsed = round((microtime(true) - $startTime) * 1000, 2);
    echo "✗ Request failed after {$elapsed}ms\n";
    echo "  Error: {$e->getMessage()}\n";
}

echo "\n========================================\n";
echo "Test Complete\n";
echo "========================================\n";
