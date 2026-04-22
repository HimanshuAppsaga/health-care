<div class="max-w-6xl mx-auto">
    <!-- Stepper Header -->
    <div class="mb-10 text-center">
        <h1 class="text-3xl font-extrabold tracking-tight text-on-surface mb-2">Book an Appointment</h1>
        <p class="text-on-surface-variant">Complete the following steps to schedule your visit.</p>
        
        <div class="mt-8 flex items-center justify-center space-x-2 md:space-x-4">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full {{ $step >= 1 ? 'bg-primary-container text-on-primary ring-4 ring-primary-fixed' : 'bg-slate-100 text-slate-400 border border-slate-200' }} flex items-center justify-center text-xs font-bold transition-all">1</div>
                <span class="text-[10px] md:text-xs mt-1 font-semibold {{ $step >= 1 ? 'text-primary' : 'text-slate-400' }}">Clinic</span>
            </div>
            <div class="w-8 md:w-16 h-[2px] {{ $step >= 2 ? 'bg-primary-fixed' : 'bg-slate-200' }} mb-4"></div>
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full {{ $step >= 2 ? 'bg-primary-container text-on-primary ring-4 ring-primary-fixed' : ($step == 2 ? 'bg-primary-fixed-dim text-primary-fixed' : 'bg-slate-100 text-slate-400 border border-slate-200') }} flex items-center justify-center text-xs font-bold transition-all">2</div>
                <span class="text-[10px] md:text-xs mt-1 font-medium {{ $step >= 2 ? 'text-primary' : 'text-slate-400' }}">Doctor</span>
            </div>
            <div class="w-8 md:w-16 h-[2px] {{ $step >= 3 ? 'bg-primary-fixed' : 'bg-slate-200' }} mb-4"></div>
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full {{ $step >= 3 ? 'bg-primary-container text-on-primary ring-4 ring-primary-fixed' : 'bg-slate-100 text-slate-400 border border-slate-200' }} flex items-center justify-center text-xs font-bold transition-all">3</div>
                <span class="text-[10px] md:text-xs mt-1 font-medium {{ $step >= 3 ? 'text-primary' : 'text-slate-400' }}">Time</span>
            </div>
            <div class="w-8 md:w-16 h-[2px] {{ $step >= 4 ? 'bg-primary-fixed' : 'bg-slate-200' }} mb-4"></div>
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full {{ $step >= 4 ? 'bg-primary-container text-on-primary ring-4 ring-primary-fixed' : 'bg-slate-100 text-slate-400 border border-slate-200' }} flex items-center justify-center text-xs font-bold transition-all">4</div>
                <span class="text-[10px] md:text-xs mt-1 font-medium {{ $step >= 4 ? 'text-primary' : 'text-slate-400' }}">Confirm</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left Column: Flow Steps -->
        <div class="lg:col-span-8 space-y-6">
            
            @if($step == 1)
            <!-- Section 1: Clinic Selection -->
            <section class="bg-surface-container-lowest p-6 rounded-xl border border-slate-100 shadow-sm animate-fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold flex items-center">
                        <span class="material-symbols-outlined mr-2 text-primary">location_on</span>
                        Select Clinic
                    </h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($clinics as $clinic)
                    <label class="relative cursor-pointer group">
                        <input type="radio" wire:model.live="selectedClinicId" value="{{ $clinic->id }}" class="peer sr-only"/>
                        <div class="p-4 border-2 border-slate-100 rounded-xl bg-white peer-checked:border-primary peer-checked:bg-primary-fixed/20 transition-all hover:border-primary-fixed">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-bold text-on-surface">{{ $clinic->name }}</p>
                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $clinic->address }}</p>
                                </div>
                                <span class="material-symbols-outlined text-primary opacity-0 peer-checked:opacity-100 transition-opacity" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            </div>
                            <div class="mt-3 flex items-center gap-2">
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-bold rounded uppercase">Open Now</span>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
                
                <div class="mt-8 flex justify-end">
                    <button wire:click="nextStep" class="bg-primary text-white px-8 py-3 rounded-xl font-bold hover:bg-primary-container transition-all active:scale-95 disabled:opacity-50" {{ !$selectedClinicId ? 'disabled' : '' }}>
                        Continue to Doctors
                    </button>
                </div>
            </section>
            @endif

            @if($step == 2)
            <!-- Section 2: Doctor Selection -->
            <section class="bg-surface-container-lowest p-6 rounded-xl border border-slate-100 shadow-sm animate-fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold flex items-center">
                        <span class="material-symbols-outlined mr-2 text-primary">person_search</span>
                        Choose your Doctor
                    </h2>
                    <button wire:click="previousStep" class="text-slate-500 text-sm flex items-center hover:text-primary">
                        <span class="material-symbols-outlined text-sm mr-1">arrow_back</span> Back
                    </button>
                </div>
                <div class="space-y-4">
                    @forelse($doctors as $doctor)
                    <label class="relative cursor-pointer block">
                        <input type="radio" wire:model.live="selectedDoctorId" value="{{ $doctor->id }}" class="peer sr-only"/>
                        <div class="p-4 border-2 border-slate-100 rounded-xl bg-white peer-checked:border-primary transition-all hover:bg-slate-50 flex flex-col md:flex-row md:items-center gap-4">
                            <img src="{{ $doctor->user->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($doctor->user->name) }}" alt="{{ $doctor->user->name }}" class="w-16 h-16 rounded-lg object-cover"/>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-on-surface">{{ $doctor->user->name }}</h3>
                                    <span class="text-primary font-bold">${{ number_format($doctor->consultation_fee, 2) }}</span>
                                </div>
                                <p class="text-xs text-secondary font-semibold">{{ $doctor->specialization }} • {{ $doctor->experience_years }} Years Exp.</p>
                                <div class="mt-2 flex items-center gap-3">
                                    <span class="flex items-center text-[11px] text-slate-500">
                                        <span class="material-symbols-outlined text-amber-500 text-xs mr-1" style="font-variation-settings: 'FILL' 1;">star</span>
                                        4.9 (120 reviews)
                                    </span>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-primary self-center hidden md:block opacity-0 peer-checked:opacity-100 transition-opacity" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                        </div>
                    </label>
                    @empty
                    <div class="text-center py-8">
                        <p class="text-slate-500">No doctors available for this clinic.</p>
                    </div>
                    @endforelse
                </div>

                <div class="mt-8 flex justify-end">
                    <button wire:click="nextStep" class="bg-primary text-white px-8 py-3 rounded-xl font-bold hover:bg-primary-container transition-all active:scale-95 disabled:opacity-50" {{ !$selectedDoctorId ? 'disabled' : '' }}>
                        Continue to Schedule
                    </button>
                </div>
            </section>
            @endif

            @if($step == 3)
            <!-- Section 3: Appointment Details -->
            <section class="bg-surface-container-lowest p-6 rounded-xl border border-slate-100 shadow-sm animate-fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold flex items-center">
                        <span class="material-symbols-outlined mr-2 text-primary">event</span>
                        Select Date & Time
                    </h2>
                    <button wire:click="previousStep" class="text-slate-500 text-sm flex items-center hover:text-primary">
                        <span class="material-symbols-outlined text-sm mr-1">arrow_back</span> Back
                    </button>
                </div>
                
                <div class="flex flex-col md:flex-row gap-8">
                    <!-- Date Picker -->
                    <div class="flex-1">
                        <p class="text-xs font-bold text-slate-500 mb-4 uppercase tracking-wider">Select Date</p>
                        <input type="date" wire:model.live="selectedDate" class="w-full p-3 border border-slate-200 rounded-xl focus:ring-primary focus:border-primary text-sm bg-white" min="{{ date('Y-m-d') }}"/>
                        
                        <div class="mt-6 p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                <span class="font-bold">Note:</span> Showing availability for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}.
                            </p>
                        </div>
                    </div>

                    <!-- Time Slots Grid -->
                    <div class="w-full md:w-56">
                        <p class="text-xs font-bold text-slate-500 mb-4 uppercase tracking-wider">Available Slots</p>
                        <div class="grid grid-cols-2 gap-2">
                            @forelse($availableSlots as $slot)
                            <button wire:click="selectSlot('{{ $slot }}')" class="py-2 text-xs border {{ $selectedSlot == $slot ? 'border-2 border-primary bg-primary-fixed/20 text-primary font-bold' : 'border-slate-200 rounded-lg hover:border-primary hover:text-primary transition-all font-medium' }} rounded-lg">
                                {{ $slot }}
                            </button>
                            @empty
                            <div class="col-span-2 text-center py-4">
                                <p class="text-[10px] text-red-500">No slots available for this date.</p>
                            </div>
                            @endforelse
                        </div>
                        <p class="mt-4 text-[10px] text-slate-400 italic text-center">Times are shown in local clinic time</p>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button wire:click="nextStep" class="bg-primary text-white px-8 py-3 rounded-xl font-bold hover:bg-primary-container transition-all active:scale-95 disabled:opacity-50" {{ !$selectedSlot ? 'disabled' : '' }}>
                        Continue to Confirmation
                    </button>
                </div>
            </section>
            @endif

            @if($step == 4)
            <!-- Section 4: Visit Info -->
            <section class="bg-surface-container-lowest p-6 rounded-xl border border-slate-100 shadow-sm animate-fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold flex items-center">
                        <span class="material-symbols-outlined mr-2 text-primary">edit_note</span>
                        Reason for Visit
                    </h2>
                    <button wire:click="previousStep" class="text-slate-500 text-sm flex items-center hover:text-primary">
                        <span class="material-symbols-outlined text-sm mr-1">arrow_back</span> Back
                    </button>
                </div>
                <div class="space-y-4">
                    <select wire:model="reason" class="w-full p-3 border border-slate-200 rounded-xl focus:ring-primary focus:border-primary text-sm bg-white">
                        <option value="">Select a reason...</option>
                        <option>Consultation</option>
                        <option>Routine Checkup</option>
                        <option>Follow-up</option>
                        <option>Emergency</option>
                        <option>Other</option>
                    </select>
                    @error('reason') <span class="text-xs text-red-500">{{ $message }}</span> @enderror

                    <textarea wire:model="notes" class="w-full p-4 border border-slate-200 rounded-xl focus:ring-primary focus:border-primary text-sm bg-white" placeholder="Describe your symptoms or concerns (optional)..." rows="3"></textarea>
                </div>

                <div class="mt-8 p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-3">
                    <span class="material-symbols-outlined text-blue-500 mt-0.5">info</span>
                    <div>
                        <p class="text-xs font-bold text-blue-700">Almost there!</p>
                        <p class="text-[11px] text-blue-600 leading-relaxed mt-1">Please review the summary on the right before confirming your appointment.</p>
                    </div>
                </div>
            </section>
            @endif
        </div>

        <!-- Right Column: Summary & Actions -->
        <div class="lg:col-span-4 space-y-6">
            <div class="sticky top-24">
                <div class="bg-primary-container text-on-primary p-6 rounded-2xl shadow-xl overflow-hidden relative">
                    <!-- Aesthetic Gradient Overlay -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    <h2 class="text-xl font-black mb-6 flex items-center justify-between">
                        Summary
                        <span class="material-symbols-outlined text-white/50">receipt_long</span>
                    </h2>
                    <div class="space-y-4 relative z-10">
                        <div class="flex justify-between items-start">
                            <div class="text-xs opacity-80">Clinic</div>
                            <div class="text-sm font-bold text-right">{{ $selectedClinic->name ?? 'Not selected' }}</div>
                        </div>
                        <div class="flex justify-between items-start">
                            <div class="text-xs opacity-80">Doctor</div>
                            <div class="text-sm font-bold text-right">{{ $selectedDoctor->user->name ?? 'Not selected' }}</div>
                        </div>
                        <div class="flex justify-between items-start">
                            <div class="text-xs opacity-80">Date & Time</div>
                            <div class="text-sm font-bold text-right">
                                @if($selectedDate && $selectedSlot)
                                    {{ \Carbon\Carbon::parse($selectedDate)->format('M j, Y') }} at {{ $selectedSlot }}
                                @else
                                    Not selected
                                @endif
                            </div>
                        </div>
                        <hr class="border-white/20 my-4"/>
                        <div class="flex justify-between items-center">
                            <div class="text-xs opacity-80">Consultation Fee</div>
                            <div class="text-sm font-bold">${{ number_format($fee, 2) }}</div>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="text-xs opacity-80">Service Tax (5%)</div>
                            <div class="text-sm font-bold">${{ number_format($tax, 2) }}</div>
                        </div>
                        <div class="pt-4 flex justify-between items-end">
                            <div class="text-sm font-bold uppercase tracking-widest">Total Pay</div>
                            <div class="text-3xl font-black">${{ number_format($total, 2) }}</div>
                        </div>
                    </div>
                </div>

                @if($step == 4)
                <div class="mt-6 space-y-4">
                    <button wire:click="bookAppointment" class="w-full bg-secondary-container text-on-secondary-container py-4 rounded-xl font-extrabold text-lg shadow-lg hover:bg-secondary-fixed transition-all active:scale-95 flex items-center justify-center gap-2">
                        Confirm Appointment
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    </button>
                </div>
                @endif

                <div class="mt-8 p-4 bg-tertiary-fixed/30 border border-tertiary-fixed rounded-xl flex items-start gap-3">
                    <span class="material-symbols-outlined text-tertiary-container mt-0.5">info</span>
                    <div>
                        <p class="text-xs font-bold text-tertiary-container">Cancellation Policy</p>
                        <p class="text-[11px] text-on-tertiary-fixed-variant leading-relaxed mt-1">Full refund if cancelled at least 24 hours before the appointment time. 50% fee thereafter.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
