@php
    $currentRoute = request()->path();
    $active = function(string $path) use ($currentRoute): string {
        return str_starts_with($currentRoute, ltrim($path, '/'))
            ? 'bg-blue-600 text-white'
            : 'text-slate-300 hover:bg-slate-800 hover:text-white';
    };
    $isExact = function(string $path) use ($currentRoute): string {
        return ($currentRoute === ltrim($path, '/') || ($path === '/' && $currentRoute === ''))
            ? 'bg-blue-600 text-white'
            : 'text-slate-300 hover:bg-slate-800 hover:text-white';
    };
@endphp

<aside class="sidebar glass min-h-screen fixed left-0 top-0 flex flex-col z-50" style="width:256px">

    {{-- Logo --}}
    <div class="p-6 border-b border-slate-700/50">
        <a href="/" class="block">
            <h2 class="text-2xl font-black tracking-tight">🌎 GSCR</h2>
            <p class="text-slate-400 text-xs mt-1 leading-tight">Global Supply Chain<br>Risk Intelligence</p>
        </a>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto scrollbar">

        <div class="text-xs text-slate-500 uppercase tracking-widest px-3 mb-2">Overview</div>

        <a href="{{ url('/') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm {{ $isExact('/') }}">
            <i class="bi bi-speedometer2 w-5 text-center"></i>
            Dashboard
        </a>

        <div class="text-xs text-slate-500 uppercase tracking-widest px-3 mt-4 mb-2">Intelligence</div>

        <a href="{{ route('countries.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm {{ $active('countries') }}">
            <i class="bi bi-globe2 w-5 text-center"></i>
            Countries
        </a>

        <a href="{{ route('risk.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm {{ $active('risk') }}">
            <i class="bi bi-exclamation-triangle-fill w-5 text-center"></i>
            Risk Scoring
        </a>

        <a href="{{ route('weather.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm {{ $active('weather') }}">
            <i class="bi bi-cloud-sun-fill w-5 text-center"></i>
            Weather
        </a>

        <a href="{{ route('news.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm {{ $active('news') }}">
            <i class="bi bi-newspaper w-5 text-center"></i>
            News Intelligence
        </a>

        <a href="{{ route('currency.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm {{ $active('currency') }}">
            <i class="bi bi-currency-exchange w-5 text-center"></i>
            Currency
        </a>

        <a href="{{ route('ports.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm {{ $active('ports') }}">
            <i class="bi bi-geo-alt-fill w-5 text-center"></i>
            Ports
        </a>

        <div class="text-xs text-slate-500 uppercase tracking-widest px-3 mt-4 mb-2">Tools</div>

        <a href="{{ route('compare.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm {{ $active('compare') }}">
            <i class="bi bi-bar-chart-steps w-5 text-center"></i>
            Compare Countries
        </a>

        <a href="{{ route('watchlist.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm {{ $active('watchlist') }}">
            <i class="bi bi-star-fill w-5 text-center"></i>
            Watchlist
        </a>

        <div class="text-xs text-slate-500 uppercase tracking-widest px-3 mt-4 mb-2">Admin</div>

        <a href="{{ route('admin.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm {{ $active('admin') }}">
            <i class="bi bi-shield-lock-fill w-5 text-center"></i>
            Admin Panel
        </a>

        <a href="{{ route('admin.watchlist') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm {{ $active('admin/watchlist') }}">
            <i class="bi bi-eye-fill w-5 text-center"></i>
            Watchlist Monitor
        </a>

    </nav>

    {{-- Footer --}}
    <div class="p-4 border-t border-slate-700/50">
        <form method="POST" action="{{ route('sync.data') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/40 text-emerald-400 transition text-sm font-semibold border border-emerald-500/20">
                <i class="bi bi-arrow-repeat"></i> Sync Data
            </button>
        </form>
        <p class="text-center text-xs text-slate-600 mt-2">v1.0 · GSCR Platform</p>
    </div>

</aside>
