@extends('layouts.app')

@section('title', 'Admin Watchlist Monitor')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div>
        <h1 class="text-4xl font-bold">👁️ Watchlist Monitor</h1>
        <p class="text-slate-400 mt-1">Monitor all user watchlists - No login required</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-6">
        <div class="glass rounded-2xl p-6">
            <div class="text-slate-400 text-sm">Total Watchlists</div>
            <div class="text-4xl font-bold mt-2">{{ $stats['total_watchlists'] }}</div>
        </div>
        <div class="glass rounded-2xl p-6">
            <div class="text-slate-400 text-sm">Unique Users</div>
            <div class="text-4xl font-bold mt-2">{{ $stats['unique_users'] }}</div>
        </div>
        <div class="glass rounded-2xl p-6">
            <div class="text-slate-400 text-sm">Unique Countries</div>
            <div class="text-4xl font-bold mt-2">{{ $stats['unique_countries'] }}</div>
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="glass rounded-2xl p-6">
        <div class="flex gap-4 flex-wrap">
            <div class="flex-1 min-w-[200px]">
                <input id="searchInput" type="text" placeholder="Search by country or email…"
                    class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-blue-500">
            </div>
            <button onclick="clearSearch()"
                class="px-6 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 transition">
                Clear
            </button>
        </div>
    </div>

    {{-- Watchlist Table --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-800/60">
                    <tr class="text-left text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3">User Email</th>
                        <th class="px-5 py-3">Country</th>
                        <th class="px-5 py-3">Added</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40" id="watchlistBody">
                    @forelse($watchlists as $wl)
                        <tr class="hover:bg-slate-800/30 transition watchlist-row"
                            data-email="{{ strtolower($wl->email) }}"
                            data-country="{{ strtolower($wl->country_name) }}">
                            <td class="px-5 py-3 font-semibold text-blue-400">{{ $wl->email }}</td>
                            <td class="px-5 py-3">{{ $wl->country_name }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ $wl->created_at->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('countries.show', $wl->country_id) }}"
                                    class="text-blue-400 hover:text-blue-300 text-xs">View →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-slate-400">
                                No watchlists yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Summary --}}
    <div class="glass rounded-2xl p-6">
        <h2 class="text-xl font-bold mb-4">📊 Summary</h2>
        <div class="text-slate-400 text-sm space-y-2">
            <p>✓ {{ $stats['total_watchlists'] }} countries are being monitored by {{ $stats['unique_users'] }} users</p>
            <p>✓ Users can add/remove countries from their watchlist to monitor risk changes</p>
            <p>✓ Refresh this page to see live updates</p>
        </div>
    </div>

</div>

<script>
document.getElementById('searchInput').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.watchlist-row').forEach(row => {
        const email = row.dataset.email;
        const country = row.dataset.country;
        const matches = email.includes(query) || country.includes(query);
        row.style.display = matches ? '' : 'none';
    });
});

function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.querySelectorAll('.watchlist-row').forEach(row => {
        row.style.display = '';
    });
}
</script>

@endsection
