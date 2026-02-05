<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Part 1: Add columns to the users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('email');
            $table->enum('service_type', ['pppoe', 'hotspot', 'static'])->default('pppoe')->after('username');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('service_type');
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('set null')->after('status');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null')->after('tenant_id');
            $table->date('expiry_date')->nullable()->after('zone_id');
            $table->string('connection_type')->nullable()->after('expiry_date');
            $table->string('billing_type')->nullable()->after('connection_type');
            $table->string('device_type')->nullable()->after('billing_type');
            $table->string('mac_address')->nullable()->after('device_type');
            $table->string('ip_address')->nullable()->after('mac_address');
            $table->boolean('is_subscriber')->default(false)->after('ip_address');
            $table->string('customer_id')->unique()->nullable()->after('is_subscriber');
        });

        // Part 2: Migrate data from customers to users
        if (Schema::hasTable('customers')) {
            DB::table('customers')->orderBy('id')->chunk(100, function ($customers) {
                foreach ($customers as $customer) {
                    $userData = [
                        'username' => $customer->username,
                        'password' => $customer->password,
                        'service_type' => $customer->service_type,
                        'status' => $customer->status,
                        'tenant_id' => $customer->tenant_id ?? null,
                        'zone_id' => $customer->zone_id ?? null,
                        'expiry_date' => $customer->expiry_date ?? null,
                        'connection_type' => $customer->connection_type ?? null,
                        'billing_type' => $customer->billing_type ?? null,
                        'device_type' => $customer->device_type ?? null,
                        'mac_address' => $customer->mac_address ?? null,
                        'ip_address' => $customer->ip_address ?? null,
                        'service_package_id' => $customer->package_id,
                        'is_subscriber' => true,
                        'operator_level' => null,
                        'customer_id' => $customer->id, // Use old ID as new customer_id
                        'created_at' => $customer->created_at,
                        'updated_at' => $customer->updated_at,
                    ];

                    if ($customer->user_id) {
                        // If a user_id exists, update the existing user
                        DB::table('users')->where('id', $customer->user_id)->update($userData);
                    } else {
                        // Otherwise, create a new user
                        $userData['name'] = $customer->username;
                        $userData['email'] = $customer->username . '@example.com'; // Create a dummy email
                        DB::table('users')->insert($userData);
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'service_type',
                'status',
                'tenant_id',
                'zone_id',
                'expiry_date',
                'connection_type',
                'billing_type',
                'device_type',
                'mac_address',
                'ip_address',
                'is_subscriber',
                'customer_id',
            ]);
        });
    }
};