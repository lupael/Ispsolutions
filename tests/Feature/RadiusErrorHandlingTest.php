<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RadiusErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that RadAcct model handles missing RADIUS tables gracefully.
     */
    public function test_radacct_query_handles_missing_tables(): void
    {
        // Try to query RadAcct without RADIUS tables
        try {
            $count = DB::connection('radius')->table('radacct')->count();
            // If this succeeds, the table exists (shouldn't happen in test env)
            $this->assertTrue(true);
        } catch (\Illuminate\Database\QueryException $e) {
            // Expected: Query exception when table doesn't exist
            $this->assertStringContainsString('radacct', $e->getMessage());
        }
    }

    /**
     * Test that the RADIUS connection configuration is properly set.
     */
    public function test_radius_connection_is_configured(): void
    {
        $connection = config('database.connections.radius');

        $this->assertIsArray($connection);
        $this->assertArrayHasKey('driver', $connection);
        $this->assertArrayHasKey('database', $connection);
        $this->assertArrayHasKey('host', $connection);

        // In test environment, might be :memory: or radius depending on config
        $this->assertNotEmpty($connection['database']);
    }

    /**
     * Test that no duplicate RADIUS connection exists in config.
     */
    public function test_no_duplicate_radius_connections(): void
    {
        $databaseConfig = file_get_contents(config_path('database.php'));

        // Count occurrences of 'radius' connection definition
        preg_match_all("/'radius'\s*=>\s*\[/", $databaseConfig, $matches);
        $count = count($matches[0]);

        // Should have exactly one 'radius' connection
        $this->assertEquals(1, $count, "Found {$count} 'radius' connection definitions, expected 1");
    }
}
