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
            <button id="compareBtn" onclick="runComparison()"
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
@endsection

@push('scripts')
<script>
let radarInstance = null;
let barInstance   = null;

// Auto-run if IDs provided in URL
const urlParams = new URLSearchParams(window.location.search);
const preIds = urlParams.get('ids');
if (preIds) {
    const parts = preIds.split(',');
    if (parts[0]) document.getElementById('countryA').value = parts[0];
    if (parts[1]) document.getElementById('countryB').value = parts[1];
    if (parts[0] && parts[1]) setTimeout(runComparison, 200);
}

async function runComparison() {
    const a = document.getElementById('countryA').value;
    const b = document.getElementById('countryB').value;
    if (!a || !b) { alert('Please select two countries.'); return; }
    if (a === b)  { alert('Please select two different countries.'); return; }

    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('comparisonResult').classList.add('hidden');
    document.getElementById('loadingState').classList.remove('hidden');

    try {
        const res  = await fetch(`/api/compare?ids=${a},${b}`);
        const data = await res.json();
        if (!Array.isArray(data) || data.length < 2) throw new Error('Incomplete data');

        renderComparison(data[0], data[1]);
    } catch(e) {
        document.getElementById('loadingState').classList.add('hidden');
        document.getElementById('emptyState').classList.remove('hidden');
        document.getElementById('emptyState').innerHTML = `<div class="text-red-400 text-xl">Failed to load data. Please try again.</div>`;
    }
}

function riskColor(level) {
    return level === 'High' ? '#ef4444' : level === 'Medium' ? '#f59e0b' : '#22c55e';
}
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

