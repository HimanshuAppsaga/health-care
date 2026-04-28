<div class="p-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-on-background flex items-center gap-3">
                <span class="w-12 h-12 bg-primary text-on-primary rounded-2xl flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">calendar_month</span>
                </span>
                Appointment History
            </h1>
            <p class="text-outline font-medium mt-1 ml-15">View and manage all appointments across the clinic</p>
        </div>
        
        <div class="flex items-center gap-3">
            @if(auth()->user()->hasRole(['receptionist', 'patient']))
                <a href="{{ auth()->user()->hasRole('receptionist') ? route('receptionist.book-appointment') : route('patient.book-appointment') }}" 
                   wire:navigate
                   class="px-6 py-3 bg-primary text-on-primary rounded-2xl font-black shadow-lg shadow-primary/30 hover:bg-primary-container transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">add</span>
                    Book New
                </a>
            @endif
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-surface p-6 rounded-[2rem] shadow-clinical border border-outline-variant mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            <!-- Search -->
            <div class="relative group">
                <label class="text-xs font-black text-outline uppercase tracking-widest mb-2 block ml-1">Search Patient</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">search</span>
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           placeholder="Name, Phone or Token..." 
                           class="w-full pl-12 pr-4 py-3 bg-surface-container-low border-2 border-transparent rounded-2xl focus:bg-surface focus:border-primary/20 outline-none transition-all font-bold text-sm text-on-surface">
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="text-xs font-black text-outline uppercase tracking-widest mb-2 block ml-1">Status</label>
                <select wire:model.live="status" class="w-full px-4 py-3 bg-surface-container-low border-2 border-transparent rounded-2xl focus:bg-surface focus:border-primary/20 outline-none transition-all font-bold text-sm text-on-surface appearance-none">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                </select>
            </div>


            <!-- Date Range -->
            <div>
                <label class="text-xs font-black text-outline uppercase tracking-widest mb-2 block ml-1">Timeframe</label>
                <div class="flex bg-surface-container-low p-1 rounded-2xl">
                    <button wire:click="setDateRange('all')" class="flex-1 py-2 rounded-xl text-xs font-black transition-all {{ $dateRange === 'all' ? 'bg-surface text-primary shadow-sm' : 'text-outline hover:text-on-surface' }}">ALL</button>
                    <button wire:click="setDateRange('today')" class="flex-1 py-2 rounded-xl text-xs font-black transition-all {{ $dateRange === 'today' ? 'bg-surface text-primary shadow-sm' : 'text-outline hover:text-on-surface' }}">TODAY</button>
                    <button wire:click="setDateRange('week')" class="flex-1 py-2 rounded-xl text-xs font-black transition-all {{ $dateRange === 'week' ? 'bg-surface text-primary shadow-sm' : 'text-outline hover:text-on-surface' }}">WEEK</button>
                </div>
            </div>

            <!-- Custom Dates -->
            @if($dateRange === 'custom' || $dateRange === 'all')
            <div class="lg:col-span-1 xl:col-span-1 flex items-end">
                <input type="date" wire:model.live="startDate" class="w-full px-3 py-3 bg-gray-50 border-2 border-transparent rounded-2xl focus:bg-white focus:border-[#5200cc]/20 outline-none transition-all font-bold text-sm">
            </div>
            @else
            <div class="flex items-end">
                <button wire:click="setDateRange('custom')" class="text-primary text-xs font-black hover:underline mb-4 ml-2 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">calendar_today</span>
                    Custom Range
                </button>
            </div>
            @endif


            <!-- Clear Action -->
            <div class="flex items-end">
                <button wire:click="clearFilters" 
                        class="w-full px-4 py-3 bg-surface-container-low text-outline rounded-2xl font-black text-xs hover:bg-error-container hover:text-error transition-all flex items-center justify-center gap-2 group">
                    <span class="material-symbols-outlined text-sm group-hover:rotate-180 transition-transform duration-500">restart_alt</span>
                    Clear All
                </button>
            </div>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="bg-surface rounded-[2rem] shadow-clinical border border-outline-variant overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low/50">
                        <th class="px-8 py-6 text-xs font-black text-outline uppercase tracking-widest">Date & Time</th>
                        <th class="px-8 py-6 text-xs font-black text-outline uppercase tracking-widest">Patient Details</th>
                        <th class="px-8 py-6 text-xs font-black text-outline uppercase tracking-widest">Token</th>
                        <th class="px-8 py-6 text-xs font-black text-outline uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @forelse($appointments as $appointment)
                    <tr class="hover:bg-primary-container/10 transition-all group">
                        <td class="px-8 py-6">
                            <div class="flex flex-col">
                                <span class="font-black text-on-background">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</span>
                                <div class="text-xs font-bold text-primary flex items-center gap-1 mt-1">
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
                                <div class="w-10 h-10 rounded-full bg-surface-container-low flex items-center justify-center text-primary font-black border border-outline-variant uppercase">
                                    {{ substr($appointment->name, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-on-background group-hover:text-primary transition-colors">{{ $appointment->name }}</span>
                                    <span class="text-xs text-outline font-medium">{{ $appointment->phone }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="inline-block px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-lg font-black text-sm border border-outline-variant">
                                {{ $appointment->token ?? '--' }}
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'completed' => 'bg-primary-container/20 text-primary border-primary-container/30',
                                ];
                                $class = $statusClasses[$appointment->status] ?? 'bg-surface-container-low text-on-surface-variant border-outline-variant';
                            @endphp
                            <span class="px-4 py-1.5 {{ $class }} text-[10px] font-black uppercase rounded-full border tracking-widest">
                                {{ $appointment->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-outline">
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
        <div class="px-8 py-6 bg-surface-container-low/30 border-t border-outline-variant flex justify-end">
            {{ $appointments->links('livewire.common.custom-pagination') }}
        </div>
        @endif
    </div>
</div>
