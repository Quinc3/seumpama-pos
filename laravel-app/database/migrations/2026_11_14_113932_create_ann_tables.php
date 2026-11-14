<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ann_models', function (Blueprint $table) {
            $table->id();
            $table->string('model_name', 150);
            $table->string('model_version', 100)->unique();
            $table->string('algorithm', 80)->default('MLP');
            $table->json('architecture')->nullable();
            $table->json('feature_schema')->nullable();
            $table->json('target_definition')->nullable();
            $table->string('artifact_path', 255)->nullable();
            $table->string('scaler_path', 255)->nullable();
            $table->enum('status', ['draft', 'candidate', 'active', 'archived'])->default('candidate');
            $table->foreignId('dataset_version_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('ann_training_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ann_model_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dataset_version_id')->constrained()->restrictOnDelete();
            $table->string('training_id', 100)->unique();
            $table->datetime('training_started_at')->nullable();
            $table->datetime('training_completed_at')->nullable();
            $table->enum('training_status', ['queued', 'running', 'completed', 'failed'])->default('queued');
            $table->json('hyperparameters')->nullable();
            $table->json('train_metrics')->nullable();
            $table->json('validation_metrics')->nullable();
            $table->json('test_metrics')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('training_status');
        });

        Schema::create('ann_model_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ann_model_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->integer('output_index')->default(0);
            $table->json('evaluation_metrics')->nullable();
            $table->timestamps();

            $table->unique(['ann_model_id', 'ingredient_id'], 'ann_model_ing_unique');
        });

        Schema::create('ann_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ann_model_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dataset_version_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->date('prediction_date');
            $table->datetime('prediction_generated_at');
            $table->decimal('predicted_demand', 15, 4);
            $table->decimal('actual_demand', 15, 4)->nullable();
            $table->decimal('mae', 15, 4)->nullable();
            $table->decimal('rmse', 15, 4)->nullable();
            $table->enum('status', ['generated', 'evaluated', 'invalid'])->default('generated');
            $table->timestamps();

            $table->index(['ingredient_id', 'prediction_date']);
            $table->index('ann_model_id');
            $table->unique(['ann_model_id', 'ingredient_id', 'prediction_date'], 'ann_pred_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ann_predictions');
        Schema::dropIfExists('ann_model_ingredients');
        Schema::dropIfExists('ann_training_runs');
        Schema::dropIfExists('ann_models');
    }
};
