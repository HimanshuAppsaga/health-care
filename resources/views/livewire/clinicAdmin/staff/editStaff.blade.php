<div class="p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Breadcrumbs and Header -->
        <div class="mb-8">
            <nav class="flex text-sm font-medium text-gray-400 mb-2">
                <a href="{{ route('admin.staff.index') }}" wire:navigate class="hover:text-indigo-600 transition-colors">Staff Management</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Edit Staff Profile</span>
            </nav>
            <h1 class="text-3xl font-bold text-gray-900">Edit Staff Profile</h1>
            <p class="text-gray-500 mt-1">Update information and access permissions for {{ $name }}.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form Card -->
            <div class="lg:col-span-2 space-y-8">
                <form wire:submit="update" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                                <x-icon name="pencil" class="w-5 h-5 text-amber-600" />
                            </div>
                            <h2 class="text-xl font-bold text-gray-900">Update Profile</h2>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-gray-400 uppercase">Active Status</span>
                            <button type="button" wire:click="$toggle('is_active')" class="w-10 h-5 rounded-full p-1 transition-colors {{ $is_active ? 'bg-indigo-600' : 'bg-gray-200' }} relative">
                                <div class="w-3 h-3 bg-white rounded-full transition-transform {{ $is_active ? 'translate-x-5' : 'translate-x-0' }}"></div>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Full Name -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Full Name</label>
                            <input wire:model="name" type="text" placeholder="e.g. Dr. Jonathan Harker" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20">
                            @error('name') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <!-- Email Address -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Email Address</label>
                            <input wire:model="email" type="email" placeholder="jonathan.h@clinic.com" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20">
                            @error('email') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <!-- Phone Number -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Phone Number</label>
                            <input wire:model="phone" type="text" placeholder="+1 (555) 000-0000" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20">
                            @error('phone') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <!-- Role -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Role</label>
                            <select wire:model="role_id" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ ucwords(str_replace('_', ' ', $role->name)) }}</option>
                                @endforeach
                            </select>
                            @error('role_id') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <!-- Employee ID -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Employee ID</label>
                            <input wire:model="employee_id" type="text" readonly class="w-full bg-gray-100 border-none rounded-xl py-3 px-4 text-sm font-bold text-gray-500 cursor-not-allowed">
                        </div>

                        <!-- Department -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Department</label>
                            <select wire:model="department" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">Select Department</option>
                                <option value="Cardiology">Cardiology</option>
                                <option value="Emergency Care">Emergency Care</option>
                                <option value="Neurology">Neurology</option>
                                <option value="Pediatrics">Pediatrics</option>
                                <option value="General Practice">General Practice</option>
                            </select>
                        </div>
                    </div>

                    <!-- Emergency & Address Section -->
                    <div class="mt-12 pt-8 border-t border-gray-50">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <x-icon name="shield-check" class="w-4 h-4 text-indigo-600" />
                            Emergency & Contact Details
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Emergency Contact Name</label>
                                <input wire:model="emergency_contact_name" type="text" placeholder="e.g. Jane Doe (Wife)" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20">
                                @error('emergency_contact_name') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Emergency Contact Phone</label>
                                <input wire:model="emergency_contact_phone" type="text" placeholder="+1 (555) 000-0000" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20">
                                @error('emergency_contact_phone') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Home Address</label>
                                <textarea wire:model="address" rows="3" placeholder="Enter full residential address" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20 resize-none"></textarea>
                                @error('address') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Employment Details Section -->
                    <div class="mt-12 pt-8 border-t border-gray-50">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <x-icon name="briefcase" class="w-4 h-4 text-indigo-600" />
                            Employment Details
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Assigned Unit</label>
                                <input wire:model="unit" type="text" placeholder="e.g. Trauma Center / ICU" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20">
                                @error('unit') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Supervisor Name</label>
                                <input wire:model="supervisor_name" type="text" placeholder="e.g. Dr. Julian Vance" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20">
                                @error('supervisor_name') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Admin Notes Section -->
                    <div class="mt-12 pt-8 border-t border-gray-50">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <x-icon name="sticky-note" class="w-4 h-4 text-indigo-600" />
                            Administrative Notes
                        </h3>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Bio / Performance Notes</label>
                            <textarea wire:model="bio" rows="4" placeholder="Enter any administrative or performance notes..." class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20 resize-none"></textarea>
                            @error('bio') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Security Section -->
                    <div class="mt-12 pt-8 border-t border-gray-50">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <x-icon name="shield-check" class="w-4 h-4 text-red-600" />
                            Security
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- New Password -->
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">New Password</label>
                                <input wire:model="password" type="password" placeholder="Leave blank to keep current" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20">
                                @error('password') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 pt-8 border-t border-gray-50 flex items-center justify-end gap-4">
                        <a href="{{ route('admin.staff.index') }}" wire:navigate class="px-8 py-3 text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="px-8 py-3 bg-[#4F46E5] hover:bg-[#4338CA] text-white font-bold rounded-2xl transition-all shadow-lg shadow-indigo-200">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Profile Overview Side -->
            <div class="space-y-8">
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 flex flex-col items-center">
                    <div class="relative group">
                        <div class="w-32 h-32 rounded-3xl bg-indigo-50 flex items-center justify-center overflow-hidden mb-6 border-4 border-indigo-50 shadow-inner">
                            @if ($photo)
                                <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                            @elseif($currentPhoto)
                                <img src="{{ asset('storage/' . $currentPhoto) }}" class="w-full h-full object-cover">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($name) }}&background=EEF2FF&color=4F46E5&size=128" alt="{{ $name }}" class="w-full h-full object-cover">
                            @endif
                            
                            <!-- Loading Overlay -->
                            <div wire:loading wire:target="photo" class="absolute inset-0 bg-white/80 flex items-center justify-center rounded-3xl">
                                <div class="w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                        </div>

                        <!-- Upload Button -->
                        <label class="absolute -bottom-2 -right-2 w-10 h-10 bg-white rounded-2xl shadow-lg border border-gray-100 flex items-center justify-center cursor-pointer hover:bg-gray-50 transition-all group-hover:scale-110 active:scale-95">
                            <x-icon name="plus" class="w-5 h-5 text-indigo-600" />
                            <input type="file" wire:model="photo" class="hidden" accept="image/*">
                        </label>
                    </div>

                    <div class="text-center">
                        <h3 class="text-xl font-bold text-gray-900">{{ $name }}</h3>
                        <p class="text-sm font-semibold text-indigo-600 mb-2">{{ $employee_id }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Profile Photo</p>
                        @error('photo') <p class="text-[10px] text-red-500 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
