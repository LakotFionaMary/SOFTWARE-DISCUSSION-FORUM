<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scoring_criteria', function (Blueprint $table) {
            $table->id('criteria_id');
            $table->foreignId('group_id')->constrained('groups', 'group_id')->cascadeOnDelete();
            $table->foreignId('lecturer_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->string('description');
            $table->enum('activity_type', ['post', 'reply', 'quiz_attempt', 'topic_creation']);
            $table->decimal('max_marks', 6, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scoring_criteria');
    }
};