function renderComparison(a, b) {
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('comparisonResult').classList.remove('hidden');

    // ── Headers ──────────────────────────────────────────────────────────────
    document.getElementById('countryHeaders').innerHTML = [a, b].map(c => `
        <div class="glass rounded-2xl p-6 text-center">
            ${c.flag_url ? `<img src="${c.flag_url}" class="w-20 h-14 object-cover rounded-xl mx-auto mb-3 shadow-lg">` : '<div class="w-20 h-14 bg-slate-700 rounded-xl mx-auto mb-3"></div>'}
            <h2 class="text-3xl font-black">${c.name}</h2>
            <div class="text-slate-400 text-sm mt-1">${c.region ?? ''} · ${c.capital ?? ''}</div>
            <div class="text-slate-400 text-sm">${c.currency_code ?? ''} — ${c.currency_name ?? ''}</div>
            ${c.exchange_rate_usd ? `<div class="mt-2 text-sm">1 USD = <b>${c.exchange_rate_usd.toFixed(4)} ${c.currency_code}</b></div>` : ''}
        </div>
    `).join('');

    // ── Risk ──────────────────────────────────────────────────────────────────
    const [wa, wb] = winner(a.risk?.total_score, b.risk?.total_score, true);
    document.getElementById('riskCompare').innerHTML = `
        <h2 class="text-xl font-bold mb-5">⚠️ Risk Comparison</h2>
        <div class="grid grid-cols-2 gap-8">
            ${[{c:a,w:wa},{c:b,w:wb}].map(({c,w}) => {
                const r = c.risk; const level = r?.risk_level ?? 'N/A';
                return `
                <div class="text-center">
                    <div class="text-lg font-bold mb-3">${c.name} ${w}</div>
                    <div class="text-6xl font-black" style="color:${riskColor(level)}">${r?.total_score ? r.total_score.toFixed(1) : '—'}</div>
                    <div class="mt-2 inline-block px-4 py-1 rounded-full text-sm font-bold border ${riskBg(level)}">${level} Risk</div>
                    ${r ? `
                    <div class="mt-4 space-y-2 text-sm text-left max-w-xs mx-auto">
                        ${[['Weather',r.weather_score],['Economic',r.inflation_score],['Currency',r.currency_score],['News',r.news_score]].map(([label,val]) => `
                            <div class="flex justify-between items-center gap-3">
                                <span class="text-slate-400 w-20">${label}</span>
                                <div class="flex-1 bg-slate-700 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-blue-500" style="width:${Math.min(val,100)}%"></div>
                                </div>
                                <span class="font-semibold w-10 text-right">${val.toFixed(1)}</span>
                            </div>
                        `).join('')}
                    </div>` : '<p class="text-slate-500 text-sm mt-3">No risk data</p>'}
                </div>`;
            }).join('')}
        </div>
    `;

    // ── Economics ─────────────────────────────────────────────────────────────
    const ea = a.economics, eb = b.economics;
    const [gaW, gbW] = winner(ea?.gdp, eb?.gdp);
    const [iaW, ibW] = winner(ea?.inflation, eb?.inflation, true);
    const rows = [
        ['GDP (USD)', ea?.gdp ? `$${(ea.gdp/1e9).toFixed(1)}B` : '—', eb?.gdp ? `$${(eb.gdp/1e9).toFixed(1)}B` : '—', gaW, gbW],
        ['Inflation %', ea?.inflation ? `${ea.inflation.toFixed(2)}%` : '—', eb?.inflation ? `${eb.inflation.toFixed(2)}%` : '—', iaW, ibW],
        ['Population', ea?.population ? `${(ea.population/1e6).toFixed(1)}M` : '—', eb?.population ? `${(eb.population/1e6).toFixed(1)}M` : '—', ...winner(ea?.population, eb?.population)],
        ['Exports', ea?.exports ? `$${(ea.exports/1e9).toFixed(1)}B` : '—', eb?.exports ? `$${(eb.exports/1e9).toFixed(1)}B` : '—', ...winner(ea?.exports, eb?.exports)],
        ['Imports', ea?.imports ? `$${(ea.imports/1e9).toFixed(1)}B` : '—', eb?.imports ? `$${(eb.imports/1e9).toFixed(1)}B` : '—', ...winner(ea?.imports, eb?.imports, true)],
        ['Data Year', ea?.data_year ?? '—', eb?.data_year ?? '—', '', ''],
    ];
    document.getElementById('economicsCompare').innerHTML = `
        <h2 class="text-xl font-bold mb-5">📊 Economic Indicators</h2>
        <table class="w-full text-sm">
            <thead><tr class="text-slate-400 text-xs uppercase border-b border-slate-700">
                <th class="py-2 text-left">Indicator</th>
                <th class="py-2 text-center">${a.name}</th>
                <th class="py-2 text-center">${b.name}</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-700/40">
                ${rows.map(([label, va, vb, aw, bw]) => `
                <tr class="hover:bg-slate-800/30 transition">
                    <td class="py-3 text-slate-400">${label}</td>
                    <td class="py-3 text-center font-semibold">${va} ${aw}</td>
                    <td class="py-3 text-center font-semibold">${vb} ${bw}</td>
                </tr>`).join('')}
            </tbody>
        </table>
    `;

    // ── Weather ───────────────────────────────────────────────────────────────
    const wa2 = a.weather, wb2 = b.weather;
    const wrows = [
        ['Temperature', wa2?.temperature != null ? `${wa2.temperature.toFixed(1)}°C` : '—', wb2?.temperature != null ? `${wb2.temperature.toFixed(1)}°C` : '—'],
        ['Rainfall', wa2?.rainfall != null ? `${wa2.rainfall.toFixed(1)} mm` : '—', wb2?.rainfall != null ? `${wb2.rainfall.toFixed(1)} mm` : '—'],
        ['Wind Speed', wa2?.wind_speed != null ? `${wa2.wind_speed.toFixed(1)} km/h` : '—', wb2?.wind_speed != null ? `${wb2.wind_speed.toFixed(1)} km/h` : '—'],
        ['Storm Risk', wa2?.storm_risk != null ? `${wa2.storm_risk.toFixed(0)}/100` : '—', wb2?.storm_risk != null ? `${wb2.storm_risk.toFixed(0)}/100` : '—'],
        ['Condition', wa2?.weather_condition ?? '—', wb2?.weather_condition ?? '—'],
    ];
    document.getElementById('weatherCompare').innerHTML = `
        <h2 class="text-xl font-bold mb-5">🌤️ Weather Conditions</h2>
        <table class="w-full text-sm">
            <thead><tr class="text-slate-400 text-xs uppercase border-b border-slate-700">
                <th class="py-2 text-left">Parameter</th>
                <th class="py-2 text-center">${a.name}</th>
                <th class="py-2 text-center">${b.name}</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-700/40">
                ${wrows.map(([label,va,vb]) => `
                <tr class="hover:bg-slate-800/30 transition">
                    <td class="py-3 text-slate-400">${label}</td>
                    <td class="py-3 text-center font-semibold">${va}</td>
                    <td class="py-3 text-center font-semibold">${vb}</td>
                </tr>`).join('')}
            </tbody>
        </table>
    `;

    // ── Radar Chart ───────────────────────────────────────────────────────────
    if (radarInstance) radarInstance.destroy();
    const ra = a.risk, rb = b.risk;
    if (ra && rb) {
        radarInstance = new Chart(document.getElementById('radarChart'), {
            type: 'radar',
            data: {
                labels: ['Weather', 'Economic', 'Currency', 'News', 'Total'],
                datasets: [
                    {
                        label: a.name,
                        data: [ra.weather_score, ra.inflation_score, ra.currency_score, ra.news_score, ra.total_score],
                        borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.15)', pointBackgroundColor: '#3b82f6',
                    },
                    {
                        label: b.name,
                        data: [rb.weather_score, rb.inflation_score, rb.currency_score, rb.news_score, rb.total_score],
                        borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.15)', pointBackgroundColor: '#ef4444',
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#94a3b8' } } },
                scales: { r: { ticks: { color: '#64748b', backdropColor: 'transparent' }, grid: { color: '#1e293b' }, pointLabels: { color: '#94a3b8' }, min: 0, max: 100 } }
            }
        });
    }

    // ── Bar Chart ─────────────────────────────────────────────────────────────
    if (barInstance) barInstance.destroy();
    if (ea && eb) {
        barInstance = new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: ['GDP (B USD)', 'Inflation (%)', 'Exports (B USD)', 'Imports (B USD)'],
                datasets: [
                    {
                        label: a.name,
                        data: [ea.gdp/1e9, ea.inflation, ea.exports/1e9, ea.imports/1e9],
                        backgroundColor: 'rgba(59,130,246,0.7)', borderColor: '#3b82f6', borderWidth: 1,
                    },
                    {
                        label: b.name,
                        data: [eb.gdp/1e9, eb.inflation, eb.exports/1e9, eb.imports/1e9],
                        backgroundColor: 'rgba(239,68,68,0.7)', borderColor: '#ef4444', borderWidth: 1,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#94a3b8' } } },
                scales: {
                    x: { ticks: { color: '#64748b' }, grid: { color: '#1e293b' } },
                    y: { ticks: { color: '#64748b' }, grid: { color: '#1e293b' } }
                }
            }
        });
    }
}
</script>
@endpush
