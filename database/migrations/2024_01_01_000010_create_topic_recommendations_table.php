<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic_recommendations', function (Blueprint $table) {
            $table->id('recommendation_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained('topics', 'topic_id')->cascadeOnDelete();
            $table->decimal('relevance_score', 4, 3); // 0.000 - 1.000
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_recommendations');
    }
};
