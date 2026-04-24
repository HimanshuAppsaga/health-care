<div class="fixed inset-0 z-[100] flex items-center justify-end glass-overlay">
    <!-- Side Panel Modal -->
    <div class="w-full max-w-xl h-full bg-surface-container-lowest shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
        <!-- Modal Header -->
        <div class="p-8 border-none flex justify-between items-center bg-surface-container-low">
            <div>
                <h2 class="font-headline text-3xl font-extrabold text-on-surface tracking-tight">{{ $scheduleId ? 'Edit' : 'Create' }} Schedule</h2>
                <p class="text-slate-500 text-sm font-medium">Update slot availability and consultation rules.</p>
            </div>
            <a href="{{ route('doctor.schedule') }}" class="w-10 h-10 flex items-center justify-center rounded-full bg-surface-container-highest/50 hover:bg-surface-container-highest transition-colors">
                <span class="material-symbols-outlined text-on-surface-variant">close</span>
            </a>
        </div>
        
        <!-- Form Content -->
        <form wire:submit.prevent="save" class="flex-1 overflow-y-auto p-8 space-y-10">
            <!-- Day Selector -->
            <div class="space-y-3">
                <label class="text-xs font-black uppercase tracking-widest text-primary/70 block">Recurrence Day</label>
                <div class="flex flex-wrap gap-2">
                    @php
                        $days = [
                            1 => 'M',
                            2 => 'T',
                            3 => 'W',
                            4 => 'T',
                            5 => 'F',
                            6 => 'S',
                            0 => 'S'
                        ];
                    @endphp
                    @foreach($days as $index => $label)
                        <button type="button" 
                                wire:click="$set('day_of_week', {{ $index }})"
                                class="w-12 h-12 rounded-xl flex items-center justify-center font-bold transition-all border-2 
                                {{ $day_of_week === $index 
                                    ? 'border-primary-container text-white bg-primary-container shadow-md' 
                                    : 'border-surface-variant text-slate-400 hover:border-primary/30' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                @error('day_of_week') <p class="text-error text-xs font-bold mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Shift Times -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Shift Start -->
                <div class="space-y-3">
                    <label class="text-xs font-black uppercase tracking-widest text-primary/70 block">Shift Start</label>
                    <div class="flex items-center gap-2">
                        <select wire:model="start_hour" class="bg-transparent border-none border-b-2 border-surface-variant py-2 px-1 font-bold text-xl focus:ring-0 cursor-pointer">
                            @foreach(range(1, 12) as $h)
                                <option value="{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</option>
                            @endforeach
                        </select>
                        <span class="font-black text-xl text-slate-400">:</span>
                        <select wire:model="start_min" class="bg-transparent border-none border-b-2 border-surface-variant py-2 px-1 font-bold text-xl focus:ring-0 cursor-pointer">
                            @foreach(['00', '15', '30', '45'] as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                        <div class="flex bg-surface-container-high rounded-lg p-1 ml-2">
                            <button type="button" wire:click="$set('start_period', 'AM')" 
                                    class="px-3 py-1 text-xs font-black rounded-md transition-all {{ $start_period === 'AM' ? 'bg-primary-container text-white shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                                AM
                            </button>
                            <button type="button" wire:click="$set('start_period', 'PM')" 
                                    class="px-3 py-1 text-xs font-black rounded-md transition-all {{ $start_period === 'PM' ? 'bg-primary-container text-white shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                                PM
                            </button>
                        </div>
                    </div>
                    @error('start_hour') <p class="text-error text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Shift End -->
                <div class="space-y-3">
                    <label class="text-xs font-black uppercase tracking-widest text-primary/70 block">Shift End</label>
                    <div class="flex items-center gap-2">
                        <select wire:model="end_hour" class="bg-transparent border-none border-b-2 border-surface-variant py-2 px-1 font-bold text-xl focus:ring-0 cursor-pointer">
                            @foreach(range(1, 12) as $h)
                                <option value="{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</option>
                            @endforeach
                        </select>
                        <span class="font-black text-xl text-slate-400">:</span>
                        <select wire:model="end_min" class="bg-transparent border-none border-b-2 border-surface-variant py-2 px-1 font-bold text-xl focus:ring-0 cursor-pointer">
                            @foreach(['00', '15', '30', '45'] as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                        <div class="flex bg-surface-container-high rounded-lg p-1 ml-2">
                            <button type="button" wire:click="$set('end_period', 'AM')" 
                                    class="px-3 py-1 text-xs font-black rounded-md transition-all {{ $end_period === 'AM' ? 'bg-primary-container text-white shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                                AM
                            </button>
                            <button type="button" wire:click="$set('end_period', 'PM')" 
                                    class="px-3 py-1 text-xs font-black rounded-md transition-all {{ $end_period === 'PM' ? 'bg-primary-container text-white shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                                PM
                            </button>
                        </div>
                    </div>
                    @error('end_hour') <p class="text-error text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="pt-10 border-none flex gap-4">
                <a href="{{ route('doctor.schedule') }}" class="flex-1 py-4 px-6 rounded-xl font-bold text-slate-600 hover:bg-slate-100 transition-colors text-center">
                    Cancel
                </a>
                <button type="submit" class="flex-[2] py-4 px-6 rounded-xl font-bold bg-primary-container text-white shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">
                    Save Schedule
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .glass-overlay {
        background-color: rgba(57, 0, 146, 0.08);
        backdrop-filter: blur(12px);
    }
    input:focus {
        outline: none;
        border-bottom-color: #390092 !important;
    }
</style>
