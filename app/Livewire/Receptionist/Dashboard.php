<?php

namespace App\Livewire\Receptionist;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Queue;
use App\Events\QueueUpdated;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Receptionist Dashboard | ClinicOS')]
class Dashboard extends Component
{
    public $lastTokenNumber;

    public $lastIsOnHoldStatus;

    public $lastStatus;

    public function getListeners()
    {
        return [
            "echo:queue-updates." . auth()->user()->clinic_id . ",QueueUpdated" => '$refresh',
        ];
    }

    public function mount()
    {
        $today = Carbon::today();
        $nowServing = Queue::whereHas('appointment', function($query) use ($today) {
            $query->where('clinic_id', auth()->user()->clinic_id)
                  ->whereDate('appointment_date', $today);
        })
        ->whereIn('status', ['serving', 'hold'])
        ->first();

        $this->lastTokenNumber = $nowServing ? $nowServing->token_number : null;
        $this->lastStatus = $nowServing ? $nowServing->status : null;

        $this->lastIsOnHoldStatus = Doctor::where('clinic_id', auth()->user()->clinic_id)
            ->where('is_on_hold', true)
            ->exists();
    }

    public function callNextPatient()
    {
        $isDoctorOnHold = Doctor::where('clinic_id', auth()->user()->clinic_id)->where('is_on_hold', true)->exists();
        if ($isDoctorOnHold) {
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
        $isDoctorOnHold = Doctor::where('clinic_id', auth()->user()->clinic_id)->where('is_on_hold', true)->exists();
        if ($isDoctorOnHold) {
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

    public function transferToken()
    {
        $isDoctorOnHold = Doctor::where('clinic_id', auth()->user()->clinic_id)->where('is_on_hold', true)->exists();
        if ($isDoctorOnHold) {
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
            ->whereHas('appointment', function ($query) use ($today) {
                $query->where('clinic_id', auth()->user()->clinic_id)
                    ->whereDate('appointment_date', $today);
            })
            ->whereIn('status', ['serving', 'hold'])
            ->first();

        $currentStatus = $nowServing ? $nowServing->status : null;
        $currentToken = $nowServing ? $nowServing->token_number : null;

        $isDoctorOnHold = Doctor::where('clinic_id', auth()->user()->clinic_id)
            ->where('is_on_hold', true)
            ->exists();

        // Detection Logic
        $shouldPlaySound = false;
        $reason = '';

        // 1. Check for Next Patient (Token change)
        if ($this->lastTokenNumber !== $currentToken) {
            if ($currentToken) {
                $shouldPlaySound = true;
                $reason = 'next';
            }
            $this->lastTokenNumber = $currentToken;
            $this->lastStatus = $currentStatus;
        }
        // 2. Check for Status change on same token
        elseif ($this->lastStatus !== $currentStatus) {
            if ($currentStatus === 'hold' || $currentStatus === 'serving') {
                $shouldPlaySound = true;
                $reason = $currentStatus === 'hold' ? 'hold' : 'continue';
            }
            $this->lastStatus = $currentStatus;
        }

        // 3. Fallback: Check for Doctor table Hold status (if no active queue or other changes)
        if (! $shouldPlaySound && $this->lastIsOnHoldStatus !== $isDoctorOnHold) {
            $shouldPlaySound = true;
            $reason = $isDoctorOnHold ? 'hold' : 'continue';
            $this->lastIsOnHoldStatus = $isDoctorOnHold;
        }

        if ($shouldPlaySound) {
            \Log::info("Sound: Dispatching $reason event via notify");
            $this->dispatch('notify', type: $reason);
        }

        $this->lastIsOnHoldStatus = $isDoctorOnHold; // Keep track regardless

        $nextTokens = Queue::with('appointment')
            ->whereHas('appointment', function ($query) use ($today) {
                $query->where('clinic_id', auth()->user()->clinic_id)
                    ->whereDate('appointment_date', $today);
            })
            ->where('status', 'waiting')
            ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
            ->take(3)
            ->get();

        $todaysAppointments = Appointment::with(['doctor.user'])
            ->where('clinic_id', auth()->user()->clinic_id)
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time', 'asc')
            ->get();

        $waitingCount = Queue::whereHas('appointment', function ($query) use ($today) {
            $query->where('clinic_id', auth()->user()->clinic_id)
                ->whereDate('appointment_date', $today);
        })
            ->where('status', 'waiting')
            ->count();

        return view('livewire.receptionist.dashboard', [
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
