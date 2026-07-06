<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id('attempt_id');
            $table->foreignId('quiz_id')->constrained('quizzes', 'quiz_id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->boolean('auto_submitted')->default(false);
            $table->decimal('score', 6, 2)->default(0);
            $table->timestamps();
            $table->unique(['quiz_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
