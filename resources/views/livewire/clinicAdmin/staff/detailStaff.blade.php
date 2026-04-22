<div class="p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Breadcrumbs and Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <nav class="flex text-sm font-medium text-gray-400 mb-2">
                    <a href="{{ route('admin.staff.index') }}" wire:navigate class="hover:text-indigo-600 transition-colors">Staff Management</a>
                    <span class="mx-2">/</span>
                    <span class="text-gray-900">Staff Profile</span>
                </nav>
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <div class="w-24 h-24 rounded-3xl bg-indigo-50 flex items-center justify-center overflow-hidden border-4 border-white shadow-xl shadow-indigo-100">
                            @if($staff->profile_photo_path)
                                <img src="{{ asset('storage/' . $staff->profile_photo_path) }}" alt="{{ $staff->name }}" class="w-full h-full object-cover">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($staff->name) }}&background=EEF2FF&color=4F46E5&size=128" alt="{{ $staff->name }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="absolute -bottom-2 -right-2 px-3 py-1 {{ $staff->is_active ? 'bg-green-500' : 'bg-gray-500' }} text-white text-[10px] font-black uppercase tracking-widest rounded-full border-2 border-white shadow-sm">
                            {{ $staff->is_active ? 'Active' : 'Inactive' }}
                        </div>
                    </div>
                    <div>
                        <h1 class="text-4xl font-black text-gray-900">{{ $staff->name }}</h1>
                        <div class="flex items-center gap-4 mt-1">
                            <div class="flex items-center gap-2 text-gray-500 font-bold text-sm">
                                <x-icon name="briefcase" class="w-4 h-4" />
                                {{ ucwords(str_replace('_', ' ', $staff->roles->first()?->name)) }}
                            </div>
                            <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                            <div class="flex items-center gap-2 text-gray-500 font-bold text-sm">
                                <x-icon name="hash" class="w-4 h-4" />
                                Employee ID: {{ $staff->employee_id }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.staff.edit', $staff->id) }}" wire:navigate class="px-6 py-3 bg-white border border-gray-100 text-gray-600 font-bold rounded-2xl hover:bg-gray-50 transition-all shadow-sm flex items-center gap-2">
                    <x-icon name="pencil" class="w-4 h-4" />
                    Edit Profile
                </a>
                <button class="px-6 py-3 bg-[#4F46E5] text-white font-bold rounded-2xl hover:bg-[#4338CA] transition-all shadow-lg shadow-indigo-100 flex items-center gap-2">
                    <x-icon name="clock" class="w-4 h-4" />
                    Manage Shifts
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Personal Info and Security -->
            <div class="space-y-8">
                <!-- Personal Details -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest flex items-center gap-2">
                            <x-icon name="user" class="w-4 h-4 text-indigo-600" />
                            Personal Details
                        </h3>
                        <button class="text-gray-400 hover:text-indigo-600 transition-colors">
                            <x-icon name="external-link" class="w-4 h-4" />
                        </button>
                    </div>
                    <div class="space-y-6">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Email Address</p>
                            <p class="text-sm font-bold text-gray-800">{{ $staff->email }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Phone Number</p>
                            <p class="text-sm font-bold text-gray-800">{{ $staff->phone ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Emergency Contact</p>
                            <p class="text-sm font-bold text-gray-800">{{ $staff->emergency_contact_name ?? 'Not provided' }}</p>
                            <p class="text-xs text-gray-400 font-medium">{{ $staff->emergency_contact_phone ?? '' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Home Address</p>
                            <p class="text-sm font-bold text-gray-800">{!! nl2br(e($staff->address ?? 'Not provided')) !!}</p>
                        </div>
                    </div>
                </div>

                <!-- Permissions -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest flex items-center gap-2 mb-8">
                        <x-icon name="shield-check" class="w-4 h-4 text-indigo-600" />
                        Permissions & Security
                    </h3>
                    <div class="space-y-4">
                        @forelse($permissions as $slug => $name)
                            <div class="flex items-center justify-between p-3 rounded-2xl bg-gray-50/50 border border-transparent hover:border-gray-100 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center bg-emerald-100 text-emerald-600">
                                        <x-icon name="check" class="w-3 h-3" />
                                    </div>
                                    <span class="text-xs font-bold text-gray-700">{{ $name }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[8px] font-black tracking-widest bg-emerald-50 text-emerald-600">
                                    GRANTED
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 font-medium text-center">No specific permissions assigned.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Middle Column: Department and Activity -->
            <div class="space-y-8">
                <!-- Department Card -->
                <div class="bg-[#310E93] p-8 rounded-3xl shadow-xl text-white relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <x-icon name="building" class="w-5 h-5 text-indigo-300" />
                            <h3 class="text-xs font-bold text-indigo-200 uppercase tracking-widest">Assigned Department</h3>
                        </div>
                        <h2 class="text-3xl font-black mb-1">{{ $staff->department ?? 'General Practice' }}</h2>
                        <p class="text-indigo-200 text-sm font-medium">{{ $staff->unit ?? 'General Unit' }}</p>
                        
                        <div class="mt-8 pt-8 border-t border-white/10 flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                                <x-icon name="user" class="w-5 h-5 text-indigo-200" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-indigo-300 uppercase tracking-widest">Supervisor</p>
                                <p class="text-sm font-bold">{{ $staff->supervisor_name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
                </div>

                <!-- Activity Log -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest flex items-center gap-2">
                            <x-icon name="history" class="w-4 h-4 text-indigo-600" />
                            Recent Activity Log
                        </h3>
                        <button class="text-xs font-bold text-indigo-600 hover:underline">View All Logs</button>
                    </div>
                    <div class="space-y-8">
                        @foreach($activities as $activity)
                            <div class="flex gap-4">
                                <div class="relative">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center relative z-10 text-indigo-600">
                                        <x-icon name="{{ $activity['icon'] }}" class="w-5 h-5" />
                                    </div>
                                    @if(!$loop->last)
                                        <div class="absolute top-10 left-1/2 -translate-x-1/2 w-px h-8 bg-gray-100"></div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="text-sm font-bold text-gray-900">{{ $activity['title'] }}</h4>
                                        <span class="text-[10px] font-bold text-gray-400">{{ $activity['time'] }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 font-medium mb-1">{{ $activity['description'] }}</p>
                                    <span class="text-[8px] font-black tracking-widest text-emerald-500">{{ $activity['status'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column: Performance and Notes -->
            <div class="space-y-8">
                <!-- Performance Overview -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest flex items-center gap-2 mb-8">
                        <x-icon name="trending-up" class="w-4 h-4 text-indigo-600" />
                        Performance Overview
                    </h3>
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="bg-gray-50 p-4 rounded-2xl">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Appointments (All Time)</p>
                            <h4 class="text-2xl font-black text-gray-900">{{ $appointmentCount }}</h4>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Rating</p>
                            <div class="flex items-center gap-1">
                                <h4 class="text-2xl font-black text-emerald-500">{{ number_format($staff->rating ?? 5.0, 1) }}</h4>
                                <span class="text-xs font-bold text-gray-400">/5</span>
                            </div>
                        </div>
                    </div>
                    <!-- Small Sparkline Bar Chart placeholder -->
                    <div class="flex items-end justify-between gap-1 h-20">
                        <div class="flex-1 bg-indigo-100 rounded-sm" style="height: 40%"></div>
                        <div class="flex-1 bg-indigo-200 rounded-sm" style="height: 60%"></div>
                        <div class="flex-1 bg-indigo-100 rounded-sm" style="height: 30%"></div>
                        <div class="flex-1 bg-indigo-300 rounded-sm" style="height: 80%"></div>
                        <div class="flex-1 bg-indigo-200 rounded-sm" style="height: 50%"></div>
                        <div class="flex-1 bg-indigo-400 rounded-sm" style="height: 90%"></div>
                        <div class="flex-1 bg-[#310E93] rounded-sm" style="height: 100%"></div>
                    </div>
                    <p class="text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-4">Weekly Activity Consistency</p>
                </div>

                <!-- Admin Notes -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest flex items-center gap-2 mb-8">
                        <x-icon name="sticky-note" class="w-4 h-4 text-indigo-600" />
                        Internal Administrative Notes
                    </h3>
                    <div class="relative">
                        <x-icon name="quote" class="absolute -top-4 -left-4 w-8 h-8 text-indigo-50 opacity-10" />
                        <p class="text-sm font-medium text-gray-600 italic leading-relaxed">
                            "{{ $staff->bio ?? 'No administrative notes available for this staff member.' }}"
                        </p>
                        <div class="mt-6 flex items-center justify-between">
                            <p class="text-xs font-bold text-gray-400">— HR Admin, Sep 2023</p>
                            <button class="text-xs font-bold text-indigo-600 hover:underline">Edit Notes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
