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

        <div class="space-y-12">
            @foreach($days as $dayNumber => $dayName)
                <div class="bg-white/40 p-8 rounded-[2.5rem] border border-white shadow-sm hover:shadow-md transition-all">
                    <div class="grid grid-cols-1 lg:grid-cols-[200px_1fr] gap-8 items-start">
                        <!-- Day Label -->
                        <div class="pt-2">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-primary-container/10 flex items-center justify-center text-primary-container">
                                    <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">calendar_today</span>
                                </div>
                                <h3 class="text-2xl font-black text-on-surface tracking-tight">{{ $dayName }}</h3>
                            </div>
                        </div>

                        <!-- Sessions Container -->
                        <div class="space-y-6">
                            @foreach($weekly_schedules[$dayNumber] as $index => $session)
                                <div class="flex flex-wrap items-center gap-6 group animate-in slide-in-from-left-4 duration-300">
                                    <!-- Time Picker Row -->
                                    <div class="flex items-center gap-4">
                                        <!-- Start Time -->
                                        <div class="flex items-center gap-2 time-picker-container px-5 py-3 rounded-2xl shadow-sm">
                                            <!-- Hour -->
                                            <div x-data="{ open: false, value: @entangle('weekly_schedules.'.$dayNumber.'.'.$index.'.start_hour') }" class="relative">
                                                <button type="button" @click="open = !open" class="time-unit-btn font-black text-xl" x-text="value"></button>
                                                <div x-show="open" @click.away="open = false" x-transition class="absolute top-full left-1/2 -translate-x-1/2 mt-2 dropdown-menu min-w-[80px]">
                                                    @foreach(range(1, 12) as $h)
                                                        @php $val = str_pad($h, 2, '0', STR_PAD_LEFT); @endphp
                                                        <div @click="value = '{{ $val }}'; open = false" 
                                                             class="dropdown-item" 
                                                             :class="value == '{{ $val }}' ? 'active' : ''">
                                                            {{ $val }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <span class="font-black text-slate-300">:</span>

                                            <!-- Minutes -->
                                            <div x-data="{ open: false, value: @entangle('weekly_schedules.'.$dayNumber.'.'.$index.'.start_min') }" class="relative">
                                                <button type="button" @click="open = !open" class="time-unit-btn font-black text-xl" x-text="value"></button>
                                                <div x-show="open" @click.away="open = false" x-transition class="absolute top-full left-1/2 -translate-x-1/2 mt-2 dropdown-menu min-w-[80px]">
                                                    @foreach(['00', '15', '30', '45'] as $m)
                                                        <div @click="value = '{{ $m }}'; open = false" 
                                                             class="dropdown-item" 
                                                             :class="value == '{{ $m }}' ? 'active' : ''">
                                                            {{ $m }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <!-- Period -->
                                            <div x-data="{ open: false, value: @entangle('weekly_schedules.'.$dayNumber.'.'.$index.'.start_period') }" class="relative ml-1">
                                                <button type="button" @click="open = !open" class="time-unit-btn font-black text-sm text-primary-container uppercase" x-text="value"></button>
                                                <div x-show="open" @click.away="open = false" x-transition class="absolute top-full left-1/2 -translate-x-1/2 mt-2 dropdown-menu min-w-[80px]">
                                                    @foreach(['AM', 'PM'] as $p)
                                                        <div @click="value = '{{ $p }}'; open = false" 
                                                             class="dropdown-item text-sm" 
                                                             :class="value == '{{ $p }}' ? 'active' : ''">
                                                            {{ $p }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        <div class="w-4 h-[2px] bg-slate-200 rounded-full"></div>

                                        <!-- End Time -->
                                        <div class="flex items-center gap-2 time-picker-container px-5 py-3 rounded-2xl shadow-sm">
                                            <!-- Hour -->
                                            <div x-data="{ open: false, value: @entangle('weekly_schedules.'.$dayNumber.'.'.$index.'.end_hour') }" class="relative">
                                                <button type="button" @click="open = !open" class="time-unit-btn font-black text-xl" x-text="value"></button>
                                                <div x-show="open" @click.away="open = false" x-transition class="absolute top-full left-1/2 -translate-x-1/2 mt-2 dropdown-menu min-w-[80px]">
                                                    @foreach(range(1, 12) as $h)
                                                        @php $val = str_pad($h, 2, '0', STR_PAD_LEFT); @endphp
                                                        <div @click="value = '{{ $val }}'; open = false" 
                                                             class="dropdown-item" 
                                                             :class="value == '{{ $val }}' ? 'active' : ''">
                                                            {{ $val }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <span class="font-black text-slate-300">:</span>

                                            <!-- Minutes -->
                                            <div x-data="{ open: false, value: @entangle('weekly_schedules.'.$dayNumber.'.'.$index.'.end_min') }" class="relative">
                                                <button type="button" @click="open = !open" class="time-unit-btn font-black text-xl" x-text="value"></button>
                                                <div x-show="open" @click.away="open = false" x-transition class="absolute top-full left-1/2 -translate-x-1/2 mt-2 dropdown-menu min-w-[80px]">
                                                    @foreach(['00', '15', '30', '45'] as $m)
                                                        <div @click="value = '{{ $m }}'; open = false" 
                                                             class="dropdown-item" 
                                                             :class="value == '{{ $m }}' ? 'active' : ''">
                                                            {{ $m }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <!-- Period -->
                                            <div x-data="{ open: false, value: @entangle('weekly_schedules.'.$dayNumber.'.'.$index.'.end_period') }" class="relative ml-1">
                                                <button type="button" @click="open = !open" class="time-unit-btn font-black text-sm text-primary-container uppercase" x-text="value"></button>
                                                <div x-show="open" @click.away="open = false" x-transition class="absolute top-full left-1/2 -translate-x-1/2 mt-2 dropdown-menu min-w-[80px]">
                                                    @foreach(['AM', 'PM'] as $p)
                                                        <div @click="value = '{{ $p }}'; open = false" 
                                                             class="dropdown-item text-sm" 
                                                             :class="value == '{{ $p }}' ? 'active' : ''">
                                                            {{ $p }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-2">
                                        <button type="button" wire:click="removeSession({{ $dayNumber }}, {{ $index }})" 
                                            class="w-10 h-10 flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all group/btn">
                                            <span class="material-symbols-outlined text-xl group-hover/btn:rotate-90 transition-transform">close</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Add Button -->
                            <button type="button" wire:click="addSession({{ $dayNumber }})" 
                                class="flex items-center gap-2 px-6 py-3 rounded-2xl border-2 border-dashed border-slate-200 text-slate-400 font-bold hover:border-primary-container/30 hover:text-primary-container hover:bg-primary-container/5 transition-all group">
                                <span class="material-symbols-outlined group-hover:rotate-180 transition-transform">add</span>
                                <span>Add Session</span>
                            </button>
                        </div>
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
    .time-picker-container {
        position: relative;
        z-index: 1;
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(82, 0, 204, 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .time-picker-container:focus-within {
        z-index: 100;
        background: white;
        border-color: #5200cc;
        box-shadow: 0 10px 25px -5px rgba(82, 0, 204, 0.1), 0 8px 10px -6px rgba(82, 0, 204, 0.1);
        transform: translateY(-1px);
    }
    .dropdown-menu {
        background: white;
        border: 1px solid rgba(82, 0, 204, 0.1);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        border-radius: 16px;
        z-index: 50;
        max-height: 240px;
        overflow-y: auto;
    }
    .dropdown-item {
        padding: 10px 16px;
        font-weight: 700;
        font-size: 1.125rem;
        color: #1c1b1b;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .dropdown-item:hover {
        background: #5200cc;
        color: white;
    }
    .dropdown-item.active {
        background: #ede7ff;
        color: #5200cc;
    }
    .time-unit-btn {
        padding: 4px 8px;
        border-radius: 8px;
        transition: all 0.2s;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 2px;
    }
    .time-unit-btn:hover {
        background: #ede7ff;
        color: #5200cc;
    }
    
    /* Custom Scrollbar for Dropdown */
    .dropdown-menu::-webkit-scrollbar {
        width: 6px;
    }
    .dropdown-menu::-webkit-scrollbar-track {
        background: transparent;
    }
    .dropdown-menu::-webkit-scrollbar-thumb {
        background: #E2E8F0;
        border-radius: 10px;
    }
    .dropdown-menu::-webkit-scrollbar-thumb:hover {
        background: #CBD5E1;
    }
</style>
