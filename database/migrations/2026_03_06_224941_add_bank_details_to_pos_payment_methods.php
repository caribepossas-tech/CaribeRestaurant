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
        Schema::table('pos_payment_methods', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('name');
            $table->text('bank_account_details')->nullable()->after('bank_name');
            $table->boolean('show_in_shop')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_payment_methods', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_details', 'show_in_shop']);
        });
    }
};
