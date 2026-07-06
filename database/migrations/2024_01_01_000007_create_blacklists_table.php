<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklists', function (Blueprint $table) {
            $table->id('blacklist_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups', 'group_id')->cascadeOnDelete();
            $table->dateTime('start_date');
            $table->integer('duration_days');
            $table->dateTime('end_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklists');
    }
};
