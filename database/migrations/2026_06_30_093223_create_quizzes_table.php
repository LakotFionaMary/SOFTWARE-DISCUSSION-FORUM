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
         Schema::create('quizzes', function (Blueprint $table) {
            $table->id('quiz_id');
            $table->foreignId('lecturer_id')->constrained('users','user_id')->onDelete('cascade');
            $table->string('title');
            $table->string('category');           // e.g. Software Engineering
            $table->date('quiz_date');
            $table->time('start_time');
            $table->integer('duration');           // in minutes
            $table->text('instructions')->nullable();
            $table->integer('attempts_allowed')->default(1);
            $table->boolean('shuffle')->default(false);
            $table->boolean('show_results')->default(true);
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
