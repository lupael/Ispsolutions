<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'radius';

    public function up(): void
    {
        Schema::connection('radius')->create('radcheck', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64)->index();
            $table->string('attribute', 64);
            $table->string('op', 2)->default('==');
            $table->string('value', 253);

            $table->index(['username', 'attribute']);
        });
    }

    public function down(): void
    {
        Schema::connection('radius')->dropIfExists('radcheck');
    }
};
