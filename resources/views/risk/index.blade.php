@extends('layouts.app')

@section('title', 'Risk Scoring Engine')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold">⚠️ Risk Scoring Engine</h1>
            <p class="text-slate-400 mt-1">
                Weighted composite risk: Weather 25% · Economic 35% · Currency 15% · News 25%
            </p>
        </div>
        @if($latestBatch)
        <div class="glass rounded-xl px-5 py-3 text-right">
            <div class="text-xs text-slate-400">Last Calculated</div>
            <div class="font-semibold">{{ \Carbon\Carbon::parse($latestBatch)->format('d M Y H:i') }}</div>
        </div>
        @endif
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-4 gap-5">
        <div class="glass rounded-2xl p-6 text-center border-t-4 border-slate-500">
            <div class="text-4xl font-black">{{ $risks->count() }}</div>
            <div class="text-slate-400 text-sm mt-1">Countries Scored</div>
            <div class="text-xs text-slate-500 mt-1">Avg: {{ number_format($avgScore, 1) }}</div>
        </div>
        <div class="glass rounded-2xl p-6 text-center border-t-4 border-red-500">
            <div class="text-4xl font-black text-red-400">{{ $highCount }}</div>
            <div class="text-slate-400 text-sm mt-1">High Risk</div>
            <div class="text-xs text-slate-500 mt-1">Score ≥ 65</div>
        </div>
        <div class="glass rounded-2xl p-6 text-center border-t-4 border-yellow-500">
            <div class="text-4xl font-black text-yellow-400">{{ $mediumCount }}</div>
            <div class="text-slate-400 text-sm mt-1">Medium Risk</div>
            <div class="text-xs text-slate-500 mt-1">Score 35–64</div>
        </div>
        <div class="glass rounded-2xl p-6 text-center border-t-4 border-green-500">
            <div class="text-4xl font-black text-green-400">{{ $lowCount }}</div>
            <div class="text-slate-400 text-sm mt-1">Low Risk</div>
            <div class="text-xs text-slate-500 mt-1">Score &lt; 35</div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-2 gap-6">

        {{-- Distribution Doughnut --}}
        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-bold mb-4">Risk Distribution</h2>
            <div style="height:280px">
                <canvas id="distributionChart"></canvas>
            </div>
        </div>

        {{-- Global Trend --}}
        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-bold mb-4">Global Average Risk Trend</h2>
            <div style="height:280px">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

    </div>

    {{-- Filter Bar --}}
    <div class="glass rounded-2xl p-5">
        <div class="flex gap-4 flex-wrap items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-slate-400 mb-1">Search Country</label>
                <input id="searchInput" type="text" placeholder="e.g. Indonesia, Germany…"
                    class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Risk Level</label>
                <select id="levelFilter" class="bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                    <option value="">All Levels</option>
                    <option value="High">🔴 High</option>
                    <option value="Medium">🟡 Medium</option>
                    <option value="Low">🟢 Low</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Sort By</label>
                <select id="sortSelect" class="bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                    <option value="score-desc">Score: High → Low</option>
                    <option value="score-asc">Score: Low → High</option>
                    <option value="name-asc">Name: A → Z</option>
                </select>
            </div>
            <button onclick="clearFilters()" class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 transition text-sm">
                Clear
            </button>
        </div>
    </div>

    {{-- Risk Table --}}
    <div class="glass rounded-2xl overflow-hidden">
        <table class="w-full text-sm" id="riskTable">
            <thead class="bg-slate-800/80">
                <tr class="text-left text-slate-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">#</th>
                    <th class="px-6 py-4">Country</th>
                    <th class="px-6 py-4 text-center">Risk Level</th>
                    <th class="px-6 py-4 text-right">Total Score</th>
                    <th class="px-6 py-4 text-right">Weather</th>
                    <th class="px-6 py-4 text-right">Economic</th>
                    <th class="px-6 py-4 text-right">Currency</th>
                    <th class="px-6 py-4 text-right">News</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50" id="riskBody">
                @foreach($risks as $i => $risk)
                    @php
                        $level = $risk->risk_level;
                        $badgeClass = match($level) {
                            'High'   => 'bg-red-500/20 text-red-400 border border-red-500/40',
                            'Medium' => 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/40',
                            'Low'    => 'bg-green-500/20 text-green-400 border border-green-500/40',
                            default  => 'bg-slate-500/20 text-slate-400',
                        };
                        $barColor = match($level) {
                            'High'   => 'bg-red-500',
                            'Medium' => 'bg-yellow-500',
                            'Low'    => 'bg-green-500',
                            default  => 'bg-slate-500',
                        };
                    @endphp
                    <tr class="risk-row hover:bg-slate-800/30 transition"
                        data-name="{{ strtolower(optional($risk->country)->name ?? '') }}"
                        data-level="{{ $level }}"
                        data-score="{{ $risk->total_score }}">

                        <td class="px-6 py-4 text-slate-500 font-mono">{{ $i + 1 }}</td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if(optional($risk->country)->flag_url)
                                    <img src="{{ $risk->country->flag_url }}" class="w-8 h-5 object-cover rounded shadow">
                                @endif
                                <div>
                                    <div class="font-semibold">{{ optional($risk->country)->name ?? '—' }}</div>
                                    <div class="text-xs text-slate-500">{{ optional($risk->country)->region ?? '' }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $badgeClass }}">
                                {{ $level }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <div class="w-20 bg-slate-700 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $barColor }}"
                                         style="width:{{ min($risk->total_score, 100) }}%"></div>
                                </div>
                                <span class="font-bold text-base w-10 text-right">{{ number_format($risk->total_score, 1) }}</span>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-right text-slate-300">{{ number_format($risk->weather_score, 1) }}</td>
                        <td class="px-6 py-4 text-right text-slate-300">{{ number_format($risk->inflation_score, 1) }}</td>
                        <td class="px-6 py-4 text-right text-slate-300">{{ number_format($risk->currency_score, 1) }}</td>
                        <td class="px-6 py-4 text-right text-slate-300">{{ number_format($risk->news_score, 1) }}</td>

                        <td class="px-6 py-4 text-center">
                            @if(optional($risk->country)->id)
                            <a href="{{ route('countries.show', $risk->country->id) }}"
                               class="px-3 py-1 rounded-lg bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 text-xs transition">
                                Detail
                            </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div id="noRiskResults" class="hidden text-center py-10 text-slate-400">
            No countries match your filters.
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// ── Charts ──────────────────────────────────────────────────────────────────
new Chart(document.getElementById('distributionChart'), {
    type: 'doughnut',
    data: {
        labels: ['High Risk', 'Medium Risk', 'Low Risk'],
        datasets: [{
            data: [{{ $highCount }}, {{ $mediumCount }}, {{ $lowCount }}],
            backgroundColor: ['#ef4444', '#f59e0b', '#22c55e'],
            borderWidth: 0,
            hoverOffset: 8,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: '#94a3b8', padding: 20 } }
        },
        cutout: '65%',
    }
});

