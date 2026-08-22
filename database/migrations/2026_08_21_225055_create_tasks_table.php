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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type');

            $table->string('title');

            $table->json('payload')->nullable();

            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled'
            ])->default('pending');

            $table->enum('priority', [
                'low',
                'normal',
                'high',
                'critical'
            ])->default('normal');

            $table->unsignedInteger('attempts')
                ->default(0);

            $table->timestamp('started_at')
                ->nullable();

            $table->timestamp('completed_at')
                ->nullable();

            $table->timestamp('failed_at')
                ->nullable();

            $table->text('error_message')
                ->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'priority']);
            $table->index('type');

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
