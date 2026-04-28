<header class="flex items-center justify-between px-8 py-4 bg-surface border-b border-outline-variant">
    <div>
        @if($title)
            @php
                // Remove the suffix | ClinicOS if it exists for cleaner header display
                $displayTitle = str_replace(' | ClinicOS', '', $title);
            @endphp
            <h1 class="text-xl font-black text-on-background tracking-tight">{{ $displayTitle }}</h1>
        @endif
    </div>
    <button wire:click="logout" class="flex items-center gap-2 px-3 py-2 text-sm font-bold text-error bg-error-container hover:bg-error-container/80 rounded-xl transition-all duration-300">
        <span class="material-symbols-outlined text-lg">logout</span>
        <span>Logout</span>
    </button>
</header>
