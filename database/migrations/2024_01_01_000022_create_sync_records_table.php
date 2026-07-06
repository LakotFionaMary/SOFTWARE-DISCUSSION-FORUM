<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_records', function (Blueprint $table) {
            $table->id('sync_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->dateTime('last_synced_at')->nullable();
            $table->json('pending_actions')->nullable();
            $table->enum('device_type', ['Web', 'Desktop'])->default('Web');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_records');
    }
};
