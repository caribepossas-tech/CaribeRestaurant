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
        if (!Schema::hasColumn('payment_gateway_credentials', 'bank_name')) {
            Schema::table('payment_gateway_credentials', function (Blueprint $table) {
                $table->string('bank_name')->nullable();
                $table->text('bank_account_details')->nullable();
            });
        }

        if (!Schema::hasColumn('payments', 'receipt')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('receipt')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('receipt');
        });

        Schema::table('payment_gateway_credentials', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_details']);
        });
    }
};
