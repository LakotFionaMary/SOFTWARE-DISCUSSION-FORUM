<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id('topic_id');
            $table->foreignId('group_id')->constrained('groups', 'group_id')->cascadeOnDelete();
            $table->string('title');
            $table->foreignId('created_by')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->string('category')->nullable(); // set by ML classifier
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
