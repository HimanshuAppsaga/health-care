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

                        <!-- Joining Date -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Joining Date</label>
                            <input wire:model="joining_date" type="date" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20">
                            @error('joining_date') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <!-- New Password -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">New Password</label>
                            <input wire:model="password" type="password" placeholder="Leave blank to keep current" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20">
                            @error('password') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
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
                    <div class="w-32 h-32 rounded-3xl bg-indigo-50 flex items-center justify-center overflow-hidden mb-6 border-4 border-indigo-50">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($name) }}&background=EEF2FF&color=4F46E5&size=128" alt="{{ $name }}" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">{{ $name }}</h3>
                    <p class="text-sm font-semibold text-indigo-600">{{ $employee_id }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
