<div class="max-w-6xl mx-auto pt-6">    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left Column: Flow Steps -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Section 4: Visit Info -->
            <section class="bg-surface-container-lowest p-6 rounded-xl border border-slate-100 shadow-sm animate-fade-in">
                @if($generatedToken)
                    <div class="text-center space-y-4 py-8 animate-fade-in">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-800">Appointment Confirmed!</h2>
                        <p class="text-slate-500">Your token number is:</p>
                        <div class="inline-block px-6 py-3 bg-indigo-50 border border-indigo-100 rounded-xl">
                            <span class="text-3xl font-extrabold tracking-widest text-indigo-700">{{ $generatedToken }}</span>
                        </div>
                        <div class="pt-6">
                            <button wire:click="$set('generatedToken', null)" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                Book Another Appointment
                            </button>
                        </div>
                    </div>
                @else
                <div class="space-y-4">
                    @if (session()->has('message'))
                        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-100 flex items-center gap-2 animate-fade-in" role="alert">
                            <span class="material-symbols-outlined text-lg">check_circle</span>
                            <span class="font-bold">{{ session('message') }}</span>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-100 flex items-center gap-2 animate-fade-in" role="alert">
                            <span class="material-symbols-outlined text-lg">error</span>
                            <span class="font-bold">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if(empty($availableSlots))
                        <div class="mb-6 p-4 bg-red-50 text-red-500 rounded-xl text-xs font-bold border border-red-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">event_busy</span>
                            No slots available for today.
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Full Name</label>
                            <input type="text" wire:model="name" class="w-full p-3 border border-slate-200 rounded-xl focus:ring-primary focus:border-primary text-sm bg-white" placeholder="Enter patient name"/>
                            @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Mobile No.</label>
                            <input type="text" wire:model="phone" class="w-full p-3 border border-slate-200 rounded-xl focus:ring-primary focus:border-primary text-sm bg-white" placeholder="Enter mobile number"/>
                            @error('phone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="pt-6">
                        <button wire:click="bookAppointment" 
                                wire:loading.attr="disabled"
                                @if(empty($availableSlots)) disabled @endif
                                class="group relative w-full py-4 bg-gradient-to-r from-primary to-indigo-600 text-white rounded-2xl font-bold text-lg shadow-[0_10px_25px_-5px_rgba(var(--primary-rgb),0.4)] hover:shadow-[0_20px_35px_-10px_rgba(var(--primary-rgb),0.5)] transition-all duration-300 transform hover:-translate-y-1 active:scale-[0.98] flex items-center justify-center overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                            <!-- Shimmer Effect -->
                            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-shimmer"></div>
                            
                            <span wire:loading.remove wire:target="bookAppointment" class="flex items-center space-x-2 relative z-10">
                                <span>Confirm & Book Appointment</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            
                            <span wire:loading wire:target="bookAppointment" class="flex items-center relative z-10">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Securing your slot...
                            </span>
                        </button>
                    </div>
                </div>
                @endif
            </section>
       </div>   
    </div>
</div>

<style>
    .animate-fade-in {
        animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes shimmer {
        100% { transform: translateX(100%); }
    }
    .animate-shimmer {
        animation: shimmer 1.5s infinite;
    }
    :root {
        --primary-rgb: 79, 70, 229; 
    }
</style>
