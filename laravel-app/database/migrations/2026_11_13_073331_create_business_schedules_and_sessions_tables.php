<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_schedules', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('day_of_week')->unique(); // 0=Sunday, 6=Saturday
            $table->time('opening_time');
            $table->time('closing_time');
            $table->tinyInteger('closing_day_offset')->default(0);
            $table->boolean('is_open')->default(true);
            $table->timestamps();
        });

        Schema::create('business_sessions', function (Blueprint $table) {
            $table->id();
            $table->date('business_date')->unique();
            $table->datetime('opened_at');
            $table->datetime('closed_at')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('business_sessions');
        Schema::dropIfExists('business_schedules');
    }
};
