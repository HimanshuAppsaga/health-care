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
            $table->foreignId('role_id')->nullable()->constrained()->onDelete('set null');
        });

        // Transfer data - use standard ANSI SQL so it works in both MySQL and SQLite
        DB::statement('UPDATE users SET role_id = (SELECT role_id FROM user_roles WHERE user_roles.user_id = users.id) WHERE EXISTS (SELECT 1 FROM user_roles WHERE user_roles.user_id = users.id)');

        Schema::dropIfExists('user_roles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
        });

        // Restore data
        DB::statement('INSERT INTO user_roles (user_id, role_id) SELECT id, role_id FROM users WHERE role_id IS NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
