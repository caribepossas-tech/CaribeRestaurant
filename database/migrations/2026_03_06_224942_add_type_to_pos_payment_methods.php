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
        if (!Schema::hasColumn('pos_payment_methods', 'type')) {
            Schema::table('pos_payment_methods', function (Blueprint $table) {
                $table->string('type')->default('pos')->after('restaurant_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_payment_methods', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
