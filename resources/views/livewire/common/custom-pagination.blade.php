<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center bg-[#5200cc] rounded-xl overflow-hidden shadow-xl border border-[#5200cc]/20">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-4 py-3 text-white/30 border-r border-white/10 cursor-default flex items-center justify-center">
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                </span>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled" rel="prev" class="px-4 py-3 text-white border-r border-white/10 hover:bg-white/10 transition-all flex items-center justify-center group">
                    <span class="material-symbols-outlined text-sm group-hover:scale-110 transition-transform">chevron_left</span>
                </button>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="px-4 py-3 text-white/50 border-r border-white/10 cursor-default flex items-center justify-center font-bold">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-5 py-3 bg-white text-[#5200cc] font-black border-r border-white/10 last:border-r-0 flex items-center justify-center min-w-[50px] shadow-inner">
                                {{ $page }}
                            </span>
                        @else
                            <button wire:click="gotoPage({{ $page }})" class="px-5 py-3 text-white font-bold border-r border-white/10 last:border-r-0 hover:bg-white/10 transition-all flex items-center justify-center min-w-[50px]">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled" rel="next" class="px-4 py-3 text-white hover:bg-white/10 transition-all flex items-center justify-center group">
                    <span class="material-symbols-outlined text-sm group-hover:scale-110 transition-transform">chevron_right</span>
                </button>
            @else
                <span class="px-4 py-3 text-white/30 cursor-default flex items-center justify-center">
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </span>
            @endif
        </nav>
    @endif
</div>
