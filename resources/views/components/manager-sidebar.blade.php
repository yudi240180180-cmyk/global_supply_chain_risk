@php
    $currentRoute = request()->path();
    $active = function(string $path) use ($currentRoute): string {
        return str_starts_with($currentRoute, ltrim($path, '/'))
            ? 'active'
            : '';
    };
    $isExact = function(string $path) use ($currentRoute): string {
        return ($currentRoute === ltrim($path, '/') || ($path === 'manager' && $currentRoute === 'manager'))
            ? 'active'
            : '';
    };
@endphp

<aside class="manager-sidebar">
    {{-- Logo --}}
    <div class="p-6 border-b border-slate-800">
        <a href="/manager" class="block">
            <h2 class="text-xl font-black tracking-tight">🚢 GSCR portal</h2>
            <p class="text-slate-500 text-xs mt-1 leading-tight">Sistem Pendukung Keputusan</p>
        </a>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto scrollbar">
        <div class="text-[10px] text-slate-500 uppercase tracking-widest px-3 mb-2 font-bold">Dasbor</div>

        <a href="{{ url('/manager') }}"
           class="nav-item {{ $currentRoute === 'manager' ? 'active' : '' }}">
            <div class="icon"><i class="bi bi-speedometer2"></i></div>
            Dasbor
        </a>

        <div class="text-[10px] text-slate-500 uppercase tracking-widest px-3 mt-4 mb-2 font-bold">Logistics & Route</div>

        <a href="{{ route('manager.shipments.index') }}"
           class="nav-item {{ $active('manager/shipments') }}">
            <div class="icon"><i class="bi bi-calendar-event"></i></div>
            Perencana Pengiriman
        </a>

        <a href="{{ route('manager.routes.index') }}"
           class="nav-item {{ $active('manager/routes') }}">
            <div class="icon"><i class="bi bi-map"></i></div>
            Rekomendasi Rute
        </a>

        <a href="{{ route('manager.cost-estimator.index') }}"
           class="nav-item {{ $active('manager/cost-estimator') }}">
            <div class="icon"><i class="bi bi-calculator"></i></div>
            Estimator Biaya
        </a>

        <div class="text-[10px] text-slate-500 uppercase tracking-widest px-3 mt-4 mb-2 font-bold">Supply Chain</div>

        <a href="{{ route('manager.purchase-orders.index') }}"
           class="nav-item {{ $active('manager/purchase-orders') }}">
            <div class="icon"><i class="bi bi-file-earmark-text"></i></div>
            Pesanan Pembelian
        </a>

        <a href="{{ route('manager.suppliers.index') }}"
           class="nav-item {{ $active('manager/suppliers') }}">
            <div class="icon"><i class="bi bi-people"></i></div>
            Manajemen Pemasok
        </a>

        <div class="text-[10px] text-slate-500 uppercase tracking-widest px-3 mt-4 mb-2 font-bold">Risk & Analytics</div>

        <a href="{{ url('/manager/watchlist') }}"
           class="nav-item {{ $active('manager/watchlist') }}">
            <div class="icon"><i class="bi bi-star"></i></div>
            Daftar Pantau
        </a>

        <a href="{{ route('manager.reports.index') }}"
           class="nav-item {{ $active('manager/reports') }}">
            <div class="icon"><i class="bi bi-graph-up"></i></div>
            Laporan
        </a>
    </nav>

    {{-- Footer --}}
    <div class="p-4 border-t border-slate-800">
        <a href="{{ route('auth.logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 transition text-sm font-semibold border border-red-500/20">
            <i class="bi bi-box-arrow-left"></i> Keluar
        </a>
        <form id="logout-form" action="{{ route('auth.logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</aside>
