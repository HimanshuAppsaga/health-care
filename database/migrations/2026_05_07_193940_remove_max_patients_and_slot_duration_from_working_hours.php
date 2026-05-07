<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $doctors = DB::table('doctors')->get();

        foreach ($doctors as $doctor) {
            $workingHours = json_decode($doctor->working_hours, true);

            if (is_array($workingHours)) {
                foreach ($workingHours as $day => $sessions) {
                    if (is_array($sessions)) {
                        foreach ($sessions as $key => $session) {
                            if (is_array($session)) {
                                unset($sessions[$key]['max_patients']);
                                unset($sessions[$key]['slot_duration']);
                            }
                        }
                        $workingHours[$day] = $sessions;
                    }
                }

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
        $doctors = DB::table('doctors')->get();

        foreach ($doctors as $doctor) {
            $workingHours = json_decode($doctor->working_hours, true);

            if (is_array($workingHours)) {
                foreach ($workingHours as $day => $sessions) {
                    if (is_array($sessions)) {
                        foreach ($sessions as $key => $session) {
                            if (is_array($session)) {
                                $sessions[$key]['max_patients'] = 1;
                                $sessions[$key]['slot_duration'] = 15;
                            }
                        }
                        $workingHours[$day] = $sessions;
                    }
                }

                DB::table('doctors')
                    ->where('id', $doctor->id)
                    ->update(['working_hours' => json_encode($workingHours)]);
            }
        }
    }
};
