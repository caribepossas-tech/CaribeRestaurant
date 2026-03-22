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
        if (!Schema::hasColumn('global_settings', 'show_logo_text')) {
            Schema::table('global_settings', function (Blueprint $table) {
                $table->boolean('show_logo_text')->default(true);
            });
        }

        if (!Schema::hasColumn('restaurants', 'show_logo_text')) {
            Schema::table('restaurants', function (Blueprint $table) {
                $table->boolean('show_logo_text')->default(true);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('show_logo_text');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('show_logo_text');
        });
    }
};
