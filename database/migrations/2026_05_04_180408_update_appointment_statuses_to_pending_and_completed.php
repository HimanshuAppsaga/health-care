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
        // 1. Migrate existing data first
        DB::table('appointments')
            ->whereIn('status', ['confirmed', 'cancelled', 'no_show'])
            ->update(['status' => 'pending']);

        // 2. Update the column definition
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'])->default('pending')->change();
        });
    }
};
