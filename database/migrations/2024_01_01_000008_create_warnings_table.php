<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warnings', function (Blueprint $table) {
            $table->id('warning_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups', 'group_id')->cascadeOnDelete();
            $table->unsignedTinyInteger('sequence_number'); // 1st or 2nd warning
            $table->dateTime('issue_date')->useCurrent();
            $table->boolean('resolved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warnings');
    }
};
