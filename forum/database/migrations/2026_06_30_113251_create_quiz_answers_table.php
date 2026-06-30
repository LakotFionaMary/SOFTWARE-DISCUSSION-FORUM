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
        Schema::create('quiz_answer', function (Blueprint $table) {
            $table->id('result_id');
            $table->foreignId('attempt_id')->constrained('quiz_attempt','attempt_id')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('quiz_question','question_id')->onDelete('cascade');
            $table->enum('selected_option', ['A', 'B', 'C', 'D'])->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('marks_awarded')->default(0);
            $table->timestamps();
 
            // One answer per question per attempt
            $table->unique(['attempt_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
