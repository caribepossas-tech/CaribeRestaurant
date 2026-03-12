<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('restaurants', 'stock_check_mode')) {
            Schema::table('restaurants', function (Blueprint $table) {
                $table->enum('stock_check_mode', ['strict', 'flexible'])->default('flexible');
            });
        }
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('stock_check_mode');
        });
    }
};
