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
        Schema::create('replies', function (Blueprint $table) {
            $table->id('reply_id');
            $table->foreignId('post_id')->constrained('posts','post_id')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('users','user_id')->onDelete('cascade');
            $table->text('content');
            $table->timestamp('replied_at')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('replies');
    }
};
