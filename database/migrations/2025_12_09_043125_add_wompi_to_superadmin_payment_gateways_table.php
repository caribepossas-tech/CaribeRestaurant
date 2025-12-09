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
            $table->boolean('wompi_status')->default(false)->after('stripe_status');
            $table->enum('wompi_type', ['test', 'live'])->default('test')->after('wompi_status');

            $table->text('test_wompi_pub_key')->nullable()->after('wompi_type');
            $table->text('test_wompi_prv_key')->nullable()->after('test_wompi_pub_key');
            $table->text('wompi_test_events_secret')->nullable()->after('test_wompi_prv_key');

            $table->text('live_wompi_pub_key')->nullable()->after('wompi_test_events_secret');
            $table->text('live_wompi_prv_key')->nullable()->after('live_wompi_pub_key');
            $table->text('wompi_live_events_secret')->nullable()->after('live_wompi_prv_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('superadmin_payment_gateways', function (Blueprint $table) {
            $table->dropColumn([
                'wompi_status',
                'wompi_type',
                'test_wompi_pub_key',
                'test_wompi_prv_key',
                'wompi_test_events_secret',
                'live_wompi_pub_key',
                'live_wompi_prv_key',
                'wompi_live_events_secret',
            ]);
        });
    }
};
