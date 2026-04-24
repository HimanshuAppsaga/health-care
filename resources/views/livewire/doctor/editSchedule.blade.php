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

            <!-- Time Ranges -->
            <div class="grid grid-cols-2 gap-8">
                <div class="space-y-3">
                    <label class="text-xs font-black uppercase tracking-widest text-primary/70 block">Shift Start</label>
                    <input wire:model="start_time" class="w-full bg-transparent border-none border-b-2 border-surface-variant py-3 px-0 font-bold text-xl focus:ring-0" type="time"/>
                    @error('start_time') <p class="text-error text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-3">
                    <label class="text-xs font-black uppercase tracking-widest text-primary/70 block">Shift End</label>
                    <input wire:model="end_time" class="w-full bg-transparent border-none border-b-2 border-surface-variant py-3 px-0 font-bold text-xl focus:ring-0" type="time"/>
                    @error('end_time') <p class="text-error text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Consultation Settings -->
            <div class="grid grid-cols-2 gap-8">
                <div class="space-y-3">
                    <label class="text-xs font-black uppercase tracking-widest text-primary/70 block">Slot Duration</label>
                    <div class="flex gap-2">
                        @foreach([15, 20, 30] as $duration)
                            <button type="button" 
                                    wire:click="$set('slot_duration', {{ $duration }})"
                                    class="flex-1 py-3 rounded-xl border-2 transition-all
                                    {{ $slot_duration == $duration 
                                        ? 'border-primary-container bg-primary-container/5 text-primary font-bold' 
                                        : 'border-surface-variant text-slate-500 font-bold hover:border-primary/30' }}">
                                {{ $duration }}m
                            </button>
                        @endforeach
                    </div>
                    @error('slot_duration') <p class="text-error text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-3">
                    <label class="text-xs font-black uppercase tracking-widest text-primary/70 block">Patients per Slot</label>
                    <div class="relative">
                        <input wire:model="max_patients" class="w-full bg-transparent border-none border-b-2 border-surface-variant py-3 px-0 font-bold text-xl focus:ring-0" min="1" type="number"/>
                        <span class="absolute right-0 top-3 text-slate-400 text-xs font-bold uppercase">Max</span>
                    </div>
                    @error('max_patients') <p class="text-error text-xs font-bold mt-1">{{ $message }}</p> @enderror
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
