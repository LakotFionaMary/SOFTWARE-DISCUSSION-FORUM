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
          Schema::create('quiz_configuration', function (Blueprint $table) {
            $table->id('config_id');
            $table->foreignId('quiz_id')->constrained('quizzes','quiz_id')->onDelete('cascade');
            $table->date('scheduled_date');
            $table->time('start_time');
            $table->integer('duration_minutes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_configurations');
    }
};
