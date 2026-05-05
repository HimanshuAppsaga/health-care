<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToClinicsTable extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
            $table->text('about_clinic')->nullable()->after('description');
            $table->json('working_hours')->nullable()->after('about_clinic');
            $table->string('contact_number', 20)->nullable()->after('working_hours');
            $table->decimal('latitude', 10, 7)->nullable()->after('contact_number');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'about_clinic',
                'working_hours',
                'contact_number',
                'latitude',
                'longitude',
            ]);
        });
    }
}