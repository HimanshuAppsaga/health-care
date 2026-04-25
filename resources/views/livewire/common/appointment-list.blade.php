<div class="p-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-[#1c1b1b] flex items-center gap-3">
                <span class="w-12 h-12 bg-[#5200cc] text-white rounded-2xl flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">calendar_month</span>
                </span>
                Appointment History
            </h1>
            <p class="text-gray-500 font-medium mt-1 ml-15">View and manage all appointments across the clinic</p>
        </div>
        
        <div class="flex items-center gap-3">
            @if(auth()->user()->hasRole(['receptionist', 'patient']))
                <a href="{{ auth()->user()->hasRole('receptionist') ? route('receptionist.book-appointment') : route('patient.book-appointment') }}" 
                   wire:navigate
                   class="px-6 py-3 bg-[#5200cc] text-white rounded-2xl font-black shadow-lg shadow-[#5200cc]/30 hover:bg-[#4100a3] transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">add</span>
                    Book New
                </a>
            @endif
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            <!-- Search -->
            <div class="relative group">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Search Patient</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-[#5200cc] transition-colors">search</span>
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           placeholder="Name, Phone or Token..." 
                           class="w-full pl-12 pr-4 py-3 bg-gray-50 border-2 border-transparent rounded-2xl focus:bg-white focus:border-[#5200cc]/20 outline-none transition-all font-bold text-sm">
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Status</label>
                <select wire:model.live="status" class="w-full px-4 py-3 bg-gray-50 border-2 border-transparent rounded-2xl focus:bg-white focus:border-[#5200cc]/20 outline-none transition-all font-bold text-sm appearance-none">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Timeframe</label>
                <div class="flex bg-gray-50 p-1 rounded-2xl">
                    <button wire:click="setDateRange('all')" class="flex-1 py-2 rounded-xl text-xs font-black transition-all {{ $dateRange === 'all' ? 'bg-white text-[#5200cc] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">ALL</button>
                    <button wire:click="setDateRange('today')" class="flex-1 py-2 rounded-xl text-xs font-black transition-all {{ $dateRange === 'today' ? 'bg-white text-[#5200cc] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">TODAY</button>
                    <button wire:click="setDateRange('week')" class="flex-1 py-2 rounded-xl text-xs font-black transition-all {{ $dateRange === 'week' ? 'bg-white text-[#5200cc] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">WEEK</button>
                </div>
            </div>

            <!-- Custom Dates -->
            @if($dateRange === 'custom' || $dateRange === 'all')
            <div class="lg:col-span-1 xl:col-span-1 flex items-end gap-2">
                    <input type="date" wire:model.live="startDate" class="w-full px-3 py-2 bg-gray-50 border-2 border-transparent rounded-xl focus:bg-white focus:border-[#5200cc]/20 outline-none transition-all font-bold text-[10px]">
            </div>
            @else
            <div class="flex items-end">
                <button wire:click="setDateRange('custom')" class="text-[#5200cc] text-xs font-black hover:underline mb-3 ml-2 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">calendar_today</span>
                    Custom Range
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-8 py-6 text-xs font-black text-gray-400 uppercase tracking-widest">Date & Time</th>
                        <th class="px-8 py-6 text-xs font-black text-gray-400 uppercase tracking-widest">Patient Details</th>
                        <th class="px-8 py-6 text-xs font-black text-gray-400 uppercase tracking-widest">Token</th>
                        <th class="px-8 py-6 text-xs font-black text-gray-400 uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($appointments as $appointment)
                    <tr class="hover:bg-gray-50/80 transition-all group">
                        <td class="px-8 py-6">
                            <div class="flex flex-col">
                                <span class="font-black text-[#1c1b1b]">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</span>
                                <div class="text-xs font-bold text-[#5200cc] flex items-center gap-1 mt-1">
                                    <span class="material-symbols-outlined text-xs">schedule</span>
                                    @php
                                        $dayOfWeek = \Carbon\Carbon::parse($appointment->appointment_date)->dayOfWeek;
                                        $session = $schedules->first(function($s) use ($appointment, $dayOfWeek) {
                                            return $s->doctor_id == $appointment->doctor_id && 
                                                   $s->day_of_week == $dayOfWeek &&
                                                   $appointment->start_time >= $s->start_time && 
                                                   $appointment->start_time < $s->end_time;
                                        });
                                    @endphp
                                    @if($session)
                                        {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($appointment->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($appointment->end_time)->format('h:i A') }}
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#fcf9f8] flex items-center justify-center text-[#5200cc] font-black border border-gray-100 uppercase">
                                    {{ substr($appointment->name, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-[#1c1b1b] group-hover:text-[#5200cc] transition-colors">{{ $appointment->name }}</span>
                                    <span class="text-xs text-gray-400 font-medium">{{ $appointment->phone }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="inline-block px-3 py-1 bg-gray-100 text-gray-600 rounded-lg font-black text-sm border border-gray-200">
                                {{ $appointment->token ?? '--' }}
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                    'confirmed' => 'bg-green-100 text-green-700 border-green-200',
                                    'completed' => 'bg-[#5200cc]/10 text-[#5200cc] border-[#5200cc]/20',
                                    'cancelled' => 'bg-red-100 text-red-700 border-red-200',
                                    'no_show' => 'bg-orange-100 text-orange-700 border-orange-200',
                                ];
                                $class = $statusClasses[$appointment->status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                            @endphp
                            <span class="px-4 py-1.5 {{ $class }} text-[10px] font-black uppercase rounded-full border tracking-widest">
                                {{ $appointment->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <span class="material-symbols-outlined text-6xl mb-4">event_busy</span>
                                <p class="text-lg font-black uppercase tracking-widest">No appointments found</p>
                                <p class="text-sm font-medium">Try adjusting your filters or search terms</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($appointments->hasPages())
        <div class="px-8 py-6 bg-gray-50/30 border-t border-gray-50">
            {{ $appointments->links() }}
        </div>
        @endif
    </div>
</div>
