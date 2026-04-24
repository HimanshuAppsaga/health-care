<div class="p-8 max-w-7xl mx-auto">
    <!-- Hero Action Bar -->
    <section class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
        <div>
            <h3 class="text-4xl font-extrabold text-on-surface tracking-tighter mb-2">Doctor Schedule Management</h3>
            <p class="text-on-surface-variant font-medium">Weekly operational planning for surgical and clinical staff.</p>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('doctor.schedule.edit') }}" class="bg-primary-container text-on-primary font-bold px-6 py-3 rounded-lg flex items-center gap-2 hover:bg-on-primary-fixed-variant transition-all active:scale-95 shadow-lg shadow-primary/10">
                <span class="material-symbols-outlined" data-icon="add">add</span>
                Create Schedule
            </a>
        </div>
    </section>

    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-secondary-container text-on-secondary-container rounded-xl font-bold flex items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('message') }}
        </div>
    @endif

    <!-- Weekly Calendar View -->
    <div class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-xl shadow-slate-200/50">
        <!-- Calendar Header -->
        <div class="grid grid-cols-7 border-b border-surface-variant/30">
            @php
                $days = [
                    1 => 'Mon',
                    2 => 'Tue',
                    3 => 'Wed',
                    4 => 'Thu',
                    5 => 'Fri',
                    6 => 'Sat',
                    0 => 'Sun',
                ];
                $today = now()->dayOfWeek;
                $startOfWeek = now()->startOfWeek(\Carbon\Carbon::MONDAY);
            @endphp
            @foreach($days as $index => $name)
                @php
                    $dayOffset = ($index === 0) ? 6 : ($index - 1);
                    $currentDayDate = $startOfWeek->copy()->addDays($dayOffset);
                @endphp
                <div class="p-6 text-center border-r border-surface-variant/30 {{ $today == $index ? 'bg-primary/5' : 'bg-surface-container-low/50' }}">
                    <span class="block text-xs font-black uppercase {{ $today == $index ? 'text-primary' : 'text-outline' }} tracking-widest mb-1">{{ $name }}</span>
                    <span class="text-2xl font-extrabold {{ $today == $index ? 'text-primary' : '' }}">
                        {{ $currentDayDate->day }}
                    </span>
                </div>
            @endforeach
        </div>

        <!-- Calendar Content Grid -->
        <div class="grid grid-cols-7 h-[600px] overflow-y-auto">
            @foreach($days as $index => $name)
                <div class="p-4 border-r border-surface-variant/10 space-y-3 {{ $today == $index ? 'bg-primary/5' : '' }}">
                    @if(isset($schedules[$index]) && count($schedules[$index]) > 0)
                        @foreach($schedules[$index] as $schedule)
                            <div class="bg-primary-fixed/30 p-3 rounded-xl border-l-4 border-primary group relative">
                                <div class="flex justify-between items-start mb-1">
                                    <p class="text-[10px] font-bold text-primary uppercase">Consultation</p>
                                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('doctor.schedule.edit', $schedule->id) }}" class="text-primary hover:text-on-primary-fixed-variant">
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                        </a>
                                        <button wire:click="confirmScheduleDeletion({{ $schedule->id }})" class="text-error hover:text-on-error-container">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                    </div>
                                </div>
                                <p class="text-sm font-semibold text-on-surface">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - 
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                </p>
                            </div>
                        @endforeach
                    @else
                        <div class="h-full flex items-center justify-center border-2 border-dashed border-outline-variant/30 rounded-2xl p-4">
                            <p class="text-[10px] font-bold text-outline uppercase tracking-widest -rotate-90">No Schedule</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($confirmingScheduleDeletion)
        <div class="fixed inset-0 z-[100] flex items-center justify-center glass-overlay">
            <div class="w-full max-w-md bg-surface-container-lowest rounded-xl p-8 space-y-8 animate-in fade-in zoom-in duration-300">
                <div class="flex flex-col items-center text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-error-container flex items-center justify-center text-error">
                        <span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">warning</span>
                    </div>
                    <div class="space-y-2">
                        <h2 class="text-2xl font-headline font-extrabold tracking-tight text-on-surface">Delete Schedule?</h2>
                        <p class="text-on-surface-variant leading-relaxed px-4">
                            This will remove your availability for the selected day. This action cannot be undone and may affect existing appointments.
                        </p>
                    </div>
                </div>
                <div class="flex flex-col gap-3">
                    <button wire:click="deleteSchedule" class="w-full py-4 bg-error text-on-error font-bold rounded-lg hover:bg-on-error-container transition-colors shadow-lg shadow-error/10">
                        Delete Schedule
                    </button>
                    <button wire:click="cancelScheduleDeletion" class="w-full py-4 bg-transparent text-slate-600 font-semibold rounded-lg hover:bg-slate-100 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .glass-overlay {
        background-color: rgba(57, 0, 146, 0.08);
        backdrop-filter: blur(24px);
    }
</style>
