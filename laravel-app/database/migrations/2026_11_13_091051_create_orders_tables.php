<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 100)->unique();
            $table->foreignId('business_session_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'pending', 'paid', 'cancelled', 'refunded'])->default('paid');
            $table->text('notes')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->timestamps();

            $table->index(['business_session_id', 'status']);
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('recipe_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name', 180);
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();

            $table->index('order_id');
        });

        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('reference_number', 150)->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('completed');
            $table->datetime('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_ingredient_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->foreignId('recipe_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity_per_unit', 15, 4);
            $table->decimal('sold_quantity', 15, 4);
            $table->decimal('total_quantity', 15, 4);
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('order_item_id');
            $table->index(['ingredient_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_ingredient_usages');
        Schema::dropIfExists('order_payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
