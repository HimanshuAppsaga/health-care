<div class="px-8 py-8">
    {{-- EMPTY STATE --}}
    @if(!$doctor)
        <div class="max-w-5xl mx-auto p-12 bg-error-container/10 border border-error/20 rounded-[2rem] text-center">
            <span class="material-symbols-outlined text-6xl text-error mb-4">error</span>
            <h2 class="text-2xl font-black text-on-error-container">Doctor Not Found</h2>
            <p class="text-outline mt-2">The doctor profile you are looking for does not exist or has been removed.</p>
        </div>
    @else

    <div class="max-w-5xl mx-auto space-y-8">
        {{-- HEADER CARD --}}
        <div class="bg-surface rounded-[2.5rem] p-10 clinical-shadow border border-outline-variant flex flex-col md:flex-row gap-8 items-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 blur-3xl rounded-full -mr-32 -mt-32"></div>
            
            {{-- PROFILE PHOTO --}}
            <div class="relative w-32 h-32 rounded-3xl overflow-hidden bg-surface-container-low flex items-center justify-center border-2 border-outline-variant shadow-inner group">
                @if($doctor->user->profile_photo_path)
                    <img src="{{ asset('storage/'.$doctor->user->profile_photo_path) }}" class="w-50% h-50% object-cover group-hover:scale-110 transition-transform duration-500">
                @else
                    <span class="material-symbols-outlined text-5xl text-outline-variant">person</span>
                @endif
            </div>

            {{-- NAME + SPECIALIZATION --}}
            <div class="flex-1 text-center md:text-left relative">
                <div class="flex flex-col md:flex-row md:items-center gap-4 mb-3">
                    <h1 class="text-4xl font-black text-on-background tracking-tight">
                        {{ $doctor->user->name }}
                    </h1>
                    <span class="px-3 py-1 bg-primary/10 text-primary text-[10px] font-black uppercase rounded-full border border-primary/20 self-center md:self-auto">
                        {{ $doctor->specialization ?? 'General Practitioner' }}
                    </span>
                </div>

                <p class="text-lg text-outline font-medium leading-relaxed max-w-2xl">
                    {{ $doctor->qualification ?? 'MBBS' }} • {{ $doctor->experience_years ?? 0 }} Years Experience
                </p>
                
                <div class="mt-6 flex flex-wrap gap-3 justify-center md:justify-start">
                    <a href="{{ route('doctor.profile.edit', $doctor->id) }}" wire:navigate class="px-6 py-3 bg-primary text-on-primary rounded-2xl font-black text-sm flex items-center gap-2 hover:bg-primary/90 transition-all shadow-lg shadow-primary/25">
                        <span class="material-symbols-outlined text-lg">edit</span>
                        Edit Profile
                    </a>
                </div>
            </div>
        </div>

        {{-- INFO GRID --}}
        <div class="grid md:grid-cols-3 gap-8">
            {{-- PROFESSIONAL INFO & BIO --}}
            <div class="md:col-span-2 space-y-8">
                <div class="bg-surface rounded-[2rem] p-8 clinical-shadow border border-outline-variant">
                    <h2 class="text-xl font-black text-on-background mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">medical_information</span>
                        Professional Details
                    </h2>
                    
                    <div class="grid sm:grid-cols-2 gap-6">
                        <div class="p-6 rounded-2xl bg-surface-container-low border border-outline-variant/30">
                            <p class="text-xs font-black text-outline-variant uppercase tracking-widest mb-2">Consultation Fee</p>
                            <p class="text-lg font-bold text-on-background flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary">payments</span>
                                {{ $doctor->consultation_fee ?? '0.00' }}
                            </p>
                        </div>
                        <div class="p-6 rounded-2xl bg-surface-container-low border border-outline-variant/30">
                            <p class="text-xs font-black text-outline-variant uppercase tracking-widest mb-2">Phone Number</p>
                            <p class="text-lg font-bold text-on-background flex items-start gap-2">
                                <span class="material-symbols-outlined text-secondary">phone</span>
                                {{ $doctor->user->phone ?? 'Not Provided' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- BIO --}}
                @if($doctor->user->bio)
                <div class="bg-surface rounded-[2rem] p-8 clinical-shadow border border-outline-variant">
                    <h2 class="text-xl font-black text-on-background mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">info</span>
                        About Doctor
                    </h2>
                    <div class="prose prose-blue max-w-none">
                        <p class="text-on-surface-variant leading-relaxed font-medium">
                            {{ $doctor->user->bio }}
                        </p>
                    </div>
                </div>
                @endif
            </div>

            {{-- WORKING HOURS --}}
            <div class="space-y-8">
                <div class="bg-surface rounded-[2rem] p-8 clinical-shadow border border-outline-variant">
                    <h2 class="text-xl font-black text-on-background mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">schedule</span>
                        Working Hours
                    </h2>

                    @php
                        $hours = is_array($doctor->working_hours)
                            ? $doctor->working_hours
                            : json_decode($doctor->working_hours, true);
                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    @endphp

                    <div class="space-y-2">
                        @foreach($days as $day)
                            @php $time = $hours[$day] ?? 'Closed'; @endphp
                            <div class="flex justify-between items-start p-3 rounded-xl transition-all duration-300 {{ $time === 'Closed' ? 'bg-error-container/5 opacity-60' : 'bg-surface-container-low border border-outline-variant/20 hover:border-primary/30 hover:bg-primary/5' }}">
                                
                                {{-- Day Label --}}
                                <div class="flex items-center gap-2 mt-0.5">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $time === 'Closed' ? 'bg-outline-variant' : 'bg-primary' }}"></div>
                                    <span class="capitalize text-xs font-black {{ $time === 'Closed' ? 'text-outline' : 'text-on-background' }}">{{ $day }}</span>
                                </div>

                                {{-- Time Slots --}}
                                <div class="flex flex-col items-end gap-1.5">
                                    @if($time === 'Closed')
                                        <span class="text-xs font-bold text-error bg-error-container/10 px-2.5 py-0.5 rounded-md border border-error/20">Closed</span>
                                    @else
                                        @php
                                            if (is_array($time)) {
                                                $slots = $time;
                                            } else {
                                                $slots = str_contains($time, ',') ? array_unique(array_map('trim', explode(',', $time))) : [trim($time)];
                                            }
                                        @endphp
                                        @foreach($slots as $slot)
                                            <span class="text-[11px] font-bold text-primary bg-primary/5 px-2.5 py-0.5 rounded-md border border-primary/10 hover:bg-primary/10 transition-colors cursor-default whitespace-nowrap">
                                                {{ is_string($slot) ? trim($slot) : '' }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-outline-variant/30 text-center">
                        <p class="text-[10px] font-black text-outline-variant uppercase tracking-widest">Last Updated</p>
                        <p class="text-xs font-bold text-outline">{{ $doctor->updated_at->format('M d, Y • h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
