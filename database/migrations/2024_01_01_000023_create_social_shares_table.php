<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_shares', function (Blueprint $table) {
            $table->id('share_id');
            $table->foreignId('post_id')->constrained('posts', 'post_id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->enum('platform', ['WhatsApp', 'Twitter', 'Facebook', 'LinkedIn', 'Clipboard', 'Other'])->default('Other');
            $table->string('shared_url')->nullable();
            $table->timestamp('shared_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_shares');
    }
};
