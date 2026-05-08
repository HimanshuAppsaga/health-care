<div>
    <!-- No inline overrides needed, using global app.css theme -->

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 mt-6 px-8">
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

    <div class="grid grid-cols-12 gap-8 px-8 pb-12">
        <!-- Center Column: Live Queue -->
        <div class="col-span-12 lg:col-span-8 flex flex-col gap-8">
            <div class="bg-surface rounded-[2rem] clinical-shadow border border-outline-variant overflow-hidden">
                <div class="p-8 border-b border-outline-variant flex justify-between items-center bg-surface-container-low">
                    <h2 class="text-xl font-black flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $isDoctorOnHold ? 'bg-orange-500' : 'bg-red-500 animate-pulse' }}"></span>
                        Live Queue Manager
                        @if($isDoctorOnHold)
                            <span class="ml-2 px-2 py-0.5 bg-orange-100 text-orange-600 text-[10px] font-black uppercase rounded-md border border-orange-200">On Hold</span>
                        @endif
                    </h2>
                    @if($nowServing && $nowServing->appointment && $nowServing->appointment->doctor)
                        <span class="text-xs font-bold text-outline-variant tracking-widest uppercase">{{ $nowServing->appointment->doctor->user->department ?? 'General Dept' }} • Dr. {{ $nowServing->appointment->doctor->user->name ?? 'Unknown' }}</span>
                    @endif
                </div>
                <div class="p-8 flex flex-col items-center justify-center text-center">
                    <p class="text-sm font-bold text-outline-variant uppercase tracking-widest mb-2">Now Serving</p>
                    <div class="relative">
                        <div class="absolute -inset-8 bg-secondary/10 blur-3xl rounded-full"></div>
                        <div class="relative text-9xl font-black {{ $nowServing && $nowServing->status?->value === 'hold' ? 'text-amber-500' : 'text-secondary' }} tracking-tighter mb-4">
                            {{ $nowServing ? $nowServing->token_number : '--' }}
                        </div>
                        @if($nowServing && $nowServing->status?->value === 'hold')
                            <div class="absolute top-0 right-0 -mr-12 bg-orange-500 text-white text-xs font-black px-3 py-1 rounded-full shadow-lg animate-bounce">
                                ON HOLD
                            </div>
                        @endif
                    </div>
                    <h4 class="text-2xl font-bold text-on-background mb-8">
                        {{ $nowServing ? ($nowServing->appointment->name ?? 'Unknown Patient') : 'No Patient Assigned' }}
                    </h4>
                    
                    <div class="flex items-center gap-3 mb-10">
                        <p class="text-sm font-bold text-outline-variant mr-2">NEXT TOKENS:</p>
                        @forelse($nextTokens as $token)
                            <div class="flex items-center gap-2 px-4 py-2 bg-surface-container-low text-on-surface-variant rounded-full clinical-shadow border border-outline-variant/30">
                                <span class="font-black text-lg text-primary">{{ $token->token_number }}</span>
                                <span class="text-xs font-bold border-l border-outline-variant pl-2 max-w-[80px] truncate text-on-surface">{{ $token->appointment->name ?? 'Unknown' }}</span>
                            </div>
                        @empty
                            <span class="px-4 py-2 bg-gray-100 text-gray-600 rounded-full font-black text-lg">None</span>
                        @endforelse
                    </div>
                    
                    <div class="flex gap-4 w-full max-w-2xl">
                        <button wire:click="callNextPatient" 
                            @if($nowServing || $isDoctorOnHold) disabled @endif 
                            class="flex-1 py-4 bg-secondary text-white rounded-2xl font-black text-lg clinical-shadow shadow-secondary/30 transition-all flex items-center justify-center gap-2 
                            @if($nowServing || $isDoctorOnHold) opacity-50 cursor-not-allowed @else hover:bg-secondary-container hover:text-on-secondary-container active:scale-95 @endif">
                            <span class="material-symbols-outlined">campaign</span>
                            Call Next Patient
                        </button>
                        <button wire:click="markAsDone" 
                            @if(!$nowServing || $isDoctorOnHold) disabled @endif 
                            class="flex-1 py-4 bg-surface border-2 border-outline-variant text-on-background rounded-2xl font-black text-lg transition-all flex items-center justify-center gap-2 
                            @if(!$nowServing || $isDoctorOnHold) opacity-50 cursor-not-allowed @else hover:border-primary hover:text-primary active:scale-95 @endif">
                            Mark as Done
                        </button>
                        @if($isDoctorOnHold)
                            <button wire:click="toggleHold" 
                                class="flex-1 py-4 bg-orange-500 text-white rounded-2xl font-black text-lg shadow-lg shadow-orange-500/30 transition-all flex items-center justify-center gap-2 hover:bg-orange-600 active:scale-95">
                                <span class="material-symbols-outlined">play_arrow</span>
                                Continue
                            </button>
                        @else
                            <button wire:click="toggleHold" 
                                class="flex-1 py-4 bg-white border-2 border-orange-200 text-orange-500 rounded-2xl font-black text-lg transition-all flex items-center justify-center gap-2 hover:border-orange-500 hover:text-orange-600 active:scale-95">
                                <span class="material-symbols-outlined">pause</span>
                                Hold
                            </button>
                        @endif
                    </div>
                </div>
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
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-surface-container-low/50">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-outline uppercase tracking-wider">Patient Name</th>
                                <th class="px-6 py-4 text-xs font-bold text-outline uppercase tracking-wider">Token</th>
                                <th class="px-6 py-4 text-xs font-bold text-outline uppercase tracking-wider text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @forelse($todaysAppointments as $appointment)
                            <tr class="hover:bg-primary-container/10 transition-colors">
                                <td class="px-6 py-4 font-medium text-on-background">{{ $appointment->name ?? 'Unknown' }}</td>
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
                                <td colspan="3" class="px-6 py-8 text-center text-outline font-medium">No appointments for today.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Quick Actions & Alerts -->
        <div class="col-span-12 lg:col-span-4 space-y-6">
            <div class="bg-surface p-6 rounded-2xl clinical-shadow border border-outline-variant">
                <h3 class="text-lg font-black text-on-background mb-6">Quick Actions</h3>
                <div class="space-y-4">
                    
                    <a href="{{ route('doctor.profile.edit', auth()->user()->doctor->id) }}" wire:navigate class="w-full flex items-center gap-4 p-4 rounded-2xl bg-primary-container/20 text-primary font-bold hover:bg-primary hover:text-on-primary transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-surface flex items-center justify-center group-hover:bg-primary/20">
                            <span class="material-symbols-outlined">calendar_month</span>
                        </div>
                        <span>Manage Schedule</span>
                    </a>

                </div>
            </div>

            <!-- Doctor Schedules Section -->
            <div class="bg-surface p-6 rounded-2xl clinical-shadow border border-outline-variant">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-black text-on-background">My Schedule Today</h3>
                    <span class="px-2 py-1 bg-primary-container/20 text-primary text-[10px] font-black uppercase rounded-md tracking-tighter">Availability</span>
                </div>
                <div class="space-y-4">
                    @forelse($doctorSchedules as $schedule)
                        <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/30 group hover:border-primary/30 transition-all">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2 text-outline text-sm font-bold">
                                    <span class="material-symbols-outlined text-base">schedule</span>
                                    <span>{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-outline-variant">
                            <span class="material-symbols-outlined text-4xl mb-2 block">event_busy</span>
                            <p class="text-xs font-bold uppercase tracking-widest">No schedule for today</p>
                            <a href="{{ route('doctor.profile.edit', auth()->user()->doctor->id) }}" class="text-primary text-xs font-bold hover:underline mt-2 inline-block">Create Schedule</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script data-navigate-once>
    console.log('Doctor Sound Script Loaded');
    
    window.addEventListener('notify', event => {
        // Only play sound if the user is a doctor
        @if(auth()->user()->hasRole('doctor'))
            console.log('Doctor notification triggered:', event.detail);
            
            // Play the sound
            const soundUrl = 'https://assets.mixkit.co/active_storage/sfx/2857/2857-preview.mp3';
            const audio = new Audio(soundUrl);
            audio.play().catch(error => {
                console.error("Audio play failed:", error);
            });

            // Show a visual toast
            const type = (event.detail && event.detail.type) ? event.detail.type : 'notification';
            const toast = document.createElement('div');
            toast.innerText = `🔔 Action: ${type.toUpperCase()}`;
            toast.style.cssText = "position:fixed; bottom:20px; right:20px; background:#005bb0; color:white; padding:12px 24px; border-radius:12px; z-index:9999; font-weight:bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1);";
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        @endif
    });
</script>
