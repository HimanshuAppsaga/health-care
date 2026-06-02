<div class="px-4 sm:px-8 py-6 sm:py-8">
    <div class="max-w-xl mx-auto">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-black text-primary mb-2">Assign Role</h1>
            <p class="text-outline font-medium">Search for a registered user by their email address and assign them a new staff role.</p>
        </div>

        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl flex items-center gap-3 animate-fade-in">
                <span class="material-symbols-outlined text-green-600">check_circle</span>
                <span class="font-bold">{{ session('message') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-3 animate-fade-in">
                <span class="material-symbols-outlined text-red-600">error</span>
                <span class="font-bold">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Card Container -->
        <div class="bg-surface rounded-[2rem] clinical-shadow border border-outline-variant overflow-hidden bg-white">
            <!-- Card Header -->
            <div class="p-6 sm:p-8 border-b border-outline-variant bg-surface-container-low">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary-container/20 rounded-2xl flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">shield_person</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-on-surface">Role Assignment</h2>
                        <p class="text-sm text-outline font-medium">Update database records for staff authorization roles.</p>
                    </div>
                </div>
            </div>

            <!-- Card Body Form -->
            <div class="p-6 sm:p-8">
                <form wire:submit.prevent="assign" class="space-y-6">
                    <!-- Email Input -->
                    <div>
                        <label for="email" class="text-sm font-bold text-on-surface mb-2 block">User Email Address</label>
                        <div class="relative">
                            <input 
                                type="email" 
                                id="email"
                                wire:model="email"
                                placeholder="e.g. staff@example.com"
                                class="w-full bg-surface border-2 border-outline-variant rounded-2xl px-6 py-4 text-base font-bold text-on-surface focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all outline-none"
                            >
                        </div>
                        @error('email') 
                            <span class="text-error text-xs font-bold mt-2 block">{{ $message }}</span> 
                        @enderror
                    </div>

                    <!-- Role Select -->
                    <div>
                        <label for="role_id" class="text-sm font-bold text-on-surface mb-2 block">Select New Role</label>
                        <div class="relative">
                            <select 
                                id="role_id"
                                wire:model="role_id"
                                class="w-full bg-surface border-2 border-outline-variant rounded-2xl px-6 py-4 text-base font-bold text-on-surface focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all outline-none appearance-none"
                            >
                                <option value="">-- Choose a Role --</option>
                                <option value="1">Doctor</option>
                                <option value="2">Receptionist</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-6 text-outline">
                                <span class="material-symbols-outlined">arrow_drop_down</span>
                            </div>
                        </div>
                        @error('role_id') 
                            <span class="text-error text-xs font-bold mt-2 block">{{ $message }}</span> 
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full py-4 bg-primary text-white rounded-2xl font-black text-lg clinical-shadow shadow-primary/30 hover:bg-primary-container hover:text-on-primary-container transition-all active:scale-95 flex items-center justify-center gap-3 cursor-pointer"
                        >
                            <span wire:loading.remove class="material-symbols-outlined">save</span>
                            <span wire:loading class="material-symbols-outlined animate-spin">refresh</span>
                            <span>Assign and Update Role</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.3s ease-out forwards;
    }
    .clinical-shadow {
        box-shadow: 0 10px 40px -10px rgba(0, 91, 176, 0.1);
    }
</style>
