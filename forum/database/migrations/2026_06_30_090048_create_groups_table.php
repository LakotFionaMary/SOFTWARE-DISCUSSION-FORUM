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
     Schema::create('groups', function (Blueprint $table) {
            $table->id('group_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users','user_id')->onDelete('set null');
            $table->integer('inactivity_warning_period')->default(10); // days
            $table->integer('blacklist_duration_days')->default(7);
            $table->timestamps();

        });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
