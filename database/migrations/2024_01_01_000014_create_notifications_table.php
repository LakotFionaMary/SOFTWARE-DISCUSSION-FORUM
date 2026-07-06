<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notification_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->enum('type', ['Quiz Announcement', 'Warning', 'Blacklist', 'New Post', 'Reply', 'General']);
            $table->text('message');
            $table->string('related_type')->nullable(); // Quiz, Post, Warning, etc.
            $table->unsignedBigInteger('related_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
