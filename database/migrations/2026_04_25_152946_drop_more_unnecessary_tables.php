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
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('patient_documents');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('clinics');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('clinic_id');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('clinic_id');
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn('clinic_id');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('clinic_id');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
