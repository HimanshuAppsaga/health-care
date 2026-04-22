<div class="p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Staff Management</h1>
                <p class="text-gray-500 mt-1">Manage permissions, roles, and system access for all clinical staff members.</p>
            </div>
            <a href="{{ route('admin.staff.create') }}" wire:navigate class="inline-flex items-center gap-2 px-6 py-3 bg-[#4F46E5] hover:bg-[#4338CA] text-white font-bold rounded-2xl transition-all shadow-lg shadow-indigo-200">
                <x-icon name="plus" class="w-5 h-5" />
                Invite Staff
            </a>
        </div>

        <!-- Stats and Filters -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Stats Card -->
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Total Active Staff</p>
                    <h2 class="text-5xl font-black text-[#310E93]">{{ $totalActiveStaff }}</h2>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-sm font-bold text-green-500">
                        <x-icon name="trending-up" class="w-4 h-4" />
                        +4
                    </span>
                    <span class="text-sm text-gray-400 font-medium">since last month</span>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="lg:col-span-2 bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Filter by Role</label>
                        <select wire:model.live="roleFilter" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ ucwords(str_replace('_', ' ', $role->name)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Account Status</label>
                        <select wire:model.live="statusFilter" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20">
                            <option value="">All Statuses</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="relative flex-1 max-w-md">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4">
                            <x-icon name="search" class="w-4 h-4 text-gray-400" />
                        </span>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by name, ID or email..." class="w-full pl-10 pr-4 py-3 bg-gray-50 border-none rounded-xl text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <button wire:click="resetFilters" class="ml-4 px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session()->has('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3 text-emerald-600">
                <x-icon name="check" class="w-5 h-5" />
                <p class="text-sm font-bold">{{ session('success') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-center gap-3 text-red-600">
                <x-icon name="activity" class="w-5 h-5" />
                <p class="text-sm font-bold">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Staff Table -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left border-b border-gray-50">
                            <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">Name</th>
                            <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">Role</th>
                            <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">Last Login</th>
                            <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($staffMembers as $staff)
                            <tr wire:key="staff-{{ $staff->id }}" class="group hover:bg-gray-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center overflow-hidden">
                                            @if($staff->profile_photo_path)
                                                <img src="{{ asset('storage/' . $staff->profile_photo_path) }}" alt="{{ $staff->name }}" class="w-full h-full object-cover">
                                            @else
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($staff->name) }}&background=EEF2FF&color=4F46E5" alt="{{ $staff->name }}" class="w-full h-full object-cover">
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">{{ $staff->name }}</p>
                                            <p class="text-xs text-gray-400 font-medium">{{ $staff->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    @php
                                        $role = $staff->roles->first()?->name;
                                        $roleClasses = [
                                            'clinic_admin' => 'bg-red-50 text-red-600',
                                            'doctor' => 'bg-purple-50 text-purple-600',
                                            'receptionist' => 'bg-blue-50 text-blue-600',
                                            'nurse' => 'bg-emerald-50 text-emerald-600',
                                        ];
                                        $class = $roleClasses[$role] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $class }}">
                                        {{ str_replace('_', ' ', $role) }}
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <button wire:click="toggleStatus({{ $staff->id }})" wire:loading.attr="disabled" class="relative inline-flex items-center group">
                                        <div class="w-10 h-5 bg-gray-200 rounded-full transition-colors {{ $staff->is_active ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
                                        <div class="absolute left-1 w-3 h-3 bg-white rounded-full transition-transform {{ $staff->is_active ? 'translate-x-5' : 'translate-x-0' }}"></div>
                                        <span class="ml-3 text-xs font-bold {{ $staff->is_active ? 'text-indigo-600' : 'text-gray-400' }}">
                                            {{ $staff->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </button>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="text-sm font-semibold text-gray-600">
                                        {{ $staff->last_login_at ? \Carbon\Carbon::parse($staff->last_login_at)->diffForHumans() : 'Never' }}
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.staff.detail', $staff->id) }}" wire:navigate class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="View Details">
                                            <x-icon name="eye" class="w-5 h-5" />
                                        </a>
                                        <a href="{{ route('admin.staff.edit', $staff->id) }}" wire:navigate class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all" title="Edit Staff">
                                            <x-icon name="pencil" class="w-5 h-5" />
                                        </a>
                                        <button 
                                            wire:click="deleteStaff({{ $staff->id }})" 
                                            wire:confirm="Are you sure you want to remove this staff member?"
                                            wire:loading.attr="disabled"
                                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" 
                                            title="Delete Staff"
                                        >
                                            <x-icon name="trash" class="w-5 h-5" wire:loading.remove wire:target="deleteStaff({{ $staff->id }})" />
                                            <div wire:loading wire:target="deleteStaff({{ $staff->id }})" class="w-5 h-5 border-2 border-red-600 border-t-transparent rounded-full animate-spin"></div>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                            <x-icon name="users" class="w-8 h-8 text-gray-300" />
                                        </div>
                                        <p class="text-gray-500 font-bold">No staff members found</p>
                                        <p class="text-sm text-gray-400 mt-1">Try adjusting your filters or search query</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($staffMembers->hasPages())
                <div class="px-8 py-6 border-t border-gray-50">
                    {{ $staffMembers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
