<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id('quiz_id');
            $table->foreignId('group_id')->constrained('groups', 'group_id')->cascadeOnDelete();
            $table->foreignId('lecturer_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->string('title');
            $table->enum('status', ['Draft', 'Scheduled', 'Open', 'Closed'])->default('Draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
