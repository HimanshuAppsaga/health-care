<header class="flex items-center justify-between px-4 sm:px-8 py-4 bg-surface border-b border-outline-variant min-w-0">
    <div class="flex items-center gap-3 min-w-0">
        <!-- Hamburger Button -->
        <button @click="mobileSidebarOpen = true" class="lg:hidden p-2 -ml-2 text-outline hover:text-on-surface hover:bg-surface-container rounded-xl transition-all shrink-0">
            <span class="material-symbols-outlined text-2xl">menu</span>
        </button>
        @if($title)
            @php
                // Remove the suffix | Clinic Saga if it exists for cleaner header display
                $displayTitle = str_replace(' | Clinic Saga', '', $title);
            @endphp
            <h1 class="text-lg sm:text-xl font-black text-on-background tracking-tight truncate">{{ $displayTitle }}</h1>
        @endif
    </div>
    <button wire:click="logout" class="flex items-center gap-2 px-3 py-2 text-xs sm:text-sm font-bold text-error bg-error-container hover:bg-error-container/80 rounded-xl transition-all duration-300 shrink-0">
        <span class="material-symbols-outlined text-base sm:text-lg">logout</span>
        <span class="hidden sm:inline">Logout</span>
    </button>
</header>
