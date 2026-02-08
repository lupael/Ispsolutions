<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MigrateNetworkUsers extends Command
{
    protected $signature = 'migrate:network-users {--chunk=500}';

    protected $description = 'Migrate orphaned rows from network_users into users table and link them (idempotent)';

    public function handle(): int
    {
        if (! Schema::hasTable('network_users')) {
            $this->info('network_users table does not exist — nothing to migrate.');
            return 0;
        }

        if (! Schema::hasTable('users')) {
            $this->error('users table does not exist — aborting.');
            return 1;
        }

        $chunkSize = (int) $this->option('chunk') ?: 500;

        $total = DB::table('network_users')->whereNull('user_id')->count();
        $this->info("Found {$total} network_users rows with no linked user. Processing in chunks of {$chunkSize}...");

        $processed = 0;

        DB::table('network_users')
            ->whereNull('user_id')
            ->orderBy('id')
            ->chunkById($chunkSize, function ($networkUsers) use (&$processed) {
                foreach ($networkUsers as $nu) {
                    DB::transaction(function () use ($nu, &$processed) {
                        // If a user already exists with this legacy id, skip
                        if (Schema::hasColumn('users', 'legacy_network_user_id')) {
                            $exists = DB::table('users')->where('legacy_network_user_id', $nu->id)->first();
                            if ($exists) {
                                // Link back if network_users.user_id is empty
                                if (empty($nu->user_id)) {
                                    DB::table('network_users')->where('id', $nu->id)->update(['user_id' => $exists->id]);
                                }
                                $processed++;
                                return;
                            }
                        }

                        // Build user payload based on existing users columns and available network_user fields
                        $now = now();
                        $payload = [];

                        if (Schema::hasColumn('users', 'name')) {
                            $payload['name'] = $nu->username ?? ($nu->name ?? 'network_user_'.$nu->id);
                        }

                        if (Schema::hasColumn('users', 'email') && isset($nu->email) && $nu->email) {
                            $payload['email'] = $nu->email;
                        }

                        if (Schema::hasColumn('users', 'username') && isset($nu->username)) {
                            $payload['username'] = $nu->username;
                        }

                        if (Schema::hasColumn('users', 'password')) {
                            // If network_users stores a password, preserve it; otherwise create a random one
                            $payload['password'] = isset($nu->password) && $nu->password ? $nu->password : Hash::make(Str::random(40));
                        }

                        if (Schema::hasColumn('users', 'tenant_id') && isset($nu->tenant_id)) {
                            $payload['tenant_id'] = $nu->tenant_id;
                        }

                        if (Schema::hasColumn('users', 'zone_id') && isset($nu->zone_id)) {
                            $payload['zone_id'] = $nu->zone_id;
                        }

                        if (Schema::hasColumn('users', 'operator_level')) {
                            // Customers are operator-level 100 by convention in this project
                            $payload['operator_level'] = 100;
                        }

                        // Map common network user fields into users where available
                        $mapFields = ['service_type','connection_type','billing_type','device_type','mac_address','ip_address','status','expiry_date','radius_password'];
                        foreach ($mapFields as $field) {
                            if (Schema::hasColumn('users', $field) && property_exists($nu, $field)) {
                                $payload[$field] = $nu->$field;
                            }
                        }

                        // Ensure we can record the original network_users id
                        if (Schema::hasColumn('users', 'legacy_network_user_id')) {
                            $payload['legacy_network_user_id'] = $nu->id;
                        }

                        $payload['created_at'] = $nu->created_at ?? $now;
                        $payload['updated_at'] = $nu->updated_at ?? $now;

                        // Insert and link back
                        $newUserId = DB::table('users')->insertGetId($payload);

                        DB::table('network_users')->where('id', $nu->id)->update(['user_id' => $newUserId]);

                        $processed++;
                    });
                }
            }, $column = 'id');

        $this->info("Completed. Processed {$processed} records.");
        return 0;
    }
}
