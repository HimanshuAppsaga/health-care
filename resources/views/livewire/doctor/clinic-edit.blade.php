<div class="px-8 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('doctor.clinic.detail', $clinic->id) }}" wire:navigate class="w-10 h-10 rounded-xl bg-surface border border-outline-variant flex items-center justify-center text-outline hover:text-primary hover:border-primary transition-all">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <h1 class="text-3xl font-black text-on-background tracking-tight">Edit Clinic Profile</h1>
                </div>
                <p class="text-outline font-medium">Update your clinic's public information, location, and operations.</p>
            </div>
            
            <button wire:click="update" wire:loading.attr="disabled"
                    class="px-8 py-4 bg-primary text-on-primary rounded-2xl font-black text-sm flex items-center gap-2 hover:bg-primary/90 transition-all shadow-lg shadow-primary/25 disabled:opacity-50">
                <span class="material-symbols-outlined text-lg" wire:loading.remove wire:target="update">save</span>
                <span class="animate-spin material-symbols-outlined text-lg" wire:loading wire:target="update">sync</span>
                Save Changes
            </button>
        </div>

        <!-- Success Message -->
        @if (session()->has('success'))
            <div class="mb-8 p-5 bg-primary/10 border border-primary/20 text-primary rounded-2xl flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                <div class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined font-black">check_circle</span>
                </div>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Main Form Card -->
        <div class="bg-surface rounded-[2.5rem] border border-outline-variant clinical-shadow overflow-hidden">
            <div class="p-10 space-y-12">
                
                {{-- LOGO SECTION --}}
                <div class="bg-surface-container-low/50 p-8 rounded-3xl border border-outline-variant/30">
                    <h3 class="text-lg font-black text-on-background mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">image</span>
                        Brand Identity
                    </h3>
                    
                    <div class="flex flex-col md:flex-row gap-8 items-start">
                        <div class="relative group">
                            <div class="w-40 h-40 rounded-3xl overflow-hidden bg-surface flex items-center justify-center border-2 border-outline-variant shadow-inner">
                                @if ($logo)
                                    <img src="{{ $logo->temporaryUrl() }}" class="w-50% h-50% object-contain">
                                @elseif($clinic->logo && !$removeLogo)
                                    <img src="{{ asset('storage/'.$clinic->logo) }}" class="w-50% h-50% object-contain">
                                @else
                                    <span class="material-symbols-outlined text-6xl text-outline-variant">apartment</span>
                                @endif
                            </div>
                            
                            @if(($clinic->logo && !$removeLogo) || $logo)
                                <button wire:click="{{ $logo ? 'cancelNewLogo' : 'removeExistingLogo' }}" 
                                        class="absolute -top-3 -right-3 w-10 h-10 bg-error text-on-error rounded-full shadow-lg flex items-center justify-center hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined text-xl">close</span>
                                </button>
                            @endif
                        </div>

                        <div class="flex-1 space-y-4">
                            <p class="text-sm font-bold text-outline uppercase tracking-widest">Clinic Logo</p>
                            <p class="text-xs text-outline-variant leading-relaxed">Upload a high-resolution logo for your clinic. Recommended size 512x512px. PNG or JPG format (max 2MB).</p>
                            
                            <label class="inline-flex items-center gap-2 px-6 py-3 bg-surface-container border border-outline-variant rounded-xl cursor-pointer hover:bg-surface-container-high transition-all">
                                <span class="material-symbols-outlined text-primary">upload</span>
                                <span class="text-sm font-bold">Choose File</span>
                                <input type="file" wire:model="logo" class="hidden">
                            </label>
                            
                            <div wire:loading wire:target="logo" class="flex items-center gap-2 text-sm text-primary font-bold">
                                <span class="animate-spin material-symbols-outlined text-lg">sync</span>
                                Uploading preview...
                            </div>
                            @error('logo') <p class="text-xs text-error font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- BASIC INFO --}}
                <div class="grid md:grid-cols-2 gap-10">
                    <div class="space-y-6">
                        <h3 class="text-lg font-black text-on-background mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">info</span>
                            General Information
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="group">
                                <label class="text-xs font-black text-outline uppercase tracking-widest ml-4 mb-2 block group-focus-within:text-primary transition-colors">Clinic Name</label>
                                <input type="text" wire:model="name"
                                       class="w-full bg-surface-container-low border-2 border-outline-variant/30 rounded-2xl px-6 py-4 font-bold focus:border-primary focus:ring-0 transition-all placeholder:text-outline-variant"
                                       placeholder="Enter clinic name">
                                @error('name') <p class="text-xs text-error font-bold mt-1 ml-4">{{ $message }}</p> @enderror
                            </div>

                            <div class="group">
                                <label class="text-xs font-black text-outline uppercase tracking-widest ml-4 mb-2 block group-focus-within:text-primary transition-colors">Short Description</label>
                                <textarea wire:model="description" rows="3"
                                          class="w-full bg-surface-container-low border-2 border-outline-variant/30 rounded-2xl px-6 py-4 font-bold focus:border-primary focus:ring-0 transition-all placeholder:text-outline-variant"
                                          placeholder="A brief tagline or summary..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h3 class="text-lg font-black text-on-background mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">call</span>
                            Contact Details
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="group">
                                <label class="text-xs font-black text-outline uppercase tracking-widest ml-4 mb-2 block group-focus-within:text-primary transition-colors">Contact Number</label>
                                <input type="text" wire:model="contact_number"
                                       class="w-full bg-surface-container-low border-2 border-outline-variant/30 rounded-2xl px-6 py-4 font-bold focus:border-primary focus:ring-0 transition-all placeholder:text-outline-variant"
                                       placeholder="+1 234 567 890">
                            </div>

                            <div class="group">
                                <label class="text-xs font-black text-outline uppercase tracking-widest ml-4 mb-2 block group-focus-within:text-primary transition-colors">Full Address</label>
                                <input type="text" wire:model="address"
                                       class="w-full bg-surface-container-low border-2 border-outline-variant/30 rounded-2xl px-6 py-4 font-bold focus:border-primary focus:ring-0 transition-all placeholder:text-outline-variant"
                                       placeholder="123 Medical St, Health City">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- EXTENDED INFO --}}
                <div class="space-y-6">
                    <h3 class="text-lg font-black text-on-background mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">description</span>
                        In-Depth Profile
                    </h3>
                    
                    <div class="group">
                        <label class="text-xs font-black text-outline uppercase tracking-widest ml-4 mb-2 block group-focus-within:text-primary transition-colors">About Clinic (Comprehensive)</label>
                        <textarea wire:model="about_clinic" rows="6"
                                  class="w-full bg-surface-container-low border-2 border-outline-variant/30 rounded-[2rem] px-8 py-6 font-medium leading-relaxed focus:border-primary focus:ring-0 transition-all placeholder:text-outline-variant"
                                  placeholder="Describe your clinic's mission, specialties, and history..."></textarea>
                    </div>
                </div>

                {{-- LOCATION & HOURS --}}
                <div class="grid md:grid-cols-2 gap-10">
                    <div class="space-y-6">
                        <h3 class="text-lg font-black text-on-background mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">location_on</span>
                            Map Coordinates
                        </h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="group">
                                <label class="text-[10px] font-black text-outline-variant uppercase tracking-widest ml-4 mb-1 block">Latitude</label>
                                <input type="text" wire:model="latitude" placeholder="e.g. 40.7128"
                                       class="w-full bg-surface-container-low border-2 border-outline-variant/30 rounded-xl px-4 py-3 font-bold focus:border-primary transition-all">
                            </div>
                            <div class="group">
                                <label class="text-[10px] font-black text-outline-variant uppercase tracking-widest ml-4 mb-1 block">Longitude</label>
                                <input type="text" wire:model="longitude" placeholder="e.g. -74.0060"
                                       class="w-full bg-surface-container-low border-2 border-outline-variant/30 rounded-xl px-4 py-3 font-bold focus:border-primary transition-all">
                            </div>
                        </div>
                        <p class="text-[10px] text-outline italic ml-2">Coordinates are used to show your clinic on maps and for patient discovery.</p>
                    </div>

                    <div class="space-y-6">
                        <h3 class="text-lg font-black text-on-background mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">schedule</span>
                            Operating Hours
                        </h3>
                        
                        <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                            @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                                <div class="flex items-center gap-4 bg-surface-container-low/30 p-2 rounded-2xl border border-outline-variant/10">
                                    <span class="w-24 capitalize text-xs font-black text-outline pl-2">{{ $day }}</span>
                                    <input type="text"
                                           wire:model="working_hours.{{ $day }}"
                                           class="flex-1 bg-surface border-2 border-outline-variant/20 rounded-xl px-4 py-2 text-sm font-bold focus:border-primary transition-all"
                                           placeholder="e.g. 09:00 AM - 06:00 PM">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Actions -->
            <div class="p-8 bg-surface-container-low border-t border-outline-variant flex justify-end gap-4">
                <a href="{{ route('doctor.clinic.detail', $clinic->id) }}" wire:navigate
                   class="px-8 py-4 bg-surface border-2 border-outline-variant text-on-background rounded-2xl font-black text-sm hover:border-primary transition-all">
                    Discard Changes
                </a>
                <button wire:click="update" wire:loading.attr="disabled"
                        class="px-10 py-4 bg-primary text-on-primary rounded-2xl font-black text-sm flex items-center gap-2 hover:bg-primary/90 transition-all shadow-lg shadow-primary/25 disabled:opacity-50">
                    Update Clinic Profile
                </button>
            </div>
        </div>
    </div>
</div>
