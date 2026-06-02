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
            $table->dropUnique(['employee_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id',
                'emergency_contact_name',
                'emergency_contact_phone',
                'address',
                'department',
                'unit',
                'supervisor_name',
                'joining_date',
                'email_verified_at',
                'last_login_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->unique()->after('id');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('emergency_contact_name')->nullable()->after('phone');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->text('address')->nullable()->after('emergency_contact_phone');
            $table->string('department')->nullable()->after('phone');
            $table->string('unit')->nullable()->after('department');
            $table->string('supervisor_name')->nullable()->after('unit');
            $table->date('joining_date')->nullable()->after('department');
            $table->timestamp('last_login_at')->nullable()->after('password');
        });
    }
};
