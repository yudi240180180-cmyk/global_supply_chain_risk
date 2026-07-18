<nav class="sticky top-0 z-40 glass border-b border-slate-700/50 h-16 flex items-center justify-between px-8">

    {{-- Page breadcrumb --}}
    <div class="flex items-center gap-3">
        @php
            $segments = request()->segments();
            $pageTitle = match($segments[0] ?? '') {
                ''          => 'Executive Dashboard',
                'countries' => isset($segments[1]) ? 'Country Detail' : 'Countries',
                'risk'      => 'Risk Scoring Engine',
                'weather'   => 'Weather Monitoring',
                'news'      => 'News Intelligence',
                'currency'  => 'Currency Dashboard',
                'ports'     => isset($segments[1]) ? 'Port Detail' : 'Port Dashboard',
                'compare'   => 'Country Comparison',
                'watchlist' => 'Watchlist',
                'admin'     => 'Admin Panel',
                default     => ucfirst($segments[0] ?? 'Dashboard'),
            };
        @endphp
        <span class="text-slate-400 text-sm">Global Supply Chain Risk</span>
        <span class="text-slate-600">/</span>
        <span class="font-semibold text-sm">{{ $pageTitle }}</span>
    </div>

    <div class="flex items-center gap-4">

        {{-- Sync Status Flash --}}
        @if(session('sync_status'))
        <div class="text-sm px-4 py-1.5 rounded-xl glass border {{ str_contains(session('sync_status'),'fail')?'border-red-500/30 text-red-400':'border-emerald-500/30 text-emerald-400' }}">
            {{ session('sync_status') }}
        </div>
        @endif

        {{-- Quick Links --}}
        <a href="{{ route('risk.index') }}" title="Risk Scoring"
           class="w-9 h-9 rounded-xl glass flex items-center justify-center hover:bg-slate-700 transition text-slate-300 hover:text-white">
            <i class="bi bi-exclamation-triangle-fill text-sm"></i>
        </a>
        <a href="{{ route('watchlist.index') }}" title="Watchlist"
           class="w-9 h-9 rounded-xl glass flex items-center justify-center hover:bg-slate-700 transition text-slate-300 hover:text-white">
            <i class="bi bi-star-fill text-sm"></i>
        </a>
        <a href="{{ route('compare.index') }}" title="Compare"
           class="w-9 h-9 rounded-xl glass flex items-center justify-center hover:bg-slate-700 transition text-slate-300 hover:text-white">
            <i class="bi bi-bar-chart-steps text-sm"></i>
        </a>

        {{-- Admin --}}
        <div class="flex items-center gap-2 pl-2 border-l border-slate-700">
            <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center font-bold text-sm">A</div>
            <div class="hidden md:block">
                <div class="text-sm font-semibold leading-tight">Admin</div>
                <div class="text-xs text-slate-400">GSCR Platform</div>
            </div>
        </div>

    </div>

</nav>
