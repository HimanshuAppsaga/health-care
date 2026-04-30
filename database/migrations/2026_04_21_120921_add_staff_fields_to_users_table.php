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
            $table->string('employee_id')->nullable()->unique()->after('id');
            $columnBefore = Schema::hasColumn('users', 'phone') ? 'phone' : 'email';
            $table->string('department')->nullable()->after($columnBefore);
            $table->date('joining_date')->nullable()->after('department');
            $table->text('bio')->nullable()->after('joining_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['employee_id', 'department', 'joining_date', 'bio']);
        });
    }
};
