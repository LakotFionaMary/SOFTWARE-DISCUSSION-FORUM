<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_admins', function (Blueprint $table) {
            $table->id('group_admin_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups', 'group_id')->cascadeOnDelete();
            $table->timestamp('appointed_at')->useCurrent();
            $table->foreignId('appointed_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_admins');
    }
};
