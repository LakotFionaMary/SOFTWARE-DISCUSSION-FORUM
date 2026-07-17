<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blacklists', function (Blueprint $table) {
            $table->string('reason', 20)->default('manual')->after('group_id');
        });

        DB::table('blacklists')->update(['reason' => 'inactivity']);
    }

    public function down(): void
    {
        Schema::table('blacklists', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
};
