<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop legacy_status and old_role_id from users if present
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'legacy_status')) {
                $table->dropColumn('legacy_status');
            }

            if (Schema::hasColumn('users', 'old_role_id')) {
                // drop foreign if exists
                try {
                    $table->dropForeign(['old_role_id']);
                } catch (\Throwable $e) {
                    // ignore
                }

                $table->dropColumn('old_role_id');
            }
        });

        // Drop legacy columns from roles table if present
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'old_role_id')) {
                $table->dropColumn('old_role_id');
            }
            if (Schema::hasColumn('roles', 'legacy_status')) {
                $table->dropColumn('legacy_status');
            }
        });

        // Optionally remove legacy role slugs if present (reseller, sub-reseller)
        try {
            DB::table('roles')->whereIn('slug', ['reseller', 'sub-reseller', 'legacy_role'])->delete();
        } catch (\Throwable $e) {
            // ignore if roles table doesn't exist yet
        }
    }

    public function down(): void
    {
        // No-op: can't reliably restore dropped columns/data
    }
};
