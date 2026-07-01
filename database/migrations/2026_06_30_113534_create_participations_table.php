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
        Schema::create('participations', function (Blueprint $table) {
            $table->id('score_id');
            $table->foreignId('user_id')->constrained('users','user_id')->onDelete('cascade');
            $table->foreignId('group_id')->constrained('groups','group_id')->onDelete('cascade');
            $table->foreignId('criteria_id')->constrained('scoring_criteria','criteria_id')->onDelete('cascade');
            $table->decimal('points_earned', 6, 2)->default(0);
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participations');
    }
};
