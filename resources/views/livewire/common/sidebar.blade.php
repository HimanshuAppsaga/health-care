<div class="relative flex">
    <!-- Sidebar -->
    <aside 
        class="flex flex-col h-screen transition-all duration-300 ease-in-out bg-white border-r dark:bg-gray-900 dark:border-gray-800 {{ $isCollapsed ? 'w-20' : 'w-64' }}"
    >
        <!-- Logo Section -->
        <div class="flex items-center h-20 px-6 border-b dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 rounded-xl bg-gradient-to-tr from-primary-600 to-primary-400 shadow-lg shadow-primary-500/20">
                    <x-icon name="layout-dashboard" class="w-6 h-6 text-white" />
                </div>
                @if(!$isCollapsed)
                    <span class="text-xl font-bold tracking-tight text-gray-800 dark:text-white transition-opacity duration-300">
                        Clinic<span class="text-primary-600">Sync</span>
                    </span>
                @endif
            </div>
        </div>

        <!-- Toggle Button (Desktop) -->
        <button 
            wire:click="toggleSidebar"
            class="absolute top-7 -right-3 flex items-center justify-center w-6 h-6 bg-white border border-gray-200 rounded-full shadow-sm dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors z-50"
        >
            <x-icon name="{{ $isCollapsed ? 'chevron-right' : 'chevron-left' }}" class="w-4 h-4 text-gray-500" />
        </button>

        <!-- Navigation Items -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden py-6 px-4 space-y-8 custom-scrollbar">
            @foreach($menuItems as $section => $items)
                <div class="space-y-2">
                    @if(!$isCollapsed)
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            {{ $section }}
                        </h3>
                    @else
                        <div class="h-px bg-gray-100 dark:bg-gray-800 mx-2"></div>
                    @endif

                    <nav class="space-y-1">
                        @foreach($items as $item)
                            @php
                                $isActive = request()->routeIs($item['route']);
                                $hasBadge = isset($item['badge']) && isset($$item['badge']) && $$item['badge'] > 0;
                            @endphp
                            <a 
                                href="#" 
                                class="flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-xl group relative
                                    {{ $isActive 
                                        ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400' 
                                        : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50 dark:hover:text-gray-200' }}"
                                title="{{ $isCollapsed ? $item['name'] : '' }}"
                            >
                                <x-icon 
                                    name="{{ $item['icon'] }}" 
                                    class="flex-shrink-0 transition-transform group-hover:scale-110 {{ $isActive ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}" 
                                />
                                
                                @if(!$isCollapsed)
                                    <span class="flex-1 truncate">{{ $item['name'] }}</span>
                                    
                                    @if($hasBadge)
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                                            {{ $$item['badge'] }}
                                        </span>
                                    @endif
                                @else
                                    @if($hasBadge)
                                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-gray-900"></span>
                                    @endif
                                @endif

                                @if($isActive && !$isCollapsed)
                                    <div class="absolute left-0 w-1 h-6 bg-primary-600 rounded-r-full"></div>
                                @endif
                            </a>
                        @endforeach
                    </nav>
                </div>
            @endforeach
        </div>

        <!-- User Profile Section -->
        <div class="p-4 border-t dark:border-gray-800">
            <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors cursor-pointer group">
                <div class="relative flex-shrink-0">
                    <img 
                        class="w-10 h-10 rounded-lg object-cover" 
                        src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()?->name ?? 'User') }}&background=0D8ABC&color=fff" 
                        alt="Avatar"
                    >
                    <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-900 rounded-full"></div>
                </div>
                
                @if(!$isCollapsed)
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">
                            {{ auth()->user()?->name ?? 'John Doe' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ ucfirst(auth()->user()?->roles()->first()?->name ?? 'Guest') }}
                        </p>
                    </div>
                    <button class="text-gray-400 hover:text-red-500 transition-colors">
                        <x-icon name="logout" class="w-5 h-5" />
                    </button>
                @endif
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
            background: #e5e7eb;
            border-radius: 10px;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #374151;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }

        :root {
            --primary-50: #eff6ff;
            --primary-100: #dbeafe;
            --primary-400: #60a5fa;
            --primary-500: #3b82f6;
            --primary-600: #2563eb;
            --primary-900: #1e3a8a;
        }

        .bg-primary-50 { background-color: var(--primary-50); }
        .bg-primary-600 { background-color: var(--primary-600); }
        .text-primary-600 { color: var(--primary-600); }
        .text-primary-400 { color: var(--primary-400); }
        .dark .bg-primary-900\/20 { background-color: rgba(30, 58, 138, 0.2); }
    </style>
</div>
