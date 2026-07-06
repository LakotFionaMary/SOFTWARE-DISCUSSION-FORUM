<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id('result_id');
            $table->foreignId('attempt_id')->constrained('quiz_attempts', 'attempt_id')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('quiz_questions', 'question_id')->cascadeOnDelete();
            $table->enum('selected_option', ['A', 'B', 'C', 'D'])->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('marks_awarded')->default(0);
            $table->timestamps();
            $table->unique(['attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
