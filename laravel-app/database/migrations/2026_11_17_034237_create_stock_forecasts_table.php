<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->foreignId('ann_prediction_id')->nullable()->constrained()->nullOnDelete();
            $table->date('forecast_date');
            $table->decimal('current_stock', 15, 4);
            $table->decimal('predicted_demand', 15, 4)->nullable();
            $table->decimal('average_daily_demand', 15, 4);
            $table->decimal('demand_std_dev', 15, 4)->default(0);
            $table->decimal('lead_time_days', 8, 2)->default(0);
            $table->decimal('service_level', 5, 2)->default(95);
            $table->decimal('z_score', 8, 4);
            $table->decimal('safety_stock', 15, 4);
            $table->decimal('reorder_point', 15, 4);
            $table->decimal('estimated_coverage_days', 10, 2)->nullable();
            $table->decimal('recommended_quantity', 15, 4);
            $table->enum('status', ['normal', 'warning', 'critical'])->default('normal');
            $table->timestamps();

            $table->index(['ingredient_id', 'forecast_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_forecasts');
    }
};
