<div class="p-8 max-w-5xl mx-auto space-y-10 animate-in fade-in duration-500">
    <!-- Hero Action Bar -->
    <section class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h3 class="text-5xl font-black text-on-surface tracking-tighter mb-2">My Schedule</h3>
            <p class="text-slate-500 font-medium text-lg">Weekly clinical availability and session planning.</p>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('doctor.schedule.edit') }}" class="bg-primary-container text-white font-black px-8 py-4 rounded-2xl flex items-center gap-3 hover:scale-[1.02] active:scale-95 transition-all shadow-2xl shadow-primary/20">
                <span class="material-symbols-outlined">edit_calendar</span>
                Manage Schedule
            </a>
        </div>
    </section>

    @if (session()->has('message'))
        <div class="p-4 bg-secondary-container text-on-secondary-container rounded-xl font-bold flex items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('message') }}
        </div>
    @endif

    <!-- Weekly List View -->
    <div class="space-y-6">
        @php
            $days = [
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
                0 => 'Sunday',
            ];
            $today = now()->dayOfWeek;
        @endphp

        @foreach($days as $index => $name)
            <div class="grid grid-cols-1 md:grid-cols-[200px_1fr] gap-6 items-start p-6 rounded-3xl transition-all {{ $today == $index ? 'bg-primary-container/5 border-2 border-primary-container/10' : 'bg-surface-container-lowest border-2 border-transparent' }}">
                <!-- Day Label -->
                <div class="flex items-center gap-3 md:block">
                    <h4 class="text-2xl font-black {{ $today == $index ? 'text-primary-container' : 'text-on-surface' }}">{{ $name }}</h4>
                    @if($today == $index)
                        <span class="px-3 py-1 bg-primary-container text-white text-[10px] font-black uppercase tracking-widest rounded-full">Today</span>
                    @endif
                </div>

                <!-- Sessions List -->
                <div class="flex flex-wrap gap-4">
                    @if(isset($schedules[$index]) && count($schedules[$index]) > 0)
                        @foreach($schedules[$index] as $schedule)
                            <div class="flex items-center gap-4 bg-surface-container-low px-6 py-4 rounded-2xl border border-surface-variant/20 shadow-sm group">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary-container text-sm">schedule</span>
                                    <span class="text-lg font-bold text-on-surface">
                                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                                    </span>
                                    <span class="text-slate-300 font-black px-1">—</span>
                                    <span class="text-lg font-bold text-on-surface">
                                        {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="flex items-center gap-2 text-slate-400 font-medium italic py-2">
                            <span class="material-symbols-outlined text-sm">block</span>
                            <span>No clinical sessions scheduled</span>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
    .glass-overlay {
        background-color: rgba(57, 0, 146, 0.08);
        backdrop-filter: blur(24px);
    }
</style>
