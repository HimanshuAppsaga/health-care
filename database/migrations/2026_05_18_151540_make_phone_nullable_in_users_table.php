<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->change();
        });

        // Convert existing empty phone strings to null to prevent unique key violations
        DB::table('users')
            ->where('phone', '')
            ->update(['phone' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert any null phone values to empty string before making it non-nullable (may fail if there are duplicates)
        DB::table('users')
            ->whereNull('phone')
            ->update(['phone' => '']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable(false)->change();
        });
    }
};
