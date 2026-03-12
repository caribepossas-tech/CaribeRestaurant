<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('recipe_ingredients')) {
            Schema::create('recipe_ingredients', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('recipe_id');
                $table->unsignedBigInteger('ingredient_id');
                $table->double('quantity');
                $table->timestamps();

                $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade');
                $table->foreign('ingredient_id')->references('id')->on('ingredients')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};
