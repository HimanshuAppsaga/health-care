<div>
    <style>
        .bg-background { background-color: #fcf9f8; }
        .text-on-surface { color: #1c1b1b; }
        .bg-primary-container { background-color: #5200cc; }
        .text-on-primary { color: #ffffff; }
    </style>


    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 mt-6 px-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between group hover:border-[#5200cc]/30 transition-all">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Total Appointments</p>
                <h3 class="text-3xl font-black text-[#5200cc]">{{ $totalAppointments }}</h3>
            </div>
            <div class="w-14 h-14 bg-[#ede7ff] rounded-2xl flex items-center justify-center text-[#5200cc]">
                <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">calendar_month</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between hover:border-orange-200 transition-all">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Completed Today</p>
                <h3 class="text-3xl font-black text-orange-500">{{ $completedToday }}</h3>
                <p class="text-xs text-gray-400 font-medium mt-2 italic">Done for today</p>
            </div>
            <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-500">
                <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">task_alt</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between hover:border-[#0fbda6]/30 transition-all">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Pending Patients</p>
                <h3 class="text-3xl font-black text-[#0fbda6]">{{ $waitingCount }}</h3>
            </div>
            <div class="w-14 h-14 bg-[#e6fffb] rounded-2xl flex items-center justify-center text-[#0fbda6]">
                <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">pending_actions</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-8 px-8 pb-12">
        <!-- Center Column: Live Queue -->
        <div class="col-span-12 lg:col-span-8 flex flex-col gap-8">
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-[#fcf9f8]">
                    <h2 class="text-xl font-black flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $isDoctorOnHold ? 'bg-orange-500' : 'bg-red-500 animate-pulse' }}"></span>
                        Live Queue Manager
                    </h2>
                    <div class="flex items-center gap-4">
                        @if($isDoctorOnHold)
                            <div class="flex items-center gap-2 bg-orange-100 text-orange-600 px-4 py-2 rounded-xl border border-orange-200 animate-pulse">
                                <span class="material-symbols-outlined text-lg">pause_circle</span>
                                <span class="text-xs font-black uppercase tracking-tighter">Doctor on Hold</span>
                            </div>
                        @endif
                        
                        <div class="flex flex-col items-end">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Select Doctor</label>
                            <select wire:model.live="selectedDoctorId" class="bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm font-bold text-[#5200cc] focus:ring-2 focus:ring-[#5200cc]/20 focus:border-[#5200cc] outline-none shadow-sm transition-all cursor-pointer">
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}">Dr. {{ $doctor->user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="p-8 flex flex-col items-center justify-center text-center">
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Now Serving</p>
                    <div class="relative">
                        <div class="absolute -inset-8 bg-[#0fbda6]/10 blur-3xl rounded-full"></div>
                        <div class="relative text-9xl font-black {{ $nowServing && $nowServing->status === 'hold' ? 'text-orange-500' : 'text-[#0fbda6]' }} tracking-tighter mb-4">
                            {{ $nowServing ? $nowServing->token_number : '--' }}
                        </div>
                        @if($nowServing && $nowServing->status === 'hold')
                            <div class="absolute top-0 right-0 -mr-12 bg-orange-500 text-white text-xs font-black px-3 py-1 rounded-full shadow-lg animate-bounce">
                                ON HOLD
                            </div>
                        @endif
                    </div>
                    <h4 class="text-2xl font-bold text-[#1c1b1b] mb-8">
                        {{ $nowServing ? ($nowServing->appointment->name ?? 'Unknown Patient') : 'No Patient Assigned' }}
                    </h4>

                    @if($isDoctorOnHold)
                        <div class="mb-8 px-6 py-2 bg-orange-50 text-orange-600 rounded-full border border-orange-100 flex items-center gap-2 animate-pulse mx-auto">
                            <span class="material-symbols-outlined text-sm">info</span>
                            <span class="text-xs font-black uppercase tracking-tight">Session is paused by doctor</span>
                        </div>
                    @endif
                    
                    <div class="flex items-center gap-3 mb-10">
                        <p class="text-sm font-bold text-gray-400 mr-2">NEXT TOKENS:</p>
                        @forelse($nextTokens as $token)
                            <div class="flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-600 rounded-full shadow-sm">
                                <span class="font-black text-lg">{{ $token->token_number }}</span>
                                <span class="text-xs font-bold border-l border-gray-300 pl-2 max-w-[80px] truncate">{{ $token->appointment->name ?? 'Unknown' }}</span>
                            </div>
                        @empty
                            <span class="px-4 py-2 bg-gray-100 text-gray-600 rounded-full font-black text-lg">None</span>
                        @endforelse
                    </div>
                    
                    <div class="flex gap-4 w-full max-w-2xl">
                        <button wire:click="callNextPatient" 
                            @if($nowServing || $isDoctorOnHold) disabled @endif 
                            class="flex-1 py-4 bg-[#0fbda6] text-white rounded-2xl font-black text-lg shadow-lg shadow-[#0fbda6]/30 transition-all flex items-center justify-center gap-2 
                            @if($nowServing || $isDoctorOnHold) opacity-50 cursor-not-allowed @else hover:bg-[#0da692] active:scale-95 @endif">
                            <span class="material-symbols-outlined">campaign</span>
                            Call Next Patient
                        </button>
                        <button wire:click="transferToken" 
                            @if(!$nowServing || $isDoctorOnHold) disabled @endif 
                            class="flex-1 py-4 bg-orange-50 border-2 border-orange-200 text-orange-500 rounded-2xl font-black text-lg transition-all flex items-center justify-center gap-2 
                            @if(!$nowServing || $isDoctorOnHold) opacity-50 cursor-not-allowed @else hover:border-orange-500 hover:text-orange-600 active:scale-95 @endif">
                            <span class="material-symbols-outlined">forward_5</span>
                            Transfer Token
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-black text-[#1c1b1b]">Today's Appointments</h3>
                    <a href="{{ route('appointments.index') }}" wire:navigate class="text-sm font-black text-[#5200cc] hover:underline flex items-center gap-1">
                        View All History
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Schedule Time</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Patient Name</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Token</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($todaysAppointments as $appointment)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-[#5200cc]">
                                    @php
                                        $session = $doctorSchedules->first(function($s) use ($appointment) {
                                            return $appointment->start_time >= $s->start_time && $appointment->start_time < $s->end_time;
                                        });
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base">schedule</span>
                                        @if($session)
                                            {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($appointment->start_time)->format('h:i A') }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium text-[#1c1b1b]">{{ $appointment->name ?? 'Unknown' }}</td>
                                <td class="px-6 py-4 text-gray-600 font-bold">{{ $appointment->token ?? '--' }}</td>
                                <td class="px-6 py-4 text-right">
                                    @if($appointment->status === 'confirmed')
                                        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Confirmed</span>
                                    @elseif($appointment->status === 'pending')
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">Pending</span>
                                    @elseif($appointment->status === 'cancelled')
                                        <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">Cancelled</span>
                                    @else
                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-full">{{ ucfirst($appointment->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500 font-medium">No appointments for today.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Quick Actions & Alerts -->
        <div class="col-span-12 lg:col-span-4 space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-black text-[#1c1b1b] mb-6">Quick Actions</h3>
                <div class="space-y-4">
                    @php
                        $hasActiveSchedule = $doctorSchedules->contains(function($s) {
                            $now = now();
                            return \Carbon\Carbon::parse($s->start_time)->isBefore($now) && \Carbon\Carbon::parse($s->end_time)->isAfter($now);
                        });
                        $nextSchedule = $doctorSchedules->first(function($s) {
                            return \Carbon\Carbon::parse($s->start_time)->isAfter(now());
                        });
                    @endphp
                    
                    <a href="{{ route('receptionist.book-appointment') }}" wire:navigate class="w-full flex items-center gap-4 p-4 rounded-2xl bg-[#e6fffb] text-[#0fbda6] font-bold hover:bg-[#0fbda6] hover:text-white transition-all group shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-white/50 flex items-center justify-center group-hover:bg-[#0da692]">
                            <span class="material-symbols-outlined">calendar_add_on</span>
                        </div>
                        <div class="flex flex-col">
                            <span>Book Appointment</span>
                        </div>
                    </a>

                    <!-- Test Sound Button -->
                    <button type="button" wire:click="$dispatch('notify', { type: 'test' })" class="w-full flex items-center gap-4 p-4 rounded-2xl bg-[#ede7ff] text-[#5200cc] font-bold hover:bg-[#5200cc] hover:text-white transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-white/50 flex items-center justify-center group-hover:bg-[#5200cc]">
                            <span class="material-symbols-outlined">volume_up</span>
                        </div>
                        <span>Test Notification Sound</span>
                    </button>
                </div>
            </div>

            <!-- Doctor Schedules Section -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-black text-[#1c1b1b]">Today's Schedule</h3>
                    <span class="px-2 py-1 bg-[#5200cc]/10 text-[#5200cc] text-[10px] font-black uppercase rounded-md tracking-tighter">Availability</span>
                </div>
                <div class="space-y-4">
                    @forelse($doctorSchedules as $schedule)
                        @php
                            $isCompleted = \Carbon\Carbon::parse($schedule->end_time)->isBefore(now());
                            $isActive = \Carbon\Carbon::parse($schedule->start_time)->isBefore(now()) && \Carbon\Carbon::parse($schedule->end_time)->isAfter(now());
                        @endphp
                        <div class="p-4 rounded-xl {{ $isCompleted ? 'bg-gray-100 opacity-60' : 'bg-gray-50' }} border {{ $isActive ? 'border-[#0fbda6] bg-[#e6fffb]/30' : 'border-gray-100' }} group hover:border-[#5200cc]/30 transition-all">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-1 text-gray-500 text-sm">
                                    <span class="material-symbols-outlined text-sm">schedule</span>
                                    <span class="font-bold">{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</span>
                                </div>
                                @if($isCompleted)
                                    <span class="text-[10px] font-black uppercase text-gray-400">Completed</span>
                                @elseif($isActive)
                                    <span class="flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#0fbda6] animate-pulse"></span>
                                        <span class="text-[10px] font-black uppercase text-[#0fbda6]">Active</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-400">
                            <span class="material-symbols-outlined text-4xl mb-2 block">event_busy</span>
                            <p class="text-xs font-bold uppercase tracking-widest">No doctors scheduled today</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script data-navigate-once>
    console.log('Receptionist Sound Script Loaded');
    
    window.addEventListener('notify', event => {
        // Only play sound if the user is a receptionist
        @if(auth()->user()->hasRole('receptionist'))
            console.log('Sound notification triggered:', event.detail);
            
            // Play the sound - using the exact pattern from your example
            const soundUrl = 'https://assets.mixkit.co/active_storage/sfx/2857/2857-preview.mp3';
            const audio = new Audio(soundUrl);
            audio.play().catch(error => {
                console.error("Audio play failed:", error);
            });

            // Show a simple visual toast as well
            const type = (event.detail && event.detail.type) ? event.detail.type : 'notification';
            const toast = document.createElement('div');
            toast.innerText = `🔔 Action: ${type.toUpperCase()}`;
            toast.style.cssText = "position:fixed; bottom:20px; right:20px; background:#5200cc; color:white; padding:12px 24px; border-radius:12px; z-index:9999; font-weight:bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1);";
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        @endif
    });
</script>
