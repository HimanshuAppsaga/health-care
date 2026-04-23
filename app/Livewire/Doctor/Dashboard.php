<?php

namespace App\Livewire\Doctor;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Queue;
use App\Events\QueueUpdated;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Doctor Dashboard | ClinicOS')]
class Dashboard extends Component
{
    public bool $isDoctorOnHold = false;

    public function getListeners()
    {
        $clinicId = auth()->user()->clinic_id;
        return [
            "echo:queue-updates.{$clinicId},QueueUpdated" => '$refresh',
        ];
    }

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
            broadcast(new QueueUpdated(auth()->user()->clinic_id, 'next'))->toOthers();
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
            broadcast(new QueueUpdated(auth()->user()->clinic_id, 'completed'))->toOthers();
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
                ->first();

            if ($current) {
                $current->update(['status' => $this->isDoctorOnHold ? 'hold' : 'serving']);
            }
            broadcast(new QueueUpdated(auth()->user()->clinic_id, $this->isDoctorOnHold ? 'hold' : 'continue'))->toOthers();
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
            broadcast(new QueueUpdated(auth()->user()->clinic_id, 'transfer'))->toOthers();
        }
    }

    public function render()
    {
        $today = Carbon::today();
        $doctor = auth()->user()->doctor;
        $doctorId = $doctor->id ?? 0;

        $totalAppointments = Appointment::whereDate('appointment_date', $today)->count();

        $checkedIn = Queue::whereDate('created_at', $today)
            ->whereIn('status', ['waiting', 'serving', 'completed'])
            ->count();

        $pendingArrivals = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'confirmed')
            ->count();

        $completedToday = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'completed')
            ->count();

        $revenueToday = 0;
        if (class_exists(Payment::class)) {
            $revenueToday = Payment::whereDate('paid_at', $today)
                ->where('status', 'paid')
                ->sum('amount');
        } elseif (class_exists(Invoice::class)) {
            $revenueToday = Invoice::whereDate('issued_at', $today)
                ->where('status', 'paid')
                ->sum('total_amount');
        }

        $nowServing = Queue::with('appointment')
            ->whereDate('created_at', $today)
            ->whereIn('status', ['serving', 'hold'])
            ->first();

        $nextTokens = Queue::with('appointment')
            ->whereDate('created_at', $today)
            ->where('status', 'waiting')
            ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
            ->take(3)
            ->get();

        $todaysAppointments = Appointment::with(['doctor.user'])
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time', 'asc')
            ->get();

        $waitingCount = Queue::whereDate('created_at', $today)
            ->where('status', 'waiting')
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
