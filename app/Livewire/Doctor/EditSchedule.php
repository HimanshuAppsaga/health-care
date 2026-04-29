<?php

namespace App\Livewire\Doctor;

use App\Events\ScheduleUpdated;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Edit Schedule | ClinicOS')]
class EditSchedule extends Component
{
    public $weekly_schedules = [];

    public $doctor_id;

    public function mount($id = null)
    {
        $user = Auth::user();

        if ($id) {
            $doctor = Doctor::find($id);
        } else {
            $doctor = $user->doctor;

            // Auto-create doctor record if user has doctor role but no doctor record
            if (! $doctor && $user->hasRole('doctor')) {
                $doctor = Doctor::create([
                    'user_id' => $user->id,
                    'specialization' => 'General',
                    'qualification' => 'MBBS',
                    'experience_years' => 0,
                    'consultation_fee' => 0,
                ]);
            }
        }

        if (! $doctor) {
            return redirect()->route('doctor.dashboard');
        }

        $this->doctor_id = $doctor->id;

        // Initialize all days (1=Mon, ..., 6=Sat, 0=Sun)
        $days = [1, 2, 3, 4, 5, 6, 0];
        foreach ($days as $day) {
            $this->weekly_schedules[$day] = [];
        }

        $schedules = DoctorSchedule::where('doctor_id', $doctor->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        foreach ($schedules as $s) {
            $start = Carbon::parse($s->start_time);
            $end = Carbon::parse($s->end_time);

            $this->weekly_schedules[$s->day_of_week][] = [
                'id' => $s->id,
                'start_hour' => $start->format('h'),
                'start_min' => $start->format('i'),
                'start_period' => $start->format('A'),
                'end_hour' => $end->format('h'),
                'end_min' => $end->format('i'),
                'end_period' => $end->format('A'),
            ];
        }

        // If no sessions for a day, maybe add an empty one?
        // The image shows days even without sessions? No, it shows sessions for every day.
        // I'll leave them empty if they are empty, but the user can add them.
    }

    public function addSession($day)
    {
        $this->weekly_schedules[$day][] = [
            'id' => null,
            'start_hour' => '09',
            'start_min' => '00',
            'start_period' => 'AM',
            'end_hour' => '05',
            'end_min' => '00',
            'end_period' => 'PM',
        ];
    }

    public function removeSession($day, $index)
    {
        unset($this->weekly_schedules[$day][$index]);
        $this->weekly_schedules[$day] = array_values($this->weekly_schedules[$day]);
    }

    public function syncToAllDays($fromDay)
    {
        $sourceSessions = $this->weekly_schedules[$fromDay];

        foreach ($this->weekly_schedules as $day => $sessions) {
            if ($day != $fromDay) {
                // Deep copy the sessions to avoid reference issues
                $this->weekly_schedules[$day] = json_decode(json_encode($sourceSessions), true);
            }
        }

        session()->flash('sync_message', 'Schedule synced to all days successfully.');
    }

    public function save()
    {
        $doctor = Doctor::find($this->doctor_id);

        if (! $doctor) {
            return;
        }

        // Transaction for atomic update
        \DB::transaction(function () use ($doctor) {
            // Clear existing schedules for this doctor
            DoctorSchedule::where('doctor_id', $doctor->id)->delete();

            foreach ($this->weekly_schedules as $day => $sessions) {
                $processedSessions = [];

                foreach ($sessions as $session) {
                    $startTime = Carbon::createFromFormat('h:i A', "{$session['start_hour']}:{$session['start_min']} {$session['start_period']}")->format('H:i:s');
                    $endTime = Carbon::createFromFormat('h:i A', "{$session['end_hour']}:{$session['end_min']} {$session['end_period']}")->format('H:i:s');

                    // Avoid duplicate timings for the same day
                    $sessionHash = $startTime.'-'.$endTime;
                    if (in_array($sessionHash, $processedSessions)) {
                        continue;
                    }
                    $processedSessions[] = $sessionHash;

                    DoctorSchedule::create([
                        'doctor_id' => $doctor->id,
                        'day_of_week' => $day,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'max_patients' => 1,
                        'slot_duration' => 15,
                    ]);
                }
            }
        });

        broadcast(new ScheduleUpdated(1, 'updated'))->toOthers();

        session()->flash('message', 'Weekly schedule updated successfully.');

        return redirect()->route('doctor.schedule');
    }

    public function render()
    {
        return view('livewire.doctor.editSchedule');
    }
}
