<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'radius';

    public function up(): void
    {
        Schema::connection('radius')->create('radreply', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64);
            $table->string('attribute', 64);
            $table->enum('op', ['=', ':=', '==', '+=', '!=', '>', '>=', '<', '<=', '=~', '!~', '=*', '!*'])->default(':=');
            $table->string('value', 253);
            $table->timestamps();

            $table->index('username');
        });
    }

    public function down(): void
    {
        Schema::connection('radius')->dropIfExists('radreply');
    }
};
