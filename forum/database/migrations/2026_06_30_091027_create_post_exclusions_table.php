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
        Schema::create('post_exclusions', function (Blueprint $table) {
            $table->id('exclusion_id');
            $table->foreignId('post_id')->constrained('posts','post_id')->onDelete('cascade');
            $table->foreignId('excluded_user_id')->constrained('users','user_id')->onDelete('cascade');
            $table->timestamps();
 
            $table->unique(['post_id', 'excluded_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_exclusions');
    }
};
