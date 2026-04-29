<?php

namespace App\Livewire\Doctor;

use App\Events\QueueUpdated;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Queue;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Doctor Dashboard | ClinicOS')]
class Dashboard extends Component
{
    public bool $isDoctorOnHold = false;

    public $lastTokenNumber;

    public $lastStatus;

    public function getListeners()
    {
        $clinicId = 1;

        return [
            "echo:queue-updates.{$clinicId},QueueUpdated" => '$refresh',
            "echo:schedule-updates.{$clinicId},ScheduleUpdated" => '$refresh',
        ];
    }

    public function mount()
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        if (! $doctor && $user->hasRole('doctor')) {

            Doctor::create([
                'user_id' => $user->id,
                'clinic_id' => $doctor?->clinic_id ?? 1, // fallback
                'specialization' => 'General',
                'qualification' => 'MBBS',
                'experience_years' => 0,
                'consultation_fee' => 0,
            ]);
        }

        $this->isDoctorOnHold = $doctor ? (bool) $doctor->is_on_hold : false;

        $today = Carbon::today();
        $doctorId = $doctor->id ?? 0;

        $nowServing = Queue::whereHas('appointment', function ($query) use ($doctorId, $today) {
            $query->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $today);
        })
            ->whereIn('status', ['serving', 'hold'])
            ->first();

        $this->lastTokenNumber = $nowServing ? $nowServing->token_number : null;
        $this->lastStatus = $nowServing ? $nowServing->status : null;
    }

    public function callNextPatient()
    {
        if ($this->isDoctorOnHold) {
            return;
        }

        $today = Carbon::today();
        $doctor = auth()->user()->doctor;
        $doctorId = $doctor->id ?? 0;

        $current = Queue::whereHas('appointment', function ($query) use ($doctorId, $today) {
            $query->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $today);
        })->where('status', 'serving')->first();

        if ($current) {
            $current->update(['status' => 'completed']);
        }

        $next = Queue::whereHas('appointment', function ($query) use ($doctorId, $today) {
            $query->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $today);
        })
            ->where('status', 'waiting')
            ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
            ->first();

        if ($next) {
            $next->update(['status' => 'serving', 'called_at' => now()]);
            broadcast(new QueueUpdated(1, 'next'))->toOthers();
        }
    }

    public function markAsDone()
    {
        if ($this->isDoctorOnHold) {
            return;
        }

        $today = Carbon::today();
        $doctor = auth()->user()->doctor;
        $doctorId = $doctor->id ?? 0;

        $current = Queue::whereHas('appointment', function ($query) use ($doctorId, $today) {
            $query->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $today);
        })->whereIn('status', ['serving', 'hold'])->first();

        if ($current) {
            $current->update(['status' => 'completed']);
            if ($current->appointment) {
                $current->appointment->update(['status' => 'completed']);
            }
            broadcast(new QueueUpdated(1, 'completed'))->toOthers();
        }
    }

    public function toggleHold()
    {
        $doctor = auth()->user()->doctor;
        if ($doctor) {
            $this->isDoctorOnHold = ! $this->isDoctorOnHold;
            $doctor->update(['is_on_hold' => $this->isDoctorOnHold]);

            // If toggling hold while serving, also update the queue status for better reflection
            $today = Carbon::today();
            $doctorId = $doctor->id;

            $current = Queue::whereHas('appointment', function ($query) use ($doctorId, $today) {
                $query->where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', $today);
            })
                ->whereIn('status', ['serving', 'hold'])
                ->first();

            if ($current) {
                $current->update(['status' => $this->isDoctorOnHold ? 'hold' : 'serving']);
            }
            broadcast(new QueueUpdated(1, $this->isDoctorOnHold ? 'hold' : 'continue'))->toOthers();
        }
    }

    public function transferToken()
    {
        if ($this->isDoctorOnHold) {
            return;
        }

        $today = Carbon::today();
        $doctor = auth()->user()->doctor;
        $doctorId = $doctor->id ?? 0;

        $current = Queue::whereHas('appointment', function ($query) use ($doctorId, $today) {
            $query->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $today);
        })->where('status', 'serving')->first();

        if ($current) {
            $currentTokenNum = (int) $current->token_number;
            $newTokenStr = (string) ($currentTokenNum + 6);

            // Shift the next 6 tokens down by 1
            $nextTokensToShift = Queue::whereHas('appointment', function ($query) use ($doctorId, $today) {
                $query->where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', $today);
            })
                ->where('status', 'waiting')
                ->whereRaw('CAST(token_number AS UNSIGNED) > ?', [$currentTokenNum])
                ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
                ->take(6)
                ->get();

            foreach ($nextTokensToShift as $q) {
                $num = (int) $q->token_number;
                $newNumStr = (string) ($num - 1);
                $q->update(['token_number' => $newNumStr]);
                if ($q->appointment) {
                    $q->appointment->update(['token' => $newNumStr]);
                }
            }

            // Update the transferred token
            $current->update([
                'token_number' => $newTokenStr,
                'status' => 'waiting',
            ]);

            if ($current->appointment) {
                $current->appointment->update(['token' => $newTokenStr]);
            }
            broadcast(new QueueUpdated(1, 'transfer'))->toOthers();
        }
    }

    public function render()
    {
        $today = Carbon::today();
        $doctor = auth()->user()->doctor;
        $doctorId = $doctor->id ?? 0;

        $totalAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->count();

        $checkedIn = Queue::whereHas('appointment', function ($query) use ($doctorId, $today) {
            $query->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $today);
        })
            ->whereIn('status', ['waiting', 'serving', 'completed'])
            ->count();

        $pendingArrivals = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->where('status', 'confirmed')
            ->count();

        $completedToday = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->where('status', 'completed')
            ->count();

        $revenueToday = 0;

        $nowServing = Queue::with('appointment')
            ->whereHas('appointment', function ($query) use ($doctorId, $today) {
                $query->where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', $today);
            })
            ->whereIn('status', ['serving', 'hold'])
            ->first();

        $currentStatus = $nowServing ? $nowServing->status : null;
        $currentToken = $nowServing ? $nowServing->token_number : null;

        // Detection Logic for Notifications
        $shouldNotify = false;
        $reason = '';

        if ($this->lastTokenNumber !== $currentToken) {
            if ($currentToken) {
                $shouldNotify = true;
                $reason = 'next';
            }
            $this->lastTokenNumber = $currentToken;
            $this->lastStatus = $currentStatus;
        } elseif ($this->lastStatus !== $currentStatus) {
            if ($currentStatus === 'hold' || $currentStatus === 'serving') {
                $shouldNotify = true;
                $reason = $currentStatus === 'hold' ? 'hold' : 'continue';
            }
            $this->lastStatus = $currentStatus;
        }

        if ($shouldNotify) {
            $this->dispatch('notify', type: $reason);
        }

        $nextTokens = Queue::with('appointment')
            ->whereHas('appointment', function ($query) use ($doctorId, $today) {
                $query->where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', $today);
            })
            ->where('status', 'waiting')
            ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
            ->take(3)
            ->get();

        $todaysAppointments = Appointment::with(['doctor.user'])
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time', 'asc')
            ->get();

        $waitingCount = Queue::whereHas('appointment', function ($query) use ($doctorId, $today) {
            $query->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $today);
        })
            ->where('status', 'waiting')
            ->count();

        $isDoctorOnHold = $this->isDoctorOnHold;

        $doctorSchedules = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('day_of_week', $today->dayOfWeek)
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($schedule) use ($today) {
                $schedule->booked_count = Appointment::where('doctor_id', $schedule->doctor_id)
                    ->whereDate('appointment_date', $today)
                    ->whereTime('start_time', '>=', $schedule->start_time)
                    ->whereTime('start_time', '<', $schedule->end_time)
                    ->count();

                return $schedule;
            });

        return view('livewire.doctor.dashboard', [
            'totalAppointments' => $totalAppointments,
            'checkedIn' => $checkedIn,
            'pendingArrivals' => $pendingArrivals,
            'waitingCount' => $waitingCount,
            'revenueToday' => $revenueToday,
            'completedToday' => $completedToday,
            'nowServing' => $nowServing,
            'nextTokens' => $nextTokens,
            'todaysAppointments' => $todaysAppointments,
            'isDoctorOnHold' => $isDoctorOnHold,
            'doctorSchedules' => $doctorSchedules,
        ]);
    }
}
