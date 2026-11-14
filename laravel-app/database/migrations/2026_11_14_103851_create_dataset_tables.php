<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dataset_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version', 50)->unique();
            $table->string('dataset_name', 150);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_records')->default(0);
            $table->enum('status', ['draft', 'validated', 'approved', 'archived'])->default('draft');
            $table->json('feature_schema')->nullable();
            $table->json('validation_summary')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['start_date', 'end_date']);
        });

        Schema::create('dataset_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->date('business_date');

            // Target
            $table->decimal('actual_demand', 15, 4)->default(0);

            // Lags (nullable kalau data historis tidak cukup)
            $table->decimal('lag_d1', 15, 4)->nullable();
            $table->decimal('lag_d2', 15, 4)->nullable();
            $table->decimal('lag_d3', 15, 4)->nullable();
            $table->decimal('lag_d7', 15, 4)->nullable();

            // Rolling averages
            $table->decimal('rolling_mean_3d', 15, 4)->nullable();
            $table->decimal('rolling_mean_7d', 15, 4)->nullable();

            // Weather (dari weather_daily_summaries)
            $table->decimal('temperature', 7, 3)->nullable();
            $table->decimal('rainfall', 10, 3)->nullable();

            // Calendar
            $table->tinyInteger('day_of_week');
            $table->boolean('is_weekend')->default(false);

            // Quality
            $table->enum('data_quality', ['valid', 'partial', 'invalid'])->default('valid');

            $table->timestamps();

            $table->unique(['dataset_version_id', 'ingredient_id', 'business_date'], 'ds_record_unique');
            $table->index(['dataset_version_id', 'business_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dataset_records');
        Schema::dropIfExists('dataset_versions');
    }
};
