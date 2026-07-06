<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id('membership_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups', 'group_id')->cascadeOnDelete();
            $table->boolean('rules_accepted')->default(false);
            $table->timestamp('joined_at')->useCurrent();
            $table->enum('role', ['Member', 'Lecturer', 'Administrator'])->default('Member');
            $table->timestamps();
            $table->unique(['user_id', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
