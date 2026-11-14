<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('provider_code', 80)->unique();
            $table->string('base_url', 255);
            $table->boolean('is_active')->default(true);
            $table->json('configuration')->nullable();
            $table->timestamps();
        });

        Schema::create('weather_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('weather_providers')->restrictOnDelete();
            $table->datetime('observed_at');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('temperature', 7, 3)->nullable();
            $table->decimal('rainfall', 10, 3)->nullable();
            $table->string('weather_condition', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['provider_id', 'observed_at'], 'weather_obs_unique');
            $table->index('observed_at');
        });

        Schema::create('weather_daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('business_date')->unique();
            $table->decimal('average_temperature', 7, 3)->nullable();
            $table->decimal('minimum_temperature', 7, 3)->nullable();
            $table->decimal('maximum_temperature', 7, 3)->nullable();
            $table->decimal('total_rainfall', 10, 3)->nullable();
            $table->string('dominant_condition', 100)->nullable();
            $table->integer('observation_count')->default(0);
            $table->decimal('data_completeness', 5, 2)->default(0);
            $table->enum('source_status', ['complete', 'partial', 'missing'])->default('partial');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_daily_summaries');
        Schema::dropIfExists('weather_observations');
        Schema::dropIfExists('weather_providers');
    }
};
