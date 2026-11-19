<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 100)->nullable();
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 15, 2);
            $table->datetime('start_at');
            $table->datetime('end_at');
            $table->decimal('minimum_purchase', 15, 2)->nullable();
            $table->decimal('maximum_discount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'start_at', 'end_at']);
        });

        Schema::create('promotion_products', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            $table->primary(['promotion_id', 'product_id']);
        });

        Schema::create('order_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promotion_id')->constrained()->restrictOnDelete();
            $table->string('promotion_name', 150);
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 15, 2);
            $table->decimal('discount_amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_promotions');
        Schema::dropIfExists('promotion_products');
        Schema::dropIfExists('promotions');
    }
};
