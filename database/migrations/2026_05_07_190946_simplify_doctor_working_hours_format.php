<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $daysMap = [
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
        ];

        $doctors = DB::table('doctors')->get();
        foreach ($doctors as $doctor) {
            $oldHours = json_decode($doctor->working_hours, true);
            if (! $oldHours) {
                continue;
            }

            // Check if it's already in the new format (string value instead of array)
            $firstValue = reset($oldHours);
            if (is_string($firstValue)) {
                continue;
            }

            $newHours = [];
            foreach ($daysMap as $index => $name) {
                if (isset($oldHours[$index]) && ! empty($oldHours[$index])) {
                    $first = $oldHours[$index][0];
                    $start = Carbon::parse($first['start_time'])->format('h:i A');
                    $end = Carbon::parse($first['end_time'])->format('h:i A');
                    $newHours[$name] = "{$start} - {$end}";
                } else {
                    $newHours[$name] = 'Closed';
                }
            }

            DB::table('doctors')
                ->where('id', $doctor->id)
                ->update(['working_hours' => json_encode($newHours)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to reverse this without losing data (max_patients, etc.)
    }
};
