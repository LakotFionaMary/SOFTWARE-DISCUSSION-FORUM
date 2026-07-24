<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A separate table from `memberships` on purpose — memberships
        // already means "actually a member" everywhere else in the app
        // (blacklist checks, statistics, participation tracking). Mixing
        // pending rows into it would quietly break those assumptions.
        Schema::create('group_join_requests', function (Blueprint $table) {
            $table->id('join_request_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups', 'group_id')->cascadeOnDelete();
            $table->boolean('rules_accepted')->default(false);
            $table->enum('status', ['pending', 'approved', 'declined'])->default('pending');
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_join_requests');
    }
};
