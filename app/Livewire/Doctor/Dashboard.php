<?php

namespace App\Livewire\Doctor;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Payment;
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

    public function mount()
    {
        $doctor = auth()->user()->doctor;
        $this->isDoctorOnHold = $doctor ? (bool) $doctor->is_on_hold : false;
    }
    public function callNextPatient()
    {
        if ($this->isDoctorOnHold) {
            return;
        }

        $today = Carbon::today();

        $current = Queue::whereDate('created_at', $today)->where('status', 'serving')->first();
        if ($current) {
            $current->update(['status' => 'completed']);
        }

        $next = Queue::whereDate('created_at', $today)
            ->where('status', 'waiting')
            ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
            ->first();
        if ($next) {
            $next->update(['status' => 'serving', 'called_at' => now()]);
        }
    }

    public function markAsDone()
    {
        if ($this->isDoctorOnHold) {
            return;
        }

        $today = Carbon::today();
        $current = Queue::whereDate('created_at', $today)->whereIn('status', ['serving', 'hold'])->first();
        if ($current) {
            $current->update(['status' => 'completed']);
            if ($current->appointment) {
                $current->appointment->update(['status' => 'completed']);
            }
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
            $current = Queue::whereDate('created_at', $today)
                ->whereIn('status', ['serving', 'hold'])
                ->whereHas('appointment', function($query) use ($doctor) {
                    $query->where('doctor_id', $doctor->id);
                })
                ->first();

            if ($current) {
                $current->update(['status' => $this->isDoctorOnHold ? 'hold' : 'serving']);
            }
        }
    }

    public function transferToken()
    {
        if ($this->isDoctorOnHold) {
            return;
        }

        $today = Carbon::today();
        $current = Queue::whereDate('created_at', $today)->where('status', 'serving')->first();
        if ($current) {
            $currentTokenNum = (int) $current->token_number;
            $newTokenStr = (string) ($currentTokenNum + 6);

            // Shift the next 6 tokens down by 1
            $nextTokensToShift = Queue::whereDate('created_at', $today)
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
        }
    }

    public function render()
    {
        $today = Carbon::today();
        $doctor = auth()->user()->doctor;
        $doctorId = $doctor->id ?? 0;

        $totalAppointments = Appointment::whereDate('appointment_date', $today)
            ->where('doctor_id', $doctorId)
            ->count();

        $checkedIn = Queue::whereDate('created_at', $today)
            ->whereIn('status', ['waiting', 'serving', 'completed'])
            ->whereHas('appointment', fn($q) => $q->where('doctor_id', $doctorId))
            ->count();

        $pendingArrivals = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'confirmed')
            ->where('doctor_id', $doctorId)
            ->count();

        $completedToday = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'completed')
            ->where('doctor_id', $doctorId)
            ->count();

        $revenueToday = 0;
        if (class_exists(Payment::class)) {
            $revenueToday = Payment::whereDate('paid_at', $today)
                ->where('status', 'paid')
                ->whereHas('invoice.appointment', fn($q) => $q->where('doctor_id', $doctorId))
                ->sum('amount');
        } elseif (class_exists(Invoice::class)) {
            $revenueToday = Invoice::whereDate('issued_at', $today)
                ->where('status', 'paid')
                ->whereHas('appointment', fn($q) => $q->where('doctor_id', $doctorId))
                ->sum('total_amount');
        }

        $nowServing = Queue::with('appointment')
            ->whereDate('created_at', $today)
            ->whereIn('status', ['serving', 'hold'])
            ->whereHas('appointment', fn($q) => $q->where('doctor_id', $doctorId))
            ->first();

        $nextTokens = Queue::with('appointment')
            ->whereDate('created_at', $today)
            ->where('status', 'waiting')
            ->whereHas('appointment', fn($q) => $q->where('doctor_id', $doctorId))
            ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
            ->take(3)
            ->get();

        $todaysAppointments = Appointment::with(['doctor.user'])
            ->whereDate('appointment_date', $today)
            ->where('doctor_id', $doctorId)
            ->orderBy('start_time', 'asc')
            ->get();

        $waitingCount = Queue::whereDate('created_at', $today)
            ->where('status', 'waiting')
            ->whereHas('appointment', fn($q) => $q->where('doctor_id', $doctorId))
            ->count();

        $isDoctorOnHold = $this->isDoctorOnHold;

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
        ]);
    }
}
