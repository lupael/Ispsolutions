<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\RoleLabelSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        RoleLabelSetting::where('role_slug', 'admin')->update(['role_slug' => 'isp']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        RoleLabelSetting::where('role_slug', 'isp')->update(['role_slug' => 'admin']);
    }
};
