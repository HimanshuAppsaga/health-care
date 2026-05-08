<div class="px-8 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('doctor.profile.detail', $doctor->id) }}" wire:navigate class="w-10 h-10 rounded-xl bg-surface border border-outline-variant flex items-center justify-center text-outline hover:text-primary hover:border-primary transition-all">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <h1 class="text-3xl font-black text-on-background tracking-tight">Edit Doctor Profile</h1>
                </div>
                <p class="text-outline font-medium">Update your professional information, bio, and working hours.</p>
            </div>
            
            <div></div>
        </div>

        <!-- Success Message -->
        @if (session()->has('success'))
            @teleport('body')
                <div x-data="{ show: true }" 
                     x-show="show" 
                     x-init="setTimeout(() => show = false, 5000)"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="fixed top-8 right-8 z-[9999] pointer-events-none">
                    <div class="p-4 bg-primary text-on-primary border border-primary/20 rounded-2xl flex items-center gap-4 animate-in fade-in slide-in-from-right-4 duration-500 min-w-[320px] max-w-md shadow-2xl shadow-primary/40 pointer-events-auto">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined font-black text-white">check_circle</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-black uppercase tracking-widest opacity-70 mb-0.5">Success</p>
                            <p class="font-bold text-white">{{ session('success') }}</p>
                        </div>
                        <button @click="show = false" class="w-8 h-8 flex items-center justify-center hover:bg-white/10 rounded-lg transition-colors">
                            <span class="material-symbols-outlined text-base text-white">close</span>
                        </button>
                    </div>
                </div>
            @endteleport
        @endif

        <!-- Main Form Card -->
        <div class="bg-surface rounded-[2.5rem] border border-outline-variant clinical-shadow overflow-hidden">
            <div class="p-10 space-y-12">
                
                {{-- PHOTO SECTION --}}
                <div class="bg-surface-container-low/50 p-8 rounded-3xl border border-outline-variant/30">
                    <h3 class="text-lg font-black text-on-background mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">image</span>
                        Profile Photo
                    </h3>
                    
                    <div class="flex flex-col md:flex-row gap-8 items-start">
                        <div class="relative group">
                            <div class="w-40 h-40 rounded-3xl overflow-hidden bg-surface flex items-center justify-center border-2 border-outline-variant shadow-inner">
                                @if ($photo)
                                    <img src="{{ $photo->temporaryUrl() }}" class="w-50% h-50% object-cover">
                                @elseif($doctor->user->profile_photo_path && !$removePhoto)
                                    <img src="{{ asset('storage/'.$doctor->user->profile_photo_path) }}" class="w-50% h-50% object-cover">
                                @else
                                    <span class="material-symbols-outlined text-6xl text-outline-variant">person</span>
                                @endif
                            </div>
                            
                            @if(($doctor->user->profile_photo_path && !$removePhoto) || $photo)
                                <button wire:click="{{ $photo ? 'cancelNewPhoto' : 'removeExistingPhoto' }}" 
                                        class="absolute -top-3 -right-3 w-10 h-10 bg-error text-on-error rounded-full shadow-lg flex items-center justify-center hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined text-xl">close</span>
                                </button>
                            @endif
                        </div>

                        <div class="flex-1 space-y-4">
                            <p class="text-sm font-bold text-outline uppercase tracking-widest">Profile Picture</p>
                            <p class="text-xs text-outline-variant leading-relaxed">Upload a high-resolution profile photo. Recommended size 512x512px. PNG or JPG format (max 2MB).</p>
                            
                            <label class="inline-flex items-center gap-2 px-6 py-3 bg-surface-container border border-outline-variant rounded-xl cursor-pointer hover:bg-surface-container-high transition-all">
                                <span class="material-symbols-outlined text-primary">upload</span>
                                <span class="text-sm font-bold">Choose File</span>
                                <input type="file" wire:model="photo" class="hidden">
                            </label>
                            
                            <div wire:loading wire:target="photo" class="flex items-center gap-2 text-sm text-primary font-bold">
                                <span class="animate-spin material-symbols-outlined text-lg">sync</span>
                                Uploading preview...
                            </div>
                            @error('photo') <p class="text-xs text-error font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- BASIC INFO --}}
                <div class="grid md:grid-cols-2 gap-10">
                    <div class="space-y-6">
                        <h3 class="text-lg font-black text-on-background mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">person</span>
                            Personal Information
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="group">
                                <label class="text-xs font-black text-outline uppercase tracking-widest ml-4 mb-2 block group-focus-within:text-primary transition-colors">Full Name</label>
                                <input type="text" wire:model="name"
                                       class="w-full bg-surface-container-low border-2 border-outline-variant/30 rounded-2xl px-6 py-4 font-bold focus:border-primary focus:ring-0 transition-all placeholder:text-outline-variant"
                                       placeholder="Enter your name">
                                @error('name') <p class="text-xs text-error font-bold mt-1 ml-4">{{ $message }}</p> @enderror
                            </div>

                            <div class="group">
                                <label class="text-xs font-black text-outline uppercase tracking-widest ml-4 mb-2 block group-focus-within:text-primary transition-colors">Phone Number</label>
                                <input type="text" wire:model="phone"
                                       class="w-full bg-surface-container-low border-2 border-outline-variant/30 rounded-2xl px-6 py-4 font-bold focus:border-primary focus:ring-0 transition-all placeholder:text-outline-variant"
                                       placeholder="+1 234 567 890">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h3 class="text-lg font-black text-on-background mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">medical_information</span>
                            Professional Details
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="group">
                                <label class="text-xs font-black text-outline uppercase tracking-widest ml-4 mb-2 block group-focus-within:text-primary transition-colors">Specialization</label>
                                <input type="text" wire:model="specialization"
                                       class="w-full bg-surface-container-low border-2 border-outline-variant/30 rounded-2xl px-6 py-4 font-bold focus:border-primary focus:ring-0 transition-all placeholder:text-outline-variant"
                                       placeholder="e.g. Cardiologist">
                            </div>

                            <div class="group">
                                <label class="text-xs font-black text-outline uppercase tracking-widest ml-4 mb-2 block group-focus-within:text-primary transition-colors">Qualification</label>
                                <input type="text" wire:model="qualification"
                                       class="w-full bg-surface-container-low border-2 border-outline-variant/30 rounded-2xl px-6 py-4 font-bold focus:border-primary focus:ring-0 transition-all placeholder:text-outline-variant"
                                       placeholder="e.g. MBBS, MD">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- EXPERIENCE & FEE --}}
                <div class="grid md:grid-cols-2 gap-10">
                    <div class="group">
                        <label class="text-xs font-black text-outline uppercase tracking-widest ml-4 mb-2 block group-focus-within:text-primary transition-colors">Experience (Years)</label>
                        <input type="number" wire:model="experience_years"
                               class="w-full bg-surface-container-low border-2 border-outline-variant/30 rounded-2xl px-6 py-4 font-bold focus:border-primary focus:ring-0 transition-all placeholder:text-outline-variant"
                               placeholder="e.g. 10">
                    </div>

                    <div class="group">
                        <label class="text-xs font-black text-outline uppercase tracking-widest ml-4 mb-2 block group-focus-within:text-primary transition-colors">Consultation Fee ($)</label>
                        <input type="number" step="0.01" wire:model="consultation_fee"
                               class="w-full bg-surface-container-low border-2 border-outline-variant/30 rounded-2xl px-6 py-4 font-bold focus:border-primary focus:ring-0 transition-all placeholder:text-outline-variant"
                               placeholder="e.g. 50.00">
                    </div>
                </div>

                {{-- BIO --}}
                <div class="space-y-6">
                    <h3 class="text-lg font-black text-on-background mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">description</span>
                        About Me
                    </h3>
                    
                    <div class="group">
                        <label class="text-xs font-black text-outline uppercase tracking-widest ml-4 mb-2 block group-focus-within:text-primary transition-colors">Professional Bio</label>
                        <textarea wire:model="bio" rows="6"
                                  class="w-full bg-surface-container-low border-2 border-outline-variant/30 rounded-[2rem] px-8 py-6 font-medium leading-relaxed focus:border-primary focus:ring-0 transition-all placeholder:text-outline-variant"
                                  placeholder="Describe your background, specialties, and approach to patient care..."></textarea>
                    </div>
                </div>

                {{-- OPERATING HOURS --}}
                <div class="space-y-6">
                    <h3 class="text-lg font-black text-on-background mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">schedule</span>
                        Working Hours
                    </h3>
                    
                    <div class="space-y-4 max-h-[600px] overflow-y-auto pr-4 custom-scrollbar">
                        @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                            <div class="p-4 rounded-3xl border border-outline-variant/30 bg-surface-container-low/20 hover:bg-surface-container-low/40 transition-all"
                                 x-data="{ isClosed: @entangle('working_hours_parts.'.$day.'.is_closed') }">
                                <div class="flex items-center justify-between gap-4 mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                            <span class="material-symbols-outlined text-xl">calendar_today</span>
                                        </div>
                                        <span class="capitalize font-black text-on-background tracking-tight text-sm">{{ $day }}</span>
                                    </div>

                                    <label class="relative inline-flex items-center cursor-pointer group scale-90">
                                        <input type="checkbox" x-model="isClosed" class="sr-only peer">
                                        <div class="w-11 h-6 bg-outline-variant peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                        <span class="ms-3 text-[10px] font-black text-outline uppercase tracking-widest" x-text="isClosed ? 'Closed' : 'Open'"></span>
                                    </label>
                                </div>

                                <div class="space-y-3" x-show="!isClosed" x-cloak x-transition>
                                    @foreach($working_hours_parts[$day]['slots'] as $index => $slot)
                                        <div class="flex items-center gap-2 animate-in slide-in-from-left-2 duration-300">
                                            <!-- START TIME -->
                                            <div class="flex items-center gap-1.5 time-picker-container px-2.5 py-1.5 rounded-xl shadow-sm border border-outline-variant/40">
                                                <!-- Hour -->
                                                <div x-data="{ open: false, value: @entangle('working_hours_parts.'.$day.'.slots.'.$index.'.start_hour') }" class="relative">
                                                    <button type="button" @click="open = !open" class="time-unit-btn font-black text-base" x-text="value"></button>
                                                    <div x-show="open" @click.away="open = false" x-transition class="absolute top-full left-1/2 -translate-x-1/2 mt-2 dropdown-menu min-w-[70px]">
                                                        @foreach(range(1, 12) as $h)
                                                            @php $val = str_pad($h, 2, '0', STR_PAD_LEFT); @endphp
                                                            <div @click="value = '{{ $val }}'; open = false" class="dropdown-item text-xs" :class="value == '{{ $val }}' ? 'active' : ''">{{ $val }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <span class="font-black text-outline-variant text-xs">:</span>
                                                <!-- Min -->
                                                <div x-data="{ open: false, value: @entangle('working_hours_parts.'.$day.'.slots.'.$index.'.start_min') }" class="relative">
                                                    <button type="button" @click="open = !open" class="time-unit-btn font-black text-base" x-text="value"></button>
                                                    <div x-show="open" @click.away="open = false" x-transition class="absolute top-full left-1/2 -translate-x-1/2 mt-2 dropdown-menu min-w-[70px]">
                                                        @foreach(['00', '15', '30', '45'] as $m)
                                                            <div @click="value = '{{ $m }}'; open = false" class="dropdown-item text-xs" :class="value == '{{ $m }}' ? 'active' : ''">{{ $m }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <!-- Period -->
                                                <div x-data="{ open: false, value: @entangle('working_hours_parts.'.$day.'.slots.'.$index.'.start_period') }" class="relative">
                                                    <button type="button" @click="open = !open" class="time-unit-btn font-black text-[10px] text-primary uppercase" x-text="value"></button>
                                                    <div x-show="open" @click.away="open = false" x-transition class="absolute top-full left-1/2 -translate-x-1/2 mt-2 dropdown-menu min-w-[70px]">
                                                        @foreach(['AM', 'PM'] as $p)
                                                            <div @click="value = '{{ $p }}'; open = false" class="dropdown-item text-[10px]" :class="value == '{{ $p }}' ? 'active' : ''">{{ $p }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <span class="text-outline-variant font-black text-xs">—</span>

                                            <!-- END TIME -->
                                            <div class="flex items-center gap-1.5 time-picker-container px-2.5 py-1.5 rounded-xl shadow-sm border border-outline-variant/40">
                                                <!-- Hour -->
                                                <div x-data="{ open: false, value: @entangle('working_hours_parts.'.$day.'.slots.'.$index.'.end_hour') }" class="relative">
                                                    <button type="button" @click="open = !open" class="time-unit-btn font-black text-base" x-text="value"></button>
                                                    <div x-show="open" @click.away="open = false" x-transition class="absolute top-full left-1/2 -translate-x-1/2 mt-2 dropdown-menu min-w-[70px]">
                                                        @foreach(range(1, 12) as $h)
                                                            @php $val = str_pad($h, 2, '0', STR_PAD_LEFT); @endphp
                                                            <div @click="value = '{{ $val }}'; open = false" class="dropdown-item text-xs" :class="value == '{{ $val }}' ? 'active' : ''">{{ $val }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <span class="font-black text-outline-variant text-xs">:</span>
                                                <!-- Min -->
                                                <div x-data="{ open: false, value: @entangle('working_hours_parts.'.$day.'.slots.'.$index.'.end_min') }" class="relative">
                                                    <button type="button" @click="open = !open" class="time-unit-btn font-black text-base" x-text="value"></button>
                                                    <div x-show="open" @click.away="open = false" x-transition class="absolute top-full left-1/2 -translate-x-1/2 mt-2 dropdown-menu min-w-[70px]">
                                                        @foreach(['00', '15', '30', '45'] as $m)
                                                            <div @click="value = '{{ $m }}'; open = false" class="dropdown-item text-xs" :class="value == '{{ $m }}' ? 'active' : ''">{{ $m }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <!-- Period -->
                                                <div x-data="{ open: false, value: @entangle('working_hours_parts.'.$day.'.slots.'.$index.'.end_period') }" class="relative">
                                                    <button type="button" @click="open = !open" class="time-unit-btn font-black text-[10px] text-primary uppercase" x-text="value"></button>
                                                    <div x-show="open" @click.away="open = false" x-transition class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 dropdown-menu min-w-[70px]">
                                                        @foreach(['AM', 'PM'] as $p)
                                                            <div @click="value = '{{ $p }}'; open = false" class="dropdown-item text-[10px]" :class="value == '{{ $p }}' ? 'active' : ''">{{ $p }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- REMOVE BUTTON -->
                                            <button type="button" wire:click="removeTimeRange('{{ $day }}', {{ $index }})" 
                                                    class="w-8 h-8 flex items-center justify-center text-outline-variant hover:text-error hover:bg-error/10 rounded-lg transition-all">
                                                <span class="material-symbols-outlined text-lg">close</span>
                                            </button>
                                        </div>
                                    @endforeach

                                    <!-- ADD BUTTON -->
                                    <button type="button" wire:click="addTimeRange('{{ $day }}')" 
                                            class="flex items-center gap-2 px-4 py-2 rounded-xl border border-dashed border-outline-variant text-outline font-bold text-[10px] hover:border-primary hover:text-primary hover:bg-primary/5 transition-all group mt-2">
                                        <span class="material-symbols-outlined text-sm group-hover:rotate-90 transition-transform">add</span>
                                        ADD SLOT
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Footer Actions -->
            <div class="p-8 bg-surface-container-low border-t border-outline-variant flex justify-end gap-4">
                <a href="{{ route('doctor.profile.detail', $doctor->id) }}" wire:navigate
                   class="px-8 py-4 bg-surface border-2 border-outline-variant text-on-background rounded-2xl font-black text-sm hover:border-primary transition-all">
                    Discard Changes
                </a>
                <button wire:click="update" wire:loading.attr="disabled"
                        class="px-10 py-4 bg-primary text-on-primary rounded-2xl font-black text-sm flex items-center gap-2 hover:bg-primary/90 transition-all shadow-lg shadow-primary/25 disabled:opacity-50">
                    Update Profile
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .time-picker-container {
        position: relative;
        z-index: 1;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(var(--primary-rgb), 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .time-picker-container:focus-within {
        z-index: 100;
        background: white;
        border-color: var(--primary);
        box-shadow: 0 10px 25px -5px rgba(var(--primary-rgb), 0.1);
    }
    .dropdown-menu {
        background: white;
        border: 1px solid rgba(var(--primary-rgb), 0.1);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        z-index: 100;
        max-height: 200px;
        overflow-y: auto;
    }
    .dropdown-item {
        padding: 8px 12px;
        font-weight: 700;
        color: var(--on-background);
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .dropdown-item:hover {
        background: var(--primary);
        color: var(--on-primary);
    }
    .dropdown-item.active {
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
    }
    .time-unit-btn {
        padding: 2px 6px;
        border-radius: 6px;
        transition: all 0.2s;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 1px;
    }
    .time-unit-btn:hover {
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
    }
    
    [x-cloak] { display: none !important; }
</style>
@script
<script>
    $wire.on('scroll-to-top', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>
@endscript
