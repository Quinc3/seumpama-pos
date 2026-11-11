<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 100)->unique();
            $table->string('name', 180);
            $table->string('category', 100)->nullable();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->enum('ingredient_type', ['core', 'non_core'])->default('core');
            $table->decimal('current_stock', 15, 4)->default(0);
            $table->decimal('minimum_stock', 15, 4)->nullable();
            $table->decimal('safety_stock', 15, 4)->nullable();
            $table->decimal('lead_time_days', 8, 2)->nullable();
            $table->boolean('is_prediction_target')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'ingredient_type']);
            $table->index('is_prediction_target');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
