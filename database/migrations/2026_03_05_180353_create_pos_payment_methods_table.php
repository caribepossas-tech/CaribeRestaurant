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
        Schema::create('pos_payment_methods', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('restaurant_id')->index();
            $blueprint->foreign('restaurant_id')->references('id')->on('restaurants')->cascadeOnDelete()->cascadeOnUpdate();
            $blueprint->string('name');
            $blueprint->enum('status', ['active', 'inactive'])->default('active');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_payment_methods');
    }
};
