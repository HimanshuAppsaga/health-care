<div>
    <!-- No inline overrides needed, using global app.css theme -->


    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 mt-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-surface p-6 rounded-2xl clinical-shadow border border-outline-variant flex items-center justify-between group hover:border-primary/30 transition-all">
            <div>
                <p class="text-sm font-medium text-outline mb-1">Total Appointments</p>
                <h3 class="text-3xl font-black text-primary">{{ $totalAppointments }}</h3>
            </div>
            <div class="w-14 h-14 bg-primary-container/20 rounded-2xl flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">calendar_month</span>
            </div>
        </div>

        <div class="bg-surface p-6 rounded-2xl clinical-shadow border border-outline-variant flex items-center justify-between hover:border-error-container transition-all">
            <div>
                <p class="text-sm font-medium text-outline mb-1">Completed Today</p>
                <h3 class="text-3xl font-black text-secondary">{{ $completedToday }}</h3>
                <p class="text-xs text-outline font-medium mt-2 italic">Done for today</p>
            </div>
            <div class="w-14 h-14 bg-secondary-container/20 rounded-2xl flex items-center justify-center text-secondary">
                <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">task_alt</span>
            </div>
        </div>

        <div class="bg-surface p-6 rounded-2xl clinical-shadow border border-outline-variant flex items-center justify-between hover:border-tertiary/30 transition-all">
            <div>
                <p class="text-sm font-medium text-outline mb-1">Pending Patients</p>
                <h3 class="text-3xl font-black text-tertiary">{{ $waitingCount }}</h3>
            </div>
            <div class="w-14 h-14 bg-tertiary-container/20 rounded-2xl flex items-center justify-center text-tertiary">
                <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">pending_actions</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-4 sm:gap-6 lg:gap-8 px-4 sm:px-6 lg:px-8 pb-12">
        <!-- Center Column: Live Queue -->
        <div class="col-span-12 lg:col-span-8 flex flex-col gap-4 sm:gap-6 lg:gap-8">
            <div class="grid grid-cols-1 {{ count($queuesData) > 1 ? 'md:grid-cols-2' : '' }} gap-4 sm:gap-6">
                @forelse($queuesData as $doctorId => $queue)
                    @php
                        $isDoctorOnHold = $queue['isDoctorOnHold'];
                        $nowServing = $queue['nowServing'];
                        $nextTokens = $queue['nextTokens'];
                        $doctor = $queue['doctor'];
                        $transferDepth = $queue['transferDepth'];
                    @endphp
                    <div class="bg-surface rounded-[2rem] clinical-shadow border border-outline-variant overflow-hidden flex flex-col">
                        <div class="p-4 sm:p-5 border-b border-outline-variant flex justify-between items-center bg-surface-container-low">
                            <h2 class="text-lg font-black flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $isDoctorOnHold ? 'bg-amber-500' : 'bg-red-500 animate-pulse' }}"></span>
                                Dr. {{ $doctor->user->name }}
                            </h2>
                            @if($isDoctorOnHold)
                                <div class="flex items-center gap-1 bg-orange-100 text-orange-600 px-2 py-1 rounded-lg border border-orange-200 animate-pulse">
                                    <span class="material-symbols-outlined text-sm">pause_circle</span>
                                    <span class="text-[10px] font-black uppercase tracking-tighter">On Hold</span>
                                </div>
                            @endif
                        </div>
                        <div class="p-6 flex flex-col items-center justify-center text-center flex-1">
                            <p class="text-[10px] font-bold text-outline-variant uppercase tracking-widest mb-2">Now Serving</p>
                            <div class="relative">
                                <div class="absolute -inset-4 bg-secondary/10 blur-2xl rounded-full"></div>
                                <div class="relative text-7xl font-black {{ $nowServing && $nowServing->status?->value === 'hold' ? 'text-amber-500' : 'text-secondary' }} tracking-tighter mb-2">
                                    {{ $nowServing ? $nowServing->token_number : '--' }}
                                </div>
                                @if($nowServing && $nowServing->status?->value === 'hold')
                                    <div class="absolute top-0 right-0 -mr-6 bg-amber-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-lg animate-bounce">
                                        HOLD
                                    </div>
                                @endif
                            </div>
                            <h4 class="text-lg font-bold text-on-background mb-4 line-clamp-1">
                                {{ $nowServing ? ($nowServing->appointment->name ?? 'Unknown Patient') : 'No Patient' }}
                            </h4>

                            @if($isDoctorOnHold)
                                <div class="mb-4 px-4 py-1.5 bg-orange-50 text-orange-600 rounded-full border border-orange-100 flex items-center gap-2 animate-pulse mx-auto">
                                    <span class="material-symbols-outlined text-xs">info</span>
                                    <span class="text-[10px] font-black uppercase tracking-tight">Session Paused</span>
                                </div>
                            @endif
                            
                            <div class="flex flex-wrap items-center justify-center gap-2 mb-6 w-full">
                                <p class="text-[10px] font-bold text-outline-variant mr-1">NEXT:</p>
                                @forelse($nextTokens as $token)
                                    <div class="flex items-center gap-1 px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full border border-outline-variant/30">
                                        <span class="font-black text-sm text-primary">{{ $token->token_number }}</span>
                                    </div>
                                @empty
                                    <span class="text-xs text-outline font-medium bg-surface-container-low px-3 py-1 rounded-full">None</span>
                                @endforelse
                            </div>
                            
                            <div class="flex gap-3 w-full mt-auto">
                                <button wire:click="callNextPatient({{ $doctorId }})" 
                                    @if($nowServing || $isDoctorOnHold) disabled @endif 
                                    class="flex-1 py-3 bg-secondary text-white rounded-xl font-black text-sm clinical-shadow transition-all flex items-center justify-center gap-1 
                                    @if($nowServing || $isDoctorOnHold) opacity-50 cursor-not-allowed @else hover:bg-secondary-container hover:text-on-secondary-container active:scale-95 @endif">
                                    <span class="material-symbols-outlined text-base">campaign</span>
                                    Call Next
                                </button>

                                <button wire:click="transferToken({{ $doctorId }})" 
                                    @if(!$nowServing || $isDoctorOnHold) disabled @endif 
                                    class="flex-1 py-3 bg-surface border-2 border-amber-200 text-amber-500 rounded-xl font-black text-sm transition-all flex flex-row items-center justify-center gap-1
                                    @if(!$nowServing || $isDoctorOnHold) opacity-50 cursor-not-allowed @else hover:border-amber-500 hover:text-amber-600 active:scale-95 @endif">
                                    <div class="flex flex-col items-center leading-none">
                                        <span class="material-symbols-outlined text-base">forward_media</span>
                                        <span class="text-[8px] font-black mt-0.5">+{{ $transferDepth }}</span>
                                    </div>
                                    Transfer
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-surface rounded-2xl clinical-shadow border border-outline-variant p-8 text-center text-outline-variant {{ count($queuesData) > 1 ? 'md:col-span-2' : '' }}">
                        <span class="material-symbols-outlined text-4xl mb-2">sentiment_dissatisfied</span>
                        <p class="font-bold">No active doctors found.</p>
                    </div>
                @endforelse
            </div>

            <!-- Table Section -->
            <div class="bg-surface rounded-2xl clinical-shadow border border-outline-variant overflow-hidden">
                <div class="p-6 border-b border-outline-variant/10 flex justify-between items-center">
                    <h3 class="text-lg font-black text-on-background">Today's Appointments</h3>
                    <a href="{{ route('appointments.index') }}" wire:navigate class="text-sm font-black text-primary hover:underline flex items-center gap-1">
                        View All History
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                </div>
                <!-- PC & Tablet View (Table) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full min-w-[600px] text-left">
                        <thead class="bg-surface-container-low/50">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-outline uppercase tracking-wider">Patient Name</th>
                                <th class="px-6 py-4 text-xs font-bold text-outline uppercase tracking-wider">Doctor</th>
                                <th class="px-6 py-4 text-xs font-bold text-outline uppercase tracking-wider">Mobile</th>
                                <th class="px-6 py-4 text-xs font-bold text-outline uppercase tracking-wider">Token</th>
                                <th class="px-6 py-4 text-xs font-bold text-outline uppercase tracking-wider text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @forelse($todaysAppointments as $appointment)
                            <tr class="hover:bg-primary-container/10 transition-colors">
                                <td class="px-6 py-4 font-medium text-on-background">{{ $appointment->name ?? 'Unknown' }}</td>
                                <td class="px-6 py-4 text-on-surface-variant font-medium">Dr. {{ $appointment->doctor->user->name ?? '--' }}</td>
                                <td class="px-6 py-4 text-on-surface-variant font-bold">{{ $appointment->phone ?? '--' }}</td>
                                <td class="px-6 py-4 text-on-surface-variant font-bold">{{ $appointment->token ?? '--' }}</td>
                                <td class="px-6 py-4 text-right">
                                    @if($appointment->status->value === 'pending')
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">Pending</span>
                                    @else
                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-full">{{ ucfirst($appointment->status->value) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-outline font-medium">No appointments for today.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile View (Cards) -->
                <div class="block md:hidden space-y-4 p-4">
                    @forelse($todaysAppointments as $appointment)
                    <div class="bg-surface p-5 rounded-2xl border border-outline-variant hover:border-primary/30 transition-all flex flex-col gap-3 relative overflow-hidden">
                        <!-- Left Status Accent Bar -->
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $appointment->status->value === 'pending' ? 'bg-amber-500' : 'bg-primary' }}"></div>
                        
                        <div class="flex justify-between items-start pl-2">
                            <div>
                                <p class="text-xs font-black text-outline uppercase tracking-widest mb-1">Patient Name</p>
                                <h4 class="text-base font-bold text-on-background">{{ $appointment->name ?? 'Unknown' }}</h4>
                                <p class="text-[10px] font-bold text-outline mt-1">Dr. {{ $appointment->doctor->user->name ?? '--' }}</p>
                            </div>
                            <div>
                                <span class="inline-block px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-lg font-black text-xs border border-outline-variant">
                                    Token #{{ $appointment->token ?? '--' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center pl-2 pt-2 border-t border-outline-variant/10">
                            <div>
                                <p class="text-[10px] font-black text-outline uppercase tracking-widest mb-0.5">Mobile</p>
                                @if($appointment->phone)
                                    <a href="tel:{{ $appointment->phone }}" class="text-sm font-bold text-primary hover:underline flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">phone</span>
                                        {{ $appointment->phone }}
                                    </a>
                                @else
                                    <span class="text-sm font-bold text-on-surface-variant">--</span>
                                @endif
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-outline uppercase tracking-widest mb-0.5 text-right">Status</p>
                                @if($appointment->status->value === 'pending')
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">Pending</span>
                                @else
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-full">{{ ucfirst($appointment->status->value) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="py-8 text-center text-outline font-medium bg-surface rounded-2xl border border-outline-variant">
                        No appointments for today.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Quick Actions & Alerts -->
        <div class="col-span-12 lg:col-span-4 space-y-6">
            <div class="bg-surface p-6 rounded-2xl clinical-shadow border border-outline-variant">
                <h3 class="text-lg font-black text-on-background mb-6">Quick Actions</h3>
                <div class="space-y-4">
                    <a href="{{ route('receptionist.book-appointment') }}" wire:navigate class="w-full flex items-center gap-4 p-4 rounded-2xl bg-tertiary-container/20 text-tertiary font-bold hover:bg-tertiary hover:text-on-tertiary transition-all group clinical-shadow">
                        <div class="w-10 h-10 rounded-xl bg-surface flex items-center justify-center group-hover:bg-tertiary/20">
                            <span class="material-symbols-outlined">calendar_add_on</span>
                        </div>
                        <div class="flex flex-col">
                            <span>Book Appointment</span>
                        </div>
                    </a>

                    <!-- Test Sound Button -->
                    {{-- <button type="button" wire:click="$dispatch('notify', { type: 'test' })" class="w-full flex items-center gap-4 p-4 rounded-2xl bg-primary-container/20 text-primary font-bold hover:bg-primary hover:text-on-primary transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-surface flex items-center justify-center group-hover:bg-primary/20">
                            <span class="material-symbols-outlined">volume_up</span>
                        </div>
                        <span>Test Notification Sound</span>
                    </button> --}}
                </div>
            </div>

            <!-- Doctor Schedules Section -->
            <div class="bg-surface p-6 rounded-2xl clinical-shadow border border-outline-variant">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-black text-on-background">Today's Schedule</h3>
                    <span class="px-2 py-1 bg-primary-container/20 text-primary text-[10px] font-black uppercase rounded-md tracking-tighter">Availability</span>
                </div>
                <div class="space-y-6">
                    @forelse($doctorSchedules as $doctorId => $schedules)
                        @php $doctor = $schedules->first()->doctor; @endphp
                        <div>
                            <h4 class="text-sm font-bold text-outline mb-3 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary"></span>
                                Dr. {{ $doctor->user->name }}
                            </h4>
                            <div class="space-y-4">
                                @foreach($schedules as $schedule)
                                    @php
                                        $isCompleted = \Carbon\Carbon::parse($schedule->end_time)->isBefore(now());
                                        $isActive = \Carbon\Carbon::parse($schedule->start_time)->isBefore(now()) && \Carbon\Carbon::parse($schedule->end_time)->isAfter(now());
                                    @endphp
                                    <div class="p-4 rounded-xl {{ $isCompleted ? 'bg-surface-container-low opacity-60' : 'bg-surface-container-low' }} border {{ $isActive ? 'border-tertiary bg-tertiary-container/10' : 'border-outline-variant/30' }} group hover:border-primary/30 transition-all">
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
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-outline-variant">
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
            toast.style.cssText = "position:fixed; bottom:20px; right:20px; background:#005bb0; color:white; padding:12px 24px; border-radius:12px; z-index:9999; font-weight:bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1);";
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        @endif
    });
</script>
