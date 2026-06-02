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
        Schema::table('doctors', function (Blueprint $table) {
            $table->json('working_hours')->nullable()->after('consultation_fee');
        });

        // Migrate existing data from doctor_schedules to doctors table
        $doctors = DB::table('doctors')->get();
        foreach ($doctors as $doctor) {
            $schedules = DB::table('doctor_schedules')
                ->where('doctor_id', $doctor->id)
                ->get();

            $workingHours = [];
            foreach ($schedules as $schedule) {
                $workingHours[$schedule->day_of_week][] = [
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'max_patients' => $schedule->max_patients,
                    'slot_duration' => $schedule->slot_duration,
                ];
            }

            if (! empty($workingHours)) {
                DB::table('doctors')
                    ->where('id', $doctor->id)
                    ->update(['working_hours' => json_encode($workingHours)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn('working_hours');
        });
    }
};
