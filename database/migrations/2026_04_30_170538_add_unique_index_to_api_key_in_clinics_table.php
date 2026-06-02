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
        Schema::table('clinics', function (Blueprint $table) {
            $table->unique('api_key');
            $table->index('api_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') !== 'sqlite') {
            Schema::table('clinics', function (Blueprint $table) {
                $table->string('api_key', 64)->nullable()->change();
                $table->dropUnique(['api_key']);
                $table->dropIndex(['api_key']);
            });
        }
    }
};
