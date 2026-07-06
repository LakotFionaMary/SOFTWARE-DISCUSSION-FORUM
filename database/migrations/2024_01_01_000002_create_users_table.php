<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->enum('presence_status', ['Online', 'Offline'])->default('Offline');
            $table->timestamp('last_active_at')->nullable();
            $table->boolean('rules_accepted')->default(false);
            $table->timestamp('rules_accepted_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
