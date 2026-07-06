<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participation_scores', function (Blueprint $table) {
            $table->id('score_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups', 'group_id')->cascadeOnDelete();
            $table->foreignId('criteria_id')->constrained('scoring_criteria', 'criteria_id')->cascadeOnDelete();
            $table->decimal('points_earned', 6, 2)->default(0);
            $table->timestamp('last_updated')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participation_scores');
    }
};