new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: @json($trendData->pluck('day')),
        datasets: [{
            label: 'Avg Risk Score',
            data: @json($trendData->pluck('avg_score')),
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245,158,11,0.12)',
            fill: true,
            tension: 0.4,
            pointRadius: 3,
            pointBackgroundColor: '#f59e0b',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { labels: { color: '#94a3b8' } } },
        scales: {
            x: { ticks: { color: '#64748b', maxTicksLimit: 8 }, grid: { color: '#1e293b' } },
            y: { ticks: { color: '#64748b' }, grid: { color: '#1e293b' }, min: 0, max: 100 }
        }
    }
});

// ── Filter & Sort ────────────────────────────────────────────────────────────
const searchInput = document.getElementById('searchInput');
const levelFilter = document.getElementById('levelFilter');
const sortSelect  = document.getElementById('sortSelect');
const tbody       = document.getElementById('riskBody');
const noResults   = document.getElementById('noRiskResults');

function applyFilters() {
    const search = searchInput.value.toLowerCase().trim();
    const level  = levelFilter.value;
    let rows     = Array.from(tbody.querySelectorAll('.risk-row'));

    rows.forEach(row => {
        const matchName  = !search || row.dataset.name.includes(search);
        const matchLevel = !level  || row.dataset.level === level;
        row.style.display = (matchName && matchLevel) ? '' : 'none';
    });

    // Sort
    const sort = sortSelect.value;
    const visible = rows.filter(r => r.style.display !== 'none');
    visible.sort((a, b) => {
        if (sort === 'score-desc') return parseFloat(b.dataset.score) - parseFloat(a.dataset.score);
        if (sort === 'score-asc')  return parseFloat(a.dataset.score) - parseFloat(b.dataset.score);
        if (sort === 'name-asc')   return a.dataset.name.localeCompare(b.dataset.name);
        return 0;
    });
    visible.forEach(r => tbody.appendChild(r));

    noResults.classList.toggle('hidden', visible.length > 0);
}

function clearFilters() {
    searchInput.value = '';
    levelFilter.value = '';
    sortSelect.value  = 'score-desc';
    applyFilters();
}

[searchInput, levelFilter, sortSelect].forEach(el => el.addEventListener('input', applyFilters));
[levelFilter, sortSelect].forEach(el => el.addEventListener('change', applyFilters));
</script>
@endpush
