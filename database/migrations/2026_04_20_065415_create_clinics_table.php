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
        if (Schema::hasTable('clinics')) {
            Schema::table('clinics', function (Blueprint $table) {
                if (! Schema::hasColumn('clinics', 'name')) {
                    $table->string('name')->after('id');
                }
                if (! Schema::hasColumn('clinics', 'address')) {
                    $table->string('address')->nullable()->after('name');
                }
            });
        } else {
            Schema::create('clinics', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('address')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
