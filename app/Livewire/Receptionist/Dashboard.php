<?php

namespace App\Livewire\Receptionist;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Appointment;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.app')]
#[Title('Receptionist Dashboard | ClinicOS')]
class Dashboard extends Component
{
    public function callNextPatient()
    {
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
        $today = Carbon::today();
        $current = Queue::whereDate('created_at', $today)->where('status', 'serving')->first();
        if ($current) {
            $current->update(['status' => 'completed']);
            if ($current->appointment) {
                $current->appointment->update(['status' => 'completed']);
            }
        }
    }

    public function transferToken()
    {
        $today = Carbon::today();
        $current = Queue::whereDate('created_at', $today)->where('status', 'serving')->first();
        if ($current) {
            $currentTokenNum = (int) $current->token_number;
            $newTokenStr = (string) ($currentTokenNum + 5);
            
            $current->update([
                'token_number' => $newTokenStr,
                'status' => 'waiting'
            ]);
            
            if ($current->appointment) {
                $current->appointment->update(['token' => $newTokenStr]);
            }
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

        $revenueToday = 0;
        if (class_exists(\App\Models\Payment::class)) {
            $revenueToday = \App\Models\Payment::whereDate('paid_at', $today)
                ->where('status', 'paid')
                ->sum('amount');
        } elseif (class_exists(\App\Models\Invoice::class)) {
            $revenueToday = \App\Models\Invoice::whereDate('issued_at', $today)
                ->where('status', 'paid')
                ->sum('total_amount');
        }

        $nowServing = Queue::with('appointment')
            ->whereDate('created_at', $today)
            ->where('status', 'serving')
            ->first();

        $nextTokens = Queue::whereDate('created_at', $today)
            ->where('status', 'waiting')
            ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
            ->take(3)
            ->get();

        $todaysAppointments = Appointment::with(['doctor.user'])
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time', 'asc')
            ->get();

        $waitlist = Queue::with('appointment')
            ->whereDate('created_at', $today)
            ->where('status', 'waiting')
            ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
            ->take(3)
            ->get();

        return view('livewire.receptionist.dashboard', [
            'totalAppointments' => $totalAppointments,
            'checkedIn' => $checkedIn,
            'pendingArrivals' => $pendingArrivals,
            'revenueToday' => $revenueToday,
            'nowServing' => $nowServing,
            'nextTokens' => $nextTokens,
            'todaysAppointments' => $todaysAppointments,
            'waitlist' => $waitlist,
        ]);
    }
}
