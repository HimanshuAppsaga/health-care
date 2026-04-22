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
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between hover:border-[#0fbda6]/30 transition-all">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Checked-in</p>
                <h3 class="text-3xl font-black text-[#0fbda6]">{{ $checkedIn }}</h3>
                <p class="text-xs text-gray-400 font-medium mt-2 italic">{{ $waitlist->count() }} in waiting area</p>
            </div>
            <div class="w-14 h-14 bg-[#e6fffb] rounded-2xl flex items-center justify-center text-[#0fbda6]">
                <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">how_to_reg</span>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between hover:border-orange-200 transition-all">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Pending Arrivals</p>
                <h3 class="text-3xl font-black text-orange-500">{{ str_pad($pendingArrivals, 2, '0', STR_PAD_LEFT) }}</h3>
            </div>
            <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-500">
                <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">pending_actions</span>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between hover:border-green-200 transition-all">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Revenue Today</p>
                <h3 class="text-3xl font-black text-green-600">${{ number_format($revenueToday, 2) }}</h3>
            </div>
            <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center text-green-600">
                <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">account_balance_wallet</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-8 px-8 pb-12">
        <!-- Center Column: Live Queue -->
        <div class="col-span-12 lg:col-span-8 flex flex-col gap-8">
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-[#fcf9f8]">
                    <h2 class="text-xl font-black flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        Live Queue Manager
                    </h2>
                    @if($nowServing && $nowServing->appointment && $nowServing->appointment->doctor)
                        <span class="text-xs font-bold text-gray-400 tracking-widest uppercase">Room 102 • Dr. {{ $nowServing->appointment->doctor->user->name ?? 'Unknown' }}</span>
                    @endif
                </div>
                <div class="p-8 flex flex-col items-center justify-center text-center">
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Now Serving</p>
                    <div class="relative">
                        <div class="absolute -inset-8 bg-[#0fbda6]/10 blur-3xl rounded-full"></div>
                        <div class="relative text-9xl font-black text-[#0fbda6] tracking-tighter mb-4">
                            {{ $nowServing ? $nowServing->token_number : '--' }}
                        </div>
                    </div>
                    <h4 class="text-2xl font-bold text-[#1c1b1b] mb-8">
                        {{ $nowServing ? ($nowServing->appointment->name ?? 'Unknown Patient') : 'No Patient Assigned' }}
                    </h4>
                    
                    <div class="flex items-center gap-3 mb-10">
                        <p class="text-sm font-bold text-gray-400 mr-2">NEXT TOKENS:</p>
                        @forelse($nextTokens as $token)
                            <span class="px-4 py-2 bg-gray-100 text-gray-600 rounded-full font-black text-lg">{{ $token->token_number }}</span>
                        @empty
                            <span class="px-4 py-2 bg-gray-100 text-gray-600 rounded-full font-black text-lg">None</span>
                        @endforelse
                    </div>
                    
                    <div class="flex gap-4 w-full max-w-md">
                        <button class="flex-1 py-4 bg-[#0fbda6] text-white rounded-2xl font-black text-lg shadow-lg shadow-[#0fbda6]/30 hover:bg-[#0da692] transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">campaign</span>
                            Call Next Patient
                        </button>
                        <button class="flex-1 py-4 bg-white border-2 border-gray-200 text-[#1c1b1b] rounded-2xl font-black text-lg hover:border-[#5200cc] hover:text-[#5200cc] transition-all active:scale-95">
                            Mark as Done
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
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Doctor</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($todaysAppointments as $appointment)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-[#5200cc]">{{ \Carbon\Carbon::parse($appointment->start_time)->format('h:i A') }}</td>
                                <td class="px-6 py-4 font-medium text-[#1c1b1b]">{{ $appointment->name ?? 'Unknown' }}</td>
                                <td class="px-6 py-4 text-gray-600">Dr. {{ $appointment->doctor->user->name ?? 'Unknown' }}</td>
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
                    <button class="w-full flex items-center gap-4 p-4 rounded-2xl bg-[#ede7ff] text-[#5200cc] font-bold hover:bg-[#5200cc] hover:text-white transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-white/50 flex items-center justify-center group-hover:bg-[#3f00a3]">
                            <span class="material-symbols-outlined">person_add</span>
                        </div>
                        <span>Add New Patient</span>
                    </button>
                    <button class="w-full flex items-center gap-4 p-4 rounded-2xl bg-[#e6fffb] text-[#0fbda6] font-bold hover:bg-[#0fbda6] hover:text-white transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-white/50 flex items-center justify-center group-hover:bg-[#0da692]">
                            <span class="material-symbols-outlined">calendar_add_on</span>
                        </div>
                        <span>Book Appointment</span>
                    </button>
                    <button class="w-full flex items-center gap-4 p-4 rounded-2xl bg-gray-100 text-gray-700 font-bold hover:bg-[#1c1b1b] hover:text-white transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-white/50 flex items-center justify-center group-hover:bg-gray-700">
                            <span class="material-symbols-outlined">confirmation_number</span>
                        </div>
                        <span>Generate Token</span>
                    </button>
                </div>
            </div>

            <div class="bg-[#5200cc] rounded-2xl p-6 text-white shadow-xl shadow-[#5200cc]/20 relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-[#0fbda6]">lightbulb</span>
                        <h4 class="font-bold text-sm tracking-widest uppercase">Receptionist Tip</h4>
                    </div>
                    <p class="text-sm font-medium leading-relaxed mb-4 opacity-90">
                        Check pending arrivals and ensure the waitlist is moving efficiently.
                    </p>
                    <button class="text-xs font-black px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
                        Dismiss Tip
                    </button>
                </div>
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/5 rounded-full"></div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-black text-[#1c1b1b] mb-4">Waitlist ({{ $waitlist->count() }})</h3>
                <div class="space-y-4">
                    @forelse($waitlist as $item)
                        <div class="flex items-center justify-between p-3 rounded-xl border border-dashed border-gray-200">
                            <div>
                                <p class="text-sm font-bold text-[#1c1b1b]">{{ $item->appointment->name ?? 'Unknown' }}</p>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Token: {{ $item->token_number }}</p>
                            </div>
                            <span class="text-xs font-black text-gray-400">{{ \Carbon\Carbon::parse($item->created_at)->diffForHumans(null, true) }} wait</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No patients in the waitlist.</p>
                    @endforelse
                </div>
                @if($waitlist->count() > 0)
                    <button class="w-full mt-6 text-sm font-bold text-gray-400 hover:text-[#5200cc] transition-colors">
                        View Full Waitlist
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
