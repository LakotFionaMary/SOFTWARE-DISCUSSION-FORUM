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
        Schema::create('topics', function (Blueprint $table) {
            $table->id('topic_id');
            $table->foreignId('group_id')->constrained('groups','group_id')->onDelete('cascade');
            $table->string('title');
            $table->foreignId('created_by')->constrained('users','user_id')->onDelete('cascade');
            $table->string('category')->nullable();      // auto-set by ML classifier
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
