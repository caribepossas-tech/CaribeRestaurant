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
        Schema::table('superadmin_payment_gateways', function (Blueprint $table) {
            $table->text('test_wompi_integrity_secret')->nullable()->after('wompi_test_events_secret');
            $table->text('live_wompi_integrity_secret')->nullable()->after('wompi_live_events_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('superadmin_payment_gateways', function (Blueprint $table) {
            $table->dropColumn(['test_wompi_integrity_secret', 'live_wompi_integrity_secret']);
        });
    }
};
