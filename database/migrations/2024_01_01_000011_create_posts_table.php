<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id('post_id');
            $table->foreignId('topic_id')->constrained('topics', 'topic_id')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->text('content');
            $table->string('attachment_url')->nullable();
            $table->timestamp('posted_at')->useCurrent();
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
