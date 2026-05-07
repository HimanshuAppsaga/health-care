<div class="relative flex">
    <!-- Sidebar -->
    <aside 
        class="flex flex-col h-screen transition-all duration-300 ease-in-out bg-surface border-r border-outline-variant {{ $isCollapsed ? 'w-20' : 'w-72' }}"
    >
        <!-- Logo Section -->
        <div class="flex flex-col justify-center h-28 px-8">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    <span class="text-2xl font-bold tracking-tight text-primary">ClinicOS</span>
                </div>
            </div>
            @if(!$isCollapsed)
                <span class="text-xs font-medium text-outline mt-0.5">Admin Console</span>
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
                                    href="{{ Route::has($item['route']) ? route($item['route'], $item['params'] ?? []) : '#' }}" 
                                    class="flex items-center gap-4 px-8 py-3.5 text-sm font-semibold transition-all duration-200 group
                                        {{ $isActive 
                                            ? 'bg-primary-container text-on-primary-container' 
                                            : 'text-outline hover:text-on-surface' }}"
                                    title="{{ $isCollapsed ? $item['name'] : '' }}"
                                >
                                    <x-icon 
                                        name="{{ $item['icon'] }}" 
                                        class="flex-shrink-0 w-5 h-5 {{ $isActive ? 'text-on-primary-container' : 'text-outline-variant group-hover:text-outline' }}" 
                                    />
                                    
                                    @if(!$isCollapsed)
                                        <span class="flex-1 truncate">{{ $item['name'] }}</span>
                                    @endif

                                    @if($hasBadge)
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold leading-none text-on-error bg-error rounded-full">
                                            {{ $badgeCount }}
                                        </span>
                                    @endif
                                </a>
                                @if($isActive)
                                    <div class="absolute right-0 top-0 h-full w-1 bg-primary"></div>
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
            @if(auth()->user()->hasRole('doctor'))
                <div class="px-4 mb-4">
                    @php $isSettingsActive = request()->routeIs('doctor.clinic-settings'); @endphp
                    <a href="{{ route('doctor.clinic-settings') }}" wire:navigate 
                        class="flex items-center gap-4 px-4 py-3 text-sm font-semibold transition-all duration-200 group rounded-xl
                            {{ $isSettingsActive 
                                ? 'bg-primary-container text-on-primary-container' 
                                : 'text-outline hover:text-on-surface hover:bg-surface-container' }}"
                    >
                        <x-icon name="settings" class="w-5 h-5 {{ $isSettingsActive ? 'text-on-primary-container' : 'text-outline-variant group-hover:text-outline' }}" />
                        @if(!$isCollapsed)
                            <span>Clinic Settings</span>
                        @endif
                    </a>
                </div>
            @endif

            <!-- User Profile Section -->
            <div class="px-4 border-t border-outline-variant pt-6">
                <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-surface-container transition-colors cursor-pointer group">
                    <div class="relative flex-shrink-0">
                        <img 
                            class="w-10 h-10 rounded-lg object-cover" 
                            src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()?->name ?? 'User') }}&background=005bb0&color=fff" 
                            alt="Avatar"
                        >
                    </div>
                    
                    @if(!$isCollapsed)
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-on-surface truncate">
                                {{ auth()->user()?->name ?? 'Dr. Sarah Smith' }}
                            </p>
                            <p class="text-[10px] font-semibold text-outline truncate">
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
