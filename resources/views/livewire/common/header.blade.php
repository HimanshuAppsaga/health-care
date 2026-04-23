<header class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-100">
    <div class="flex items-center">
        <button class="text-gray-500 focus:outline-none lg:hidden">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        <div class="relative mx-4 lg:mx-0">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                    <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </span>

            <input class="w-32 pl-10 pr-4 text-gray-700 bg-gray-50 border-none rounded-xl sm:w-64 focus:ring-2 focus:ring-indigo-500/20" type="text" placeholder="Search patients, doctors, or records...">
        </div>
    </div>

    <div class="flex items-center gap-4">
        <button class="flex p-2 text-gray-400 hover:text-indigo-600 transition-colors duration-300" aria-label="show notifications">
            <x-icon name="bell" class="w-6 h-6" />
        </button>
        
        <button class="flex p-2 text-gray-400 hover:text-indigo-600 transition-colors duration-300" aria-label="settings">
            <x-icon name="settings" class="w-6 h-6" />
        </button>

        <div class="relative ml-2">
            <button class="flex items-center gap-2 focus:outline-none" aria-label="toggle profile dropdown">
                <div class="w-9 h-9 overflow-hidden border-2 border-gray-100 rounded-xl shadow-sm">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()?->name ?? 'User') }}&background=4F46E5&color=fff" class="object-cover w-full h-full" alt="avatar">
                </div>
            </button>
        </div>

        <button wire:click="logout" class="flex items-center gap-2 px-3 py-2 text-sm font-bold text-red-500 bg-red-50 hover:bg-red-100 rounded-xl transition-all duration-300">
            <span class="material-symbols-outlined text-lg">logout</span>
            <span>Logout</span>
        </button>
    </div>
</header>
