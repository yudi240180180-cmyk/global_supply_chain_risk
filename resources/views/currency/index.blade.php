@extends('layouts.app')

@section('title', 'Currency Impact Dashboard')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold">💱 Currency Impact Dashboard</h1>
            <p class="text-slate-400 mt-1">Real-time exchange rates vs USD — track volatility & supply chain cost impact</p>
        </div>
        <div class="glass rounded-xl px-5 py-3 text-center">
            <div class="text-2xl font-bold">{{ $totalCurrencies }}</div>
            <div class="text-xs text-slate-400">Currencies Tracked</div>
        </div>
    </div>

    {{-- Top Movers --}}
    @if($topMovers->count())
    <div class="glass rounded-2xl p-6">
        <h2 class="text-xl font-bold mb-5">📈 Top Currency Movers</h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach($topMovers->take(10) as $mover)
                @php
                    $up = $mover['change_pct'] >= 0;
                    $country = $countries[$mover['currency_code']] ?? null;
                @endphp
                <div class="bg-slate-800/60 rounded-xl p-4 text-center hover:bg-slate-700/50 transition cursor-pointer"
                     onclick="loadChart('{{ $mover['currency_code'] }}')">
                    @if($country?->flag_url)
                        <img src="{{ $country->flag_url }}" class="w-10 h-7 object-cover rounded mx-auto mb-2">
                    @endif
                    <div class="font-bold text-base">{{ $mover['currency_code'] }}</div>
                    <div class="text-slate-400 text-xs">{{ $country?->name ?? '—' }}</div>
                    <div class="mt-2 text-lg font-black {{ $up ? 'text-green-400' : 'text-red-400' }}">
                        {{ $up ? '▲' : '▼' }} {{ abs($mover['change_pct']) }}%
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ number_format($mover['current_rate'], 4) }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-3 gap-6">

        {{-- Chart Panel --}}
        <div class="col-span-2 glass rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold" id="chartTitle">Select a currency to view trend</h2>
                <select id="currencySelect"
                    class="bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                    <option value="">— Pick a currency —</option>
                    @foreach($latestRates as $rate)
                        <option value="{{ $rate->currency_code }}">
                            {{ $rate->currency_code }}
                            @if(isset($countries[$rate->currency_code]))
                                — {{ $countries[$rate->currency_code]->name }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div id="chartPlaceholder" class="flex items-center justify-center h-72 text-slate-500">
                <div class="text-center">
                    <div class="text-4xl mb-3">📊</div>
                    <div>Select a currency above to see its historical trend</div>
                </div>
            </div>
            <div id="chartWrapper" class="hidden" style="height:300px">
                <canvas id="currencyChart"></canvas>
            </div>
            <div id="rateInfo" class="hidden mt-4 grid grid-cols-3 gap-4 text-center"></div>
        </div>

        {{-- Rate Table --}}
        <div class="glass rounded-2xl p-5 flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-bold">All Rates vs USD</h2>
                <input id="rateSearch" type="text" placeholder="Search…"
                    class="bg-slate-800 border border-slate-600 rounded-lg px-3 py-1.5 text-sm text-white focus:outline-none focus:border-blue-500 w-28">
            </div>
            <div class="overflow-y-auto scrollbar flex-1" style="max-height:400px">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-slate-900">
                        <tr class="text-slate-400 text-xs">
                            <th class="py-2 text-left">Currency</th>
                            <th class="py-2 text-right">Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/40" id="rateTableBody">
                        @foreach($latestRates as $rate)
                            @php $c = $countries[$rate->currency_code] ?? null; @endphp
                            <tr class="rate-row hover:bg-slate-800/40 cursor-pointer transition"
                                data-code="{{ $rate->currency_code }}"
                                data-name="{{ strtolower($c?->name ?? $rate->currency_code) }}"
                                onclick="loadChart('{{ $rate->currency_code }}')">
                                <td class="py-2.5 pr-2">
                                    <div class="flex items-center gap-2">
                                        @if($c?->flag_url)
                                            <img src="{{ $c->flag_url }}" class="w-6 h-4 object-cover rounded">
                                        @endif
                                        <div>
                                            <div class="font-semibold">{{ $rate->currency_code }}</div>
                                            <div class="text-xs text-slate-500 truncate max-w-[90px]">{{ $c?->name ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5 text-right font-mono text-sm">
                                    {{ number_format($rate->rate_to_usd, 4) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Full Rate Grid --}}
    <div class="glass rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold">All Exchange Rates vs USD</h2>
            <input id="gridSearch" type="text" placeholder="Filter currencies…"
                class="bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-blue-500 w-52">
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-3" id="rateGrid">
            @foreach($latestRates as $rate)
                @php $c = $countries[$rate->currency_code] ?? null; @endphp
                <div class="rate-card bg-slate-800/50 rounded-xl p-3 hover:bg-slate-700/50 transition cursor-pointer"
                     data-code="{{ $rate->currency_code }}"
                     data-country="{{ strtolower($c?->name ?? '') }}"
                     onclick="loadChart('{{ $rate->currency_code }}')">
                    <div class="flex items-center gap-2 mb-2">
                        @if($c?->flag_url)
                            <img src="{{ $c->flag_url }}" class="w-7 h-5 object-cover rounded">
                        @endif
                        <span class="font-bold text-sm">{{ $rate->currency_code }}</span>
                    </div>
                    <div class="font-mono text-base font-bold">{{ number_format($rate->rate_to_usd, 4) }}</div>
                    <div class="text-xs text-slate-500 truncate">{{ $c?->name ?? '—' }}</div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
let currencyChartInstance = null;

async function loadChart(code) {
    document.getElementById('currencySelect').value = code;
    document.getElementById('chartTitle').textContent = code + ' / USD Historical Rate';
    document.getElementById('chartPlaceholder').classList.add('hidden');
    document.getElementById('chartWrapper').classList.remove('hidden');

    try {
        const res  = await fetch(`/api/currency/${code}/history`);
        const data = await res.json();

        if (data.message) {
            document.getElementById('chartWrapper').innerHTML = '<p class="text-slate-400 p-8 text-center">No history data available.</p>';
            return;
        }

        const labels = data.history.map(h => {
            const d = new Date(h.fetched_at);
            return d.toLocaleDateString('en-GB', { day:'2-digit', month:'short' });
        });
        const values = data.history.map(h => h.rate_to_usd);

        if (currencyChartInstance) currencyChartInstance.destroy();

        currencyChartInstance = new Chart(document.getElementById('currencyChart'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: `${code}/USD`,
                    data: values,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139,92,246,0.1)',
                    fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#8b5cf6',
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#94a3b8' } } },
                scales: {
                    x: { ticks: { color: '#64748b', maxTicksLimit: 10 }, grid: { color: '#1e293b' } },
                    y: { ticks: { color: '#64748b' }, grid: { color: '#1e293b' } }
                }
            }
        });

        // Show stats
        const min = Math.min(...values).toFixed(4);
        const max = Math.max(...values).toFixed(4);
        const latest = values[values.length - 1].toFixed(4);
        const rateInfo = document.getElementById('rateInfo');
        rateInfo.classList.remove('hidden');
        rateInfo.innerHTML = `
            <div class="bg-slate-800/50 rounded-xl p-3"><div class="text-slate-400 text-xs">Current</div><div class="font-bold text-lg">${latest}</div></div>
            <div class="bg-slate-800/50 rounded-xl p-3"><div class="text-slate-400 text-xs">Period High</div><div class="font-bold text-lg text-green-400">${max}</div></div>
            <div class="bg-slate-800/50 rounded-xl p-3"><div class="text-slate-400 text-xs">Period Low</div><div class="font-bold text-lg text-red-400">${min}</div></div>
        `;
    } catch(e) {
        console.error(e);
    }
}

// Dropdown
document.getElementById('currencySelect').addEventListener('change', function() {
    if (this.value) loadChart(this.value);
});

// Rate table search
document.getElementById('rateSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.rate-row').forEach(r => {
        r.style.display = (r.dataset.code.toLowerCase().includes(q) || r.dataset.name.includes(q)) ? '' : 'none';
    });
});

// Grid search
document.getElementById('gridSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.rate-card').forEach(c => {
        c.style.display = (c.dataset.code.toLowerCase().includes(q) || c.dataset.country.includes(q)) ? '' : 'none';
    });
});
</script>
@endpush
