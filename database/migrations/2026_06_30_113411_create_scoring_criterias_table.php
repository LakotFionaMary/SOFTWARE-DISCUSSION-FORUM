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
          Schema::create('scoring_criteria', function (Blueprint $table) {
            $table->id('criteria_id');
            $table->foreignId('group_id')->constrained('groups','group_id')->onDelete('cascade');
            $table->foreignId('lecturer_id')->constrained('users','user_id')->onDelete('cascade');
            $table->string('description')->nullable(); // e.g. "5 marks per post"
            $table->enum('activity_type', ['post', 'reply', 'quiz_attempt', 'topic_creation']);
            $table->decimal('max_marks', 6, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scoring_criterias');
    }
};
