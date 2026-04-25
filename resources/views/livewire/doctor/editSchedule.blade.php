<div class="p-8 max-w-4xl mx-auto space-y-8 animate-in fade-in duration-500">
    <!-- Header Section -->
    <header class="space-y-2">
        <div class="flex justify-between items-center">
            <h2 class="text-4xl font-black text-on-surface tracking-tighter">Schedule Management</h2>
            <a href="{{ route('doctor.schedule') }}" class="text-slate-500 hover:text-on-surface transition-colors">
                <span class="material-symbols-outlined text-3xl">close</span>
            </a>
        </div>
        <p class="text-slate-500 font-medium">All times are in Indian Standard Time (IST)</p>
    </header>

    @if (session()->has('message'))
        <div class="p-4 bg-secondary-container text-on-secondary-container rounded-xl font-bold flex items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('sync_message'))
        <div class="p-4 bg-primary-container text-white rounded-xl font-bold flex items-center gap-2">
            <span class="material-symbols-outlined">sync</span>
            {{ session('sync_message') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-12">
        @php
            $days = [
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
                0 => 'Sunday'
            ];
        @endphp

        <div class="space-y-10">
            @foreach($days as $dayNumber => $dayName)
                <div class="grid grid-cols-1 md:grid-cols-[200px_1fr] gap-6 items-start">
                    <!-- Day Label -->
                    <div class="pt-3 space-y-1">
                        <h3 class="text-2xl font-extrabold text-on-surface">{{ $dayName }}</h3>
                        
                    </div>

                    <!-- Sessions Container -->
                    <div class="space-y-6">
                        @foreach($weekly_schedules[$dayNumber] as $index => $session)
                            <div class="flex items-center gap-4 group animate-in slide-in-from-left-4 duration-300">
                                <!-- Time Picker Wrapper -->
                                <div class="flex items-center gap-3">
                                    <!-- Start Time -->
                                    <div class="flex items-center gap-2 bg-surface-container-low px-4 py-3 rounded-xl border-2 border-transparent focus-within:border-primary-container transition-all shadow-sm">
                                        <select wire:model="weekly_schedules.{{ $dayNumber }}.{{ $index }}.start_hour" class="bg-transparent border-none p-0 font-bold text-lg focus:ring-0 cursor-pointer">
                                            @foreach(range(1, 12) as $h)
                                                <option value="{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</option>
                                            @endforeach
                                        </select>
                                        <span class="font-bold text-slate-400">:</span>
                                        <select wire:model="weekly_schedules.{{ $dayNumber }}.{{ $index }}.start_min" class="bg-transparent border-none p-0 font-bold text-lg focus:ring-0 cursor-pointer">
                                            @foreach(['00', '15', '30', '45'] as $m)
                                                <option value="{{ $m }}">{{ $m }}</option>
                                            @endforeach
                                        </select>
                                        <select wire:model="weekly_schedules.{{ $dayNumber }}.{{ $index }}.start_period" class="bg-transparent border-none p-0 font-bold text-sm text-primary uppercase focus:ring-0 cursor-pointer ml-1">
                                            <option value="AM">AM</option>
                                            <option value="PM">PM</option>
                                        </select>
                                        <span class="material-symbols-outlined text-slate-400 text-sm">expand_more</span>
                                    </div>

                                    <span class="text-slate-300 font-black">—</span>

                                    <!-- End Time -->
                                    <div class="flex items-center gap-2 bg-surface-container-low px-4 py-3 rounded-xl border-2 border-transparent focus-within:border-primary-container transition-all shadow-sm">
                                        <select wire:model="weekly_schedules.{{ $dayNumber }}.{{ $index }}.end_hour" class="bg-transparent border-none p-0 font-bold text-lg focus:ring-0 cursor-pointer">
                                            @foreach(range(1, 12) as $h)
                                                <option value="{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</option>
                                            @endforeach
                                        </select>
                                        <span class="font-bold text-slate-400">:</span>
                                        <select wire:model="weekly_schedules.{{ $dayNumber }}.{{ $index }}.end_min" class="bg-transparent border-none p-0 font-bold text-lg focus:ring-0 cursor-pointer">
                                            @foreach(['00', '15', '30', '45'] as $m)
                                                <option value="{{ $m }}">{{ $m }}</option>
                                            @endforeach
                                        </select>
                                        <select wire:model="weekly_schedules.{{ $dayNumber }}.{{ $index }}.end_period" class="bg-transparent border-none p-0 font-bold text-sm text-primary uppercase focus:ring-0 cursor-pointer ml-1">
                                            <option value="AM">AM</option>
                                            <option value="PM">PM</option>
                                        </select>
                                        <span class="material-symbols-outlined text-slate-400 text-sm">expand_more</span>
                                    </div>
                                </div>

                                <!-- Remove Button -->
                                <button type="button" wire:click="removeSession({{ $dayNumber }}, {{ $index }})" class="text-slate-300 hover:text-error transition-colors p-2 rounded-full hover:bg-error-container/20">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                        @endforeach

                        <!-- Add Button -->
                        <button type="button" wire:click="addSession({{ $dayNumber }})" class="flex items-center justify-center w-10 h-10 rounded-full border-2 border-primary-container/20 text-primary-container hover:bg-primary-container hover:text-white transition-all active:scale-90 shadow-sm">
                            <span class="material-symbols-outlined">add</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Sticky Footer Action -->
        <div class="pt-12 border-t border-surface-variant/20 flex justify-end gap-4">
            <a href="{{ route('doctor.schedule') }}" class="px-8 py-4 rounded-2xl font-bold text-slate-500 hover:bg-surface-container-low transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-12 py-4 bg-primary-container text-white font-black rounded-2xl shadow-2xl shadow-primary/30 hover:scale-[1.02] active:scale-95 transition-all">
                Save Weekly Schedule
            </button>
        </div>
    </form>
</div>

<style>
    select {
        appearance: none;
        -webkit-appearance: none;
        background-image: none !important;
    }
</style>
