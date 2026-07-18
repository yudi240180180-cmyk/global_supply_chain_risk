@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
    <div class="flex justify-between flex-1 sm:hidden">
        @if ($paginator->onFirstPage())
            <span class="px-4 py-2 rounded-xl bg-slate-800 text-slate-500 cursor-not-allowed text-sm">← Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white transition text-sm">← Previous</a>
        @endif
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white transition text-sm">Next →</a>
        @else
            <span class="px-4 py-2 rounded-xl bg-slate-800 text-slate-500 cursor-not-allowed text-sm">Next →</span>
        @endif
    </div>

    <div class="hidden sm:flex sm:flex-col sm:items-center w-full gap-4">
        <div class="text-sm text-slate-400">
            Showing <span class="font-semibold text-white">{{ $paginator->firstItem() }}</span>
            to <span class="font-semibold text-white">{{ $paginator->lastItem() }}</span>
            of <span class="font-semibold text-white">{{ $paginator->total() }}</span> results
        </div>
        <div class="flex gap-1">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-2 rounded-xl bg-slate-800 text-slate-500 cursor-not-allowed text-sm">‹</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white transition text-sm">‹</a>
            @endif

            {{-- Pages --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-3 py-2 rounded-xl bg-slate-800 text-slate-500 text-sm">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-3 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white transition text-sm">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white transition text-sm">›</a>
            @else
                <span class="px-3 py-2 rounded-xl bg-slate-800 text-slate-500 cursor-not-allowed text-sm">›</span>
            @endif
        </div>
    </div>
</nav>
@endif
