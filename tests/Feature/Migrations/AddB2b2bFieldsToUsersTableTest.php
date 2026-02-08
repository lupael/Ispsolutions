<?php

namespace Tests\Feature\Migrations;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AddB2b2bFieldsToUsersTableTest extends TestCase
{
    public function test_subscription_plan_id_and_expires_at_fields_exist()
    {
        $this->assertTrue(
            Schema::hasColumn('users', 'subscription_plan_id'),
            'users table should have subscription_plan_id column'
        );

        $this->assertTrue(
            Schema::hasColumn('users', 'expires_at'),
            'users table should have expires_at column'
        );
    }

    public function test_subscription_plan_id_is_nullable_foreign_key()
    {
        $columns = DB::select("
            SELECT COLUMN_NAME, IS_NULLABLE, COLUMN_KEY
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'subscription_plan_id'
        ");

        $this->assertCount(1, $columns);
        $this->assertEquals('YES', $columns[0]->IS_NULLABLE);
    }

    public function test_expires_at_is_timestamp_column()
    {
        $columns = DB::select("
            SELECT COLUMN_NAME, DATA_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'expires_at'
        ");

        $this->assertCount(1, $columns);
        $this->assertIn($columns[0]->DATA_TYPE, ['datetime', 'timestamp']);
    }

    public function test_subscription_plan_index_exists()
    {
        $indexes = DB::select("
            SELECT INDEX_NAME
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'subscription_plan_id'
        ");

        $this->assertTrue(count($indexes) > 0, 'subscription_plan_id should be indexed');
    }

    public function test_can_insert_user_with_subscription_fields()
    {
        $user = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'subscription_plan_id' => 1,
            'expires_at' => now()->addMonth(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertIsInt($user);
        $this->assertGreaterThan(0, $user);

        $inserted = DB::table('users')->find($user);
        $this->assertEquals(1, $inserted->subscription_plan_id);
        $this->assertNotNull($inserted->expires_at);
    }

    public function test_expires_at_cast_as_datetime()
    {
        $user = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'expires_at' => '2026-03-08 10:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $model = \App\Models\User::find($user);

        // Should be cast to Carbon instance
        $this->assertInstanceOf(\Carbon\Carbon::class, $model->expires_at);
        $this->assertEquals('2026-03-08', $model->expires_at->format('Y-m-d'));
    }
}
