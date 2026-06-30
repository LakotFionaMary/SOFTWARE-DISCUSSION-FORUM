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
       Schema::create('notifications_log', function (Blueprint $table) {
            $table->id('notification_id');
            $table->foreignId('user_id')->constrained('users','user_id')->onDelete('cascade');
            $table->enum('type', ['Quiz Announcement', 'Warning', 'Blacklist', 'New Post', 'Reply', 'General']);
            $table->text('message');
            $table->string('related_type')->nullable();   // e.g. Quiz, Post, Warning
            $table->unsignedBigInteger('related_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
