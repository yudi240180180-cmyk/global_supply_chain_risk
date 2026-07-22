@extends('layouts.app')

@section('title', 'Country Comparison Engine')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div>
        <h1 class="text-4xl font-bold">⚖️ Country Comparison Engine</h1>
        <p class="text-slate-400 mt-1">Compare two countries side-by-side across GDP, inflation, risk, weather, and currency</p>
    </div>

    {{-- Selector --}}
    <div class="glass rounded-2xl p-6">
        <div class="flex gap-5 items-end flex-wrap">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-slate-400 mb-2">Country A</label>
                <select id="countryA" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                    <option value="">— Select Country —</option>
                    @foreach($countries as $c)
                        <option value="{{ $c->id }}" data-name="{{ $c->name }}"
                            {{ request('ids') && explode(',', request('ids'))[0] == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->region }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="text-3xl font-bold text-slate-500 pb-2">VS</div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-slate-400 mb-2">Country B</label>
                <select id="countryB" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                    <option value="">— Select Country —</option>
                    @foreach($countries as $c)
                        <option value="{{ $c->id }}" data-name="{{ $c->name }}"
                            {{ request('ids') && isset(explode(',', request('ids'))[1]) && explode(',', request('ids'))[1] == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->region }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button id="compareBtn"
                class="px-8 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 transition font-bold text-base flex-shrink-0">
                Compare
            </button>
        </div>
    </div>

    {{-- Loading --}}
    <div id="loadingState" class="hidden text-center py-16">
        <div class="text-4xl animate-spin inline-block mb-4">⟳</div>
        <div class="text-slate-400">Loading comparison data…</div>
    </div>

    {{-- Empty State --}}
    <div id="emptyState" class="glass rounded-2xl p-16 text-center">
        <div class="text-6xl mb-4">🌍</div>
        <div class="text-xl font-semibold">Select two countries above</div>
        <p class="text-slate-400 mt-2">The comparison will appear here instantly.</p>
    </div>

    {{-- Comparison Result --}}
    <div id="comparisonResult" class="hidden space-y-6">

        {{-- Country Headers --}}
        <div class="grid grid-cols-2 gap-6" id="countryHeaders"></div>

        {{-- Risk Comparison --}}
        <div class="glass rounded-2xl p-6" id="riskCompare"></div>

        {{-- Economics --}}
        <div class="glass rounded-2xl p-6" id="economicsCompare"></div>

        {{-- Weather --}}
        <div class="glass rounded-2xl p-6" id="weatherCompare"></div>

        {{-- Radar Chart --}}
        <div class="glass rounded-2xl p-6">
            <h2 class="text-xl font-bold mb-4">📊 Risk Component Radar</h2>
            <div style="max-width:480px; margin:auto; height:360px">
                <canvas id="radarChart"></canvas>
            </div>
        </div>

        {{-- Bar Chart: GDP --}}
        <div class="glass rounded-2xl p-6">
            <h2 class="text-xl font-bold mb-4">📈 GDP & Inflation Comparison</h2>
            <div style="height:280px">
                <canvas id="barChart"></canvas>
            </div>
        </div>

    </div>

</div>

<script>
(function() {
    console.log('=== COMPARE PAGE LOADED ===');
    
    let radarInstance = null;
    let barInstance = null;
    const n = (v) => v == null ? null : parseFloat(v);
    const fmt = (v, digits = 1) => v == null ? '—' : n(v).toFixed(digits);

    function riskBg(level) {
        return level === 'High' ? 'bg-red-500/20 border-red-500/40 text-red-400'
             : level === 'Medium' ? 'bg-yellow-500/20 border-yellow-500/40 text-yellow-400'
             : 'bg-green-500/20 border-green-500/40 text-green-400';
    }

    function winner(valA, valB, lowerIsBetter = false) {
        if (valA == null || valB == null) return ['', ''];
        const aWins = lowerIsBetter ? valA < valB : valA > valB;
        return aWins ? ['🏆', ''] : ['', '🏆'];
    }

    window.runComparison = async function() {
        console.log('runComparison called');
        const a = document.getElementById('countryA').value;
        const b = document.getElementById('countryB').value;
        if (!a || !b) { alert('Please select two countries.'); return; }
        if (a === b) { alert('Please select two different countries.'); return; }

        document.getElementById('emptyState').classList.add('hidden');
        document.getElementById('comparisonResult').classList.add('hidden');
        document.getElementById('loadingState').classList.remove('hidden');

        try {
            console.log('Fetching /api/compare?ids=' + a + ',' + b);
            const res = await fetch(`/api/compare?ids=${a},${b}`);
            console.log('Response status:', res.status);
            if (!res.ok) {
                const text = await res.text();
                throw new Error(`HTTP ${res.status}: ${text}`);
            }
            const data = await res.json();
            console.log('Response data:', data);
            if (!Array.isArray(data) || data.length < 1) throw new Error('No data');
            renderComparison(data[0], data[1] ?? data[0]);
        } catch(e) {
            console.error('Compare error:', e);
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('emptyState').classList.remove('hidden');
            document.getElementById('emptyState').innerHTML = `<div class="text-red-400 text-lg">${e.message}</div>`;
        }
    };

    function renderComparison(a, b) {
        console.log('Rendering:', a.name, 'vs', b.name);
        document.getElementById('loadingState').classList.add('hidden');
        document.getElementById('comparisonResult').classList.remove('hidden');

        // Headers
        document.getElementById('countryHeaders').innerHTML = [a, b].map(c => `
            <div class="glass rounded-2xl p-6 text-center">
                ${c.flag_url ? `<img src="${c.flag_url}" class="w-20 h-14 object-cover rounded-xl mx-auto mb-3">` : ''}
                <h2 class="text-3xl font-black">${c.name}</h2>
                <div class="text-slate-400 text-sm">${c.region ?? ''}</div>
            </div>
        `).join('');

        // Risk
        const ra = a.risk, rb = b.risk;
        const riskRows = [
            ['Total Risk Score', ra?.total_score ? fmt(ra.total_score, 2) : '—', rb?.total_score ? fmt(rb.total_score, 2) : '—'],
            ['Risk Level', ra?.risk_level ?? '—', rb?.risk_level ?? '—'],
            ['Weather Score', ra?.weather_score ? fmt(ra.weather_score, 2) : '—', rb?.weather_score ? fmt(rb.weather_score, 2) : '—'],
            ['Inflation Score', ra?.inflation_score ? fmt(ra.inflation_score, 2) : '—', rb?.inflation_score ? fmt(rb.inflation_score, 2) : '—'],
            ['Currency Score', ra?.currency_score ? fmt(ra.currency_score, 2) : '—', rb?.currency_score ? fmt(rb.currency_score, 2) : '—'],
            ['News Score', ra?.news_score ? fmt(ra.news_score, 2) : '—', rb?.news_score ? fmt(rb.news_score, 2) : '—'],
        ];
        document.getElementById('riskCompare').innerHTML = `
            <h2 class="text-xl font-bold mb-5">⚠️ Risk Assessment</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-slate-400 text-xs uppercase border-b border-slate-700">
                    <th class="py-2 text-left">Component</th>
                    <th class="py-2 text-center">${a.name}</th>
                    <th class="py-2 text-center">${b.name}</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-700/40">
                    ${riskRows.map(([l,va,vb]) => `
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 text-slate-400">${l}</td>
                        <td class="py-3 text-center font-semibold">${va}</td>
                        <td class="py-3 text-center font-semibold">${vb}</td>
                    </tr>`).join('')}
                </tbody>
            </table>
        `;

        // Economics
        const ea = a.economics, eb = b.economics;
        document.getElementById('economicsCompare').innerHTML = `
            <h2 class="text-xl font-bold mb-5">📊 Economic Indicators</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-slate-400 text-xs uppercase border-b border-slate-700">
                    <th class="py-2 text-left">Indicator</th>
                    <th class="py-2 text-center">${a.name}</th>
                    <th class="py-2 text-center">${b.name}</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-700/40">
                    <tr><td class="py-3 text-slate-400">GDP (USD)</td><td class="py-3 text-center">${ea?.gdp ? '$' + (n(ea.gdp)/1e9).toFixed(1) + 'B' : '—'}</td><td class="py-3 text-center">${eb?.gdp ? '$' + (n(eb.gdp)/1e9).toFixed(1) + 'B' : '—'}</td></tr>
                    <tr><td class="py-3 text-slate-400">Inflation %</td><td class="py-3 text-center">${ea?.inflation ? fmt(ea.inflation, 2) + '%' : '—'}</td><td class="py-3 text-center">${eb?.inflation ? fmt(eb.inflation, 2) + '%' : '—'}</td></tr>
                    <tr><td class="py-3 text-slate-400">Exports (USD)</td><td class="py-3 text-center">${ea?.exports ? '$' + (n(ea.exports)/1e9).toFixed(1) + 'B' : '—'}</td><td class="py-3 text-center">${eb?.exports ? '$' + (n(eb.exports)/1e9).toFixed(1) + 'B' : '—'}</td></tr>
                    <tr><td class="py-3 text-slate-400">Imports (USD)</td><td class="py-3 text-center">${ea?.imports ? '$' + (n(ea.imports)/1e9).toFixed(1) + 'B' : '—'}</td><td class="py-3 text-center">${eb?.imports ? '$' + (n(eb.imports)/1e9).toFixed(1) + 'B' : '—'}</td></tr>
                </tbody>
            </table>
        `;

        // Weather
        const wa = a.weather, wb = b.weather;
        document.getElementById('weatherCompare').innerHTML = `
            <h2 class="text-xl font-bold mb-5">🌤️ Weather</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-slate-400 text-xs uppercase border-b border-slate-700">
                    <th class="py-2 text-left">Parameter</th>
                    <th class="py-2 text-center">${a.name}</th>
                    <th class="py-2 text-center">${b.name}</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-700/40">
                    <tr><td class="py-3 text-slate-400">Temperature</td><td class="py-3 text-center">${wa?.temperature ? fmt(wa.temperature) + '°C' : '—'}</td><td class="py-3 text-center">${wb?.temperature ? fmt(wb.temperature) + '°C' : '—'}</td></tr>
                    <tr><td class="py-3 text-slate-400">Rainfall</td><td class="py-3 text-center">${wa?.rainfall ? fmt(wa.rainfall) + 'mm' : '—'}</td><td class="py-3 text-center">${wb?.rainfall ? fmt(wb.rainfall) + 'mm' : '—'}</td></tr>
                    <tr><td class="py-3 text-slate-400">Wind Speed</td><td class="py-3 text-center">${wa?.wind_speed ? fmt(wa.wind_speed) + 'km/h' : '—'}</td><td class="py-3 text-center">${wb?.wind_speed ? fmt(wb.wind_speed) + 'km/h' : '—'}</td></tr>
                    <tr><td class="py-3 text-slate-400">Storm Risk</td><td class="py-3 text-center">${wa?.storm_risk ? Math.round(wa.storm_risk) + '/100' : '—'}</td><td class="py-3 text-center">${wb?.storm_risk ? Math.round(wb.storm_risk) + '/100' : '—'}</td></tr>
                </tbody>
            </table>
        `;

        // Radar Chart
        if (radarInstance) radarInstance.destroy();
        if (typeof Chart !== 'undefined' && ra && rb) {
            console.log('Creating radar chart');
            const radarCtx = document.getElementById('radarChart');
            if (radarCtx) {
                radarInstance = new Chart(radarCtx, {
                    type: 'radar',
                    data: {
                        labels: ['Weather', 'Inflation', 'Currency', 'News', 'Total'],
                        datasets: [
                            {
                                label: a.name,
                                data: [n(ra.weather_score)||0, n(ra.inflation_score)||0, n(ra.currency_score)||0, n(ra.news_score)||0, n(ra.total_score)||0],
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59,130,246,0.15)',
                                pointBackgroundColor: '#3b82f6',
                                borderWidth: 2,
                                pointRadius: 4,
                            },
                            {
                                label: b.name,
                                data: [n(rb.weather_score)||0, n(rb.inflation_score)||0, n(rb.currency_score)||0, n(rb.news_score)||0, n(rb.total_score)||0],
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239,68,68,0.15)',
                                pointBackgroundColor: '#ef4444',
                                borderWidth: 2,
                                pointRadius: 4,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { labels: { color: '#94a3b8', font: { size: 12 } } } },
                        scales: {
                            r: {
                                ticks: { color: '#64748b', backdropColor: 'transparent' },
                                grid: { color: '#1e293b' },
                                pointLabels: { color: '#94a3b8', font: { size: 11 } },
                                min: 0,
                                max: 100
                            }
                        }
                    }
                });
            }
        }

        // Bar Chart
        if (barInstance) barInstance.destroy();
        if (typeof Chart !== 'undefined' && ea && eb) {
            console.log('Creating bar chart');
            const barCtx = document.getElementById('barChart');
            if (barCtx) {
                barInstance = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: ['GDP (B USD)', 'Inflation (%)', 'Exports (B USD)', 'Imports (B USD)'],
                        datasets: [
                            {
                                label: a.name,
                                data: [
                                    n(ea.gdp)/1e9 || 0,
                                    n(ea.inflation) || 0,
                                    n(ea.exports)/1e9 || 0,
                                    n(ea.imports)/1e9 || 0
                                ],
                                backgroundColor: 'rgba(59,130,246,0.7)',
                                borderColor: '#3b82f6',
                                borderWidth: 1,
                            },
                            {
                                label: b.name,
                                data: [
                                    n(eb.gdp)/1e9 || 0,
                                    n(eb.inflation) || 0,
                                    n(eb.exports)/1e9 || 0,
                                    n(eb.imports)/1e9 || 0
                                ],
                                backgroundColor: 'rgba(239,68,68,0.7)',
                                borderColor: '#ef4444',
                                borderWidth: 1,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { labels: { color: '#94a3b8', font: { size: 12 } } } },
                        scales: {
                            x: { ticks: { color: '#64748b' }, grid: { color: '#1e293b' } },
                            y: { ticks: { color: '#64748b' }, grid: { color: '#1e293b' } }
                        }
                    }
                });
            }
        }
    }

    // Setup button
    const btn = document.getElementById('compareBtn');
    if (btn) {
        btn.addEventListener('click', window.runComparison);
        console.log('Button listener attached');
    }

    // Auto-run if URL has ids
    const preIds = new URLSearchParams(window.location.search).get('ids');
    if (preIds) {
        const parts = preIds.split(',');
        if (parts[0]) document.getElementById('countryA').value = parts[0];
        if (parts[1]) document.getElementById('countryB').value = parts[1];
        if (parts[0] && parts[1]) setTimeout(window.runComparison, 300);
    }

    console.log('=== COMPARE PAGE READY ===');
})();
</script>

@endsection
