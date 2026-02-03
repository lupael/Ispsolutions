<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Role::where('slug', 'admin')->update(['name' => 'ISP', 'slug' => 'isp']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::where('slug', 'isp')->update(['name' => 'Admin', 'slug' => 'admin']);
    }
};
