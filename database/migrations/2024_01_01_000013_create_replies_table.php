<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('replies', function (Blueprint $table) {
            $table->id('reply_id');
            $table->foreignId('post_id')->constrained('posts', 'post_id')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->text('content');
            $table->timestamp('replied_at')->useCurrent();
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('replies');
    }
};
