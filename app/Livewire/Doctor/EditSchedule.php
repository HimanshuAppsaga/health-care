<?php

namespace App\Livewire\Doctor;

use App\Events\ScheduleUpdated;
use App\Models\Doctor;
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

    protected $daysMap = [
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'thursday',
        5 => 'friday',
        6 => 'saturday',
        0 => 'sunday',
    ];

    public $doctor_id;

    public function mount($id = null)
    {
        $user = Auth::user();

        if ($id) {
            $doctor = Doctor::find($id);
        } else {
            $doctor = $user->ensureDoctorProfileExists();
        }

        if (! $doctor) {
            return redirect()->route('doctor.dashboard');
        }

        $this->doctor_id = $doctor->id;

        $hours = $doctor->working_hours ?? [];

        foreach ($this->daysMap as $num => $name) {
            $this->weekly_schedules[$num] = [];
            $value = $hours[$name] ?? $hours[$num] ?? 'Closed';

            if ($value === 'Closed') {
                continue;
            }

            if (is_string($value)) {
                $parts = explode(' - ', $value);
                if (count($parts) === 2) {
                    $start = Carbon::parse($parts[0]);
                    $end = Carbon::parse($parts[1]);

                    $this->weekly_schedules[$num][] = [
                        'id' => null,
                        'start_hour' => $start->format('h'),
                        'start_min' => $start->format('i'),
                        'start_period' => $start->format('A'),
                        'end_hour' => $end->format('h'),
                        'end_min' => $end->format('i'),
                        'end_period' => $end->format('A'),
                    ];
                }
            } elseif (is_array($value)) {
                foreach ($value as $s) {
                    $start = Carbon::parse($s['start_time']);
                    $end = Carbon::parse($s['end_time']);

                    $this->weekly_schedules[$num][] = [
                        'id' => null,
                        'start_hour' => $start->format('h'),
                        'start_min' => $start->format('i'),
                        'start_period' => $start->format('A'),
                        'end_hour' => $end->format('h'),
                        'end_min' => $end->format('i'),
                        'end_period' => $end->format('A'),
                    ];
                }
            }
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

        $workingHours = [];

        foreach ($this->daysMap as $num => $name) {
            $sessions = $this->weekly_schedules[$num] ?? [];

            if (empty($sessions)) {
                $workingHours[$name] = 'Closed';

                continue;
            }

            // Take the first session for the simple format
            $session = $sessions[0];
            $startTime = Carbon::createFromFormat('h:i A', "{$session['start_hour']}:{$session['start_min']} {$session['start_period']}")->format('h:i A');
            $endTime = Carbon::createFromFormat('h:i A', "{$session['end_hour']}:{$session['end_min']} {$session['end_period']}")->format('h:i A');

            $workingHours[$name] = "{$startTime} - {$endTime}";
        }

        $doctor->update([
            'working_hours' => $workingHours,
        ]);

        broadcast(new ScheduleUpdated(1, 'updated'))->toOthers();

        session()->flash('message', 'Weekly schedule updated successfully.');

        return redirect()->route('doctor.schedule');
    }

    public function render()
    {
        return view('livewire.doctor.editSchedule');
    }
}
