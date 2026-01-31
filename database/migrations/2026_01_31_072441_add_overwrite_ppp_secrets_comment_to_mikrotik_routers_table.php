<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mikrotik_routers', function (Blueprint $table) {
            // Add overwrite PPP secrets comment field (yes/no dropdown)
            if (! Schema::hasColumn('mikrotik_routers', 'overwrite_ppp_secrets_comment')) {
                $table->enum('overwrite_ppp_secrets_comment', ['yes', 'no'])->default('yes')->after('password');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mikrotik_routers', function (Blueprint $table) {
            if (Schema::hasColumn('mikrotik_routers', 'overwrite_ppp_secrets_comment')) {
                $table->dropColumn('overwrite_ppp_secrets_comment');
            }
        });
    }
};
