<div class="relative flex">
    <!-- Sidebar -->
    <aside 
        class="flex flex-col h-screen transition-all duration-300 ease-in-out bg-white border-r border-gray-100 {{ $isCollapsed ? 'w-20' : 'w-72' }}"
    >
        <!-- Logo Section -->
        <div class="flex flex-col justify-center h-28 px-8">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    <span class="text-2xl font-bold tracking-tight text-[#310E93]">ClinicOS</span>
                </div>
            </div>
            @if(!$isCollapsed)
                <span class="text-xs font-medium text-gray-400 mt-0.5">Admin Console</span>
            @endif
        </div>

        <!-- Navigation Items -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden py-4 custom-scrollbar">
            @foreach($menuItems as $section => $items)
                <div class="mb-4">
                    <nav class="space-y-1">
                        @foreach($items as $item)
                            @php
                                $isActive = request()->routeIs($item['route']);
                                $badgeName = $item['badge'] ?? null;
                                $badgeCount = $badgeName ? ($this->$badgeName ?? 0) : 0;
                                $hasBadge = $badgeName && $badgeCount > 0;
                            @endphp
                            <div class="relative">
                                <a 
                                    href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}" 
                                    class="flex items-center gap-4 px-8 py-3.5 text-sm font-semibold transition-all duration-200 group
                                        {{ $isActive 
                                            ? 'bg-[#F0EEFF] text-[#4F46E5]' 
                                            : 'text-gray-500 hover:text-gray-900' }}"
                                    title="{{ $isCollapsed ? $item['name'] : '' }}"
                                >
                                    <x-icon 
                                        name="{{ $item['icon'] }}" 
                                        class="flex-shrink-0 w-5 h-5 {{ $isActive ? 'text-[#4F46E5]' : 'text-gray-400 group-hover:text-gray-600' }}" 
                                    />
                                    
                                    @if(!$isCollapsed)
                                        <span class="flex-1 truncate">{{ $item['name'] }}</span>
                                    @endif

                                    @if($hasBadge)
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold leading-none text-white bg-red-500 rounded-full">
                                            {{ $badgeCount }}
                                        </span>
                                    @endif
                                </a>
                                @if($isActive)
                                    <div class="absolute right-0 top-0 h-full w-1 bg-[#4F46E5]"></div>
                                @endif
                            </div>
                        @endforeach
                    </nav>
                </div>
            @endforeach
        </div>

        <!-- Bottom Actions -->
        <div class="mt-auto py-6">
            <!-- Clinic Settings -->
            <div class="px-4 mb-4">
                <a href="#" class="flex items-center gap-4 px-4 py-3 text-sm font-semibold text-gray-500 hover:text-gray-900 transition-colors group">
                    <x-icon name="settings" class="w-5 h-5 text-gray-400 group-hover:text-gray-600" />
                    @if(!$isCollapsed)
                        <span>Clinic Settings</span>
                    @endif
                </a>
            </div>

            <!-- User Profile Section -->
            <div class="px-4 border-t border-gray-50 pt-6">
                <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition-colors cursor-pointer group">
                    <div class="relative flex-shrink-0">
                        <img 
                            class="w-10 h-10 rounded-lg object-cover" 
                            src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()?->name ?? 'User') }}&background=4F46E5&color=fff" 
                            alt="Avatar"
                        >
                    </div>
                    
                    @if(!$isCollapsed)
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-800 truncate">
                                {{ auth()->user()?->name ?? 'Dr. Sarah Smith' }}
                            </p>
                            <p class="text-[10px] font-semibold text-gray-400 truncate">
                                {{ ucfirst(auth()->user()?->roles()->first()?->name ?? 'Chief Administrator') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </aside>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #f1f5f9;
            border-radius: 10px;
        }
    </style>
</div>
