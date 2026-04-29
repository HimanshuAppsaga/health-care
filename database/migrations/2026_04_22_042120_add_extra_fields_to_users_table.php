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
        Schema::table('users', function (Blueprint $table) {
            $table->string('emergency_contact_name')->nullable()->after('phone');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->text('address')->nullable()->after('emergency_contact_phone');
            $table->string('unit')->nullable()->after('department');
            $table->string('supervisor_name')->nullable()->after('unit');
            $table->decimal('rating', 2, 1)->default(5.0)->after('supervisor_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'emergency_contact_name',
                'emergency_contact_phone',
                'address',
                'unit',
                'supervisor_name',
                'rating'
            ]);
        });
    }
};
