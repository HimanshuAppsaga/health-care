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
                        @if($nowServing && $nowServing->appointment && $nowServing->appointment->doctor)
                            <span class="text-xs font-bold text-gray-400 tracking-widest uppercase">{{ $nowServing->appointment->doctor->user->department ?? 'General Dept' }} • Dr. {{ $nowServing->appointment->doctor->user->name ?? 'Unknown' }}</span>
                        @endif
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
                    <button class="text-sm font-bold text-[#5200cc] hover:underline">View All Schedule</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Patient Name</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Token</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($todaysAppointments as $appointment)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-[#5200cc]">{{ \Carbon\Carbon::parse($appointment->start_time)->format('h:i A') }}</td>
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
                    
                    <a href="{{ route('receptionist.book-appointment') }}" wire:navigate class="w-full flex items-center gap-4 p-4 rounded-2xl bg-[#e6fffb] text-[#0fbda6] font-bold hover:bg-[#0fbda6] hover:text-white transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-white/50 flex items-center justify-center group-hover:bg-[#0da692]">
                            <span class="material-symbols-outlined">calendar_add_on</span>
                        </div>
                        <span>Book Appointment</span>
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
