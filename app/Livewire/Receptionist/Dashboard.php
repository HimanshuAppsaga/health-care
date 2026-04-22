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
            ->orderBy('id', 'asc')
            ->take(3)
            ->get();

        $todaysAppointments = Appointment::with(['doctor.user'])
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time', 'asc')
            ->get();

        $waitlist = Queue::with('appointment')
            ->whereDate('created_at', $today)
            ->where('status', 'waiting')
            ->orderBy('id', 'asc')
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
