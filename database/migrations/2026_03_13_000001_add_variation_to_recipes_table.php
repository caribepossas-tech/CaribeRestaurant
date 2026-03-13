<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('recipes', 'menu_item_variation_id')) {
            Schema::table('recipes', function (Blueprint $table) {
                $table->unsignedBigInteger('menu_item_variation_id')->nullable()->after('menu_item_id');
                $table->foreign('menu_item_variation_id')
                    ->references('id')
                    ->on('menu_item_variations')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('recipes', 'menu_item_variation_id')) {
            Schema::table('recipes', function (Blueprint $table) {
                $table->dropForeign(['menu_item_variation_id']);
                $table->dropColumn('menu_item_variation_id');
            });
        }
    }
};
