@extends('layouts.public')

@section('title', 'Global Supply Chain — Watchlist Monitor')

@section('content')
<div class="space-y-8">

    {{-- Hero Header --}}
    <div class="relative overflow-hidden rounded-3xl p-8 bg-gradient-to-br from-blue-900/40 via-slate-800/40 to-purple-900/40 border border-blue-500/20">
        <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 20% 50%, #3b82f6 0%, transparent 50%), radial-gradient(circle at 80% 50%, #8b5cf6 0%, transparent 50%)"></div>
        <div class="relative flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-4xl font-black tracking-tight">⭐ Country Watchlist Monitor</h1>
                <p class="text-slate-400 mt-2 text-lg">Real-time risk monitoring for {{ $countries->count() }} countries — No login required to view</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="glass rounded-2xl px-5 py-3 text-center">
                    <div class="text-2xl font-black text-green-400">{{ $countries->where('latestRiskScore.risk_level','Low')->count() }}</div>
                    <div class="text-xs text-slate-400 mt-1">Low Risk</div>
                </div>
                <div class="glass rounded-2xl px-5 py-3 text-center">
                    <div class="text-2xl font-black text-yellow-400">{{ $countries->where('latestRiskScore.risk_level','Medium')->count() }}</div>
                    <div class="text-xs text-slate-400 mt-1">Medium Risk</div>
                </div>
                <div class="glass rounded-2xl px-5 py-3 text-center">
                    <div class="text-2xl font-black text-red-400">{{ $countries->where('latestRiskScore.risk_level','High')->count() }}</div>
                    <div class="text-xs text-slate-400 mt-1">High Risk</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Auth Status Panel --}}
    <div id="authPanel" class="hidden glass rounded-2xl p-6 max-w-md border border-blue-500/30">
        <h3 class="text-lg font-bold mb-4">🔐 Login to Sync Watchlist</h3>
        <div class="space-y-3">
            <input id="loginEmail" type="email" placeholder="Email"
                class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500">
            <input id="loginPassword" type="password" placeholder="Password"
                class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500">
            <button onclick="doLogin()" class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 transition font-semibold">Login</button>
        </div>
        <div id="loginError" class="hidden mt-3 text-red-400 text-sm"></div>
    </div>

    {{-- Logged-in status bar --}}
    <div id="statusBar" class="hidden glass rounded-2xl px-5 py-3 flex items-center justify-between border border-green-500/20">
        <span class="text-green-400 font-semibold">✅ Logged in — Your watchlist is synced to server</span>
        <button onclick="doLogout()" class="px-4 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 transition text-sm">Logout</button>
    </div>

    <div class="grid grid-cols-12 gap-6">

        {{-- === Left: My Watchlist Panel === --}}
        <div class="col-span-12 lg:col-span-4 space-y-4">

            {{-- Save CTA --}}
            <div class="glass rounded-2xl p-4 border border-blue-500/20">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-bold">📋 My Watchlist</h2>
                    <button onclick="toggleAuthPanel()" id="authToggleBtn"
                        class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 transition text-xs font-semibold">
                        🔐 Login to Save
                    </button>
                </div>
                <p class="text-xs text-slate-500 mb-3">You can add countries freely. Login to persist across sessions.</p>
                <div id="watchlistContainer" class="space-y-2 min-h-[120px]">
                    <div id="watchlistEmpty" class="text-center py-8 text-slate-400">
                        <div class="text-3xl mb-2">📭</div>
                        <div class="text-sm">No countries added yet.</div>
                        <div class="text-xs mt-1">Click ⭐ on any country card.</div>
                    </div>
                </div>
            </div>

            {{-- High Risk Alert --}}
            @php
                $highRisk = $countries->filter(fn($c) => $c->latestRiskScore?->risk_level === 'High')->take(5);
            @endphp
            @if($highRisk->isNotEmpty())
            <div class="glass rounded-2xl p-4 border border-red-500/20">
                <h3 class="font-bold text-red-400 mb-3">🚨 High Risk Alert</h3>
                <div class="space-y-2">
                    @foreach($highRisk as $c)
                    <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-red-500/10">
                        <div class="flex items-center gap-2">
                            @if($c->flag_url)
                                <img src="{{ $c->flag_url }}" class="w-6 h-4 object-cover rounded">
                            @endif
                            <span class="text-sm font-semibold">{{ $c->name }}</span>
                        </div>
                        <span class="text-xs font-bold text-red-400">{{ number_format($c->latestRiskScore?->total_score ?? 0, 0) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Info Box --}}
            <div class="glass rounded-2xl p-4 border border-slate-600/30 text-sm text-slate-400 leading-relaxed">
                <div class="font-semibold text-slate-300 mb-2">ℹ️ About this page</div>
                <p>This public monitor lets anyone track supply chain risk across countries. All data is updated periodically from economic and news sources.</p>
            </div>
        </div>

        {{-- === Right: All Countries Grid === --}}
        <div class="col-span-12 lg:col-span-8 space-y-4">

            {{-- Search & Filter --}}
            <div class="glass rounded-2xl p-4 flex flex-col sm:flex-row gap-3">
                <input id="watchSearchInput" type="text" placeholder="🔍 Search countries…"
                    class="flex-1 bg-slate-800 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500">
                <select id="riskFilter" onchange="applyFilter()"
                    class="bg-slate-800 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500">
                    <option value="">All Risk Levels</option>
                    <option value="high">🔴 High Risk</option>
                    <option value="medium">🟡 Medium Risk</option>
                    <option value="low">🟢 Low Risk</option>
                </select>
            </div>

            {{-- Results Count --}}
            <div class="flex items-center justify-between text-sm text-slate-400 px-1">
                <span id="resultsCount">Showing all {{ $countries->count() }} countries</span>
                <span>Click ⭐ to add to watchlist</span>
            </div>

            {{-- Country Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3" id="allCountriesGrid">
                @foreach($countries as $country)
                    @php
                        $risk  = $country->latestRiskScore;
                        $eco   = $country->latestEconomics;
                        $wx    = $country->latestWeather;
                        $level = $risk?->risk_level ?? 'N/A';
                        $score = $risk?->total_score ?? 0;
                        $color = match($level) {
                            'High'   => ['badge' => 'bg-red-500/20 text-red-400 border-red-500/30',
                                         'border' => 'border-red-500/20',
                                         'dot'    => 'bg-red-500'],
                            'Medium' => ['badge' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                                         'border' => 'border-yellow-500/20',
                                         'dot'    => 'bg-yellow-500'],
                            'Low'    => ['badge' => 'bg-green-500/20 text-green-400 border-green-500/30',
                                         'border' => 'border-green-500/20',
                                         'dot'    => 'bg-green-500'],
                            default  => ['badge' => 'bg-slate-700 text-slate-400 border-slate-600',
                                         'border' => 'border-transparent',
                                         'dot'    => 'bg-slate-500'],
                        };
                    @endphp
                    <div class="watchable-card glass rounded-xl p-4 border {{ $color['border'] }} hover:border-blue-500/40 transition cursor-default"
                         data-id="{{ $country->id }}"
                         data-name="{{ strtolower($country->name) }}"
                         data-risk="{{ strtolower($level) }}">

                        {{-- Card Header --}}
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <div class="relative flex-shrink-0">
                                    @if($country->flag_url)
                                        <img src="{{ $country->flag_url }}" class="w-9 h-6 object-cover rounded shadow">
                                    @else
                                        <div class="w-9 h-6 rounded bg-slate-700 flex items-center justify-center text-xs">🌐</div>
                                    @endif
                                    <div class="absolute -bottom-1 -right-1 w-2.5 h-2.5 rounded-full {{ $color['dot'] }} border-2 border-slate-900"></div>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-sm truncate">{{ $country->name }}</div>
                                    <div class="text-xs text-slate-500 truncate">{{ $country->region }}</div>
                                </div>
                            </div>
                            <button onclick="toggleWatchlist({{ $country->id }}, '{{ addslashes($country->name) }}')"
                                class="watchlist-btn flex-shrink-0 text-xl ml-1 opacity-40 hover:opacity-100 transition"
                                id="wbtn-{{ $country->id }}" title="Add to watchlist">
                                ☆
                            </button>
                        </div>

                        {{-- Risk Badge --}}
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs px-2.5 py-1 rounded-full border font-semibold {{ $color['badge'] }}">
                                {{ $level }}
                            </span>
                            @if($score)
                                <span class="text-xs text-slate-400">Score: <span class="font-bold text-white">{{ number_format($score, 0) }}</span></span>
                            @endif
                        </div>

                        {{-- Stats Grid --}}
                        <div class="grid grid-cols-2 gap-1.5 text-xs">
                            <div class="bg-slate-800/60 rounded-lg p-2">
                                <div class="text-slate-500">Inflation</div>
                                <div class="font-bold mt-0.5">{{ $eco?->inflation ? number_format($eco->inflation, 1).'%' : '—' }}</div>
                            </div>
                            <div class="bg-slate-800/60 rounded-lg p-2">
                                <div class="text-slate-500">Exports</div>
                                <div class="font-bold mt-0.5">{{ $eco?->exports ? '$' . ($eco->exports/1e9) . 'B' : '—' }}</div>
                            </div>
                        </div>

                        {{-- Weather if available --}}
                        @if($wx)
                        <div class="mt-2 text-xs text-slate-500 flex items-center gap-1">
                            <i class="bi bi-thermometer-half"></i>
                            {{ number_format($wx->temperature_avg ?? 0, 1) }}°C
                            @if($wx->disaster_risk)
                                <span class="ml-1 text-orange-400">⚠ {{ $wx->disaster_risk }}</span>
                            @endif
                        </div>
                        @endif

                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
// ── State ─────────────────────────────────────────────────────────────────────
let authToken     = localStorage.getItem('gscr_token') || null;
let watchlistIds  = new Set(JSON.parse(localStorage.getItem('gscr_watchlist') || '[]'));
const API         = '/api';

// ── Init ──────────────────────────────────────────────────────────────────────
function init() {
    updateAuthUI();
    renderLocalWatchlist();
    syncButtonStates();
    if (authToken) fetchServerWatchlist();
}

function updateAuthUI() {
    const loggedIn = !!authToken;
    document.getElementById('authPanel').classList.add('hidden');
    document.getElementById('statusBar').classList.toggle('hidden', !loggedIn);
    const btn = document.getElementById('authToggleBtn');
    if (loggedIn) {
        btn.textContent = '✅ Synced';
        btn.disabled    = true;
        btn.classList.remove('bg-blue-600','hover:bg-blue-700');
        btn.classList.add('bg-green-700','cursor-default');
    } else {
        btn.textContent = '🔐 Login to Save';
        btn.disabled    = false;
        btn.classList.add('bg-blue-600','hover:bg-blue-700');
        btn.classList.remove('bg-green-700','cursor-default');
    }
}

function toggleAuthPanel() {
    if (authToken) return;
    document.getElementById('authPanel').classList.toggle('hidden');
}

// ── Login ─────────────────────────────────────────────────────────────────────
async function doLogin() {
    const email    = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    const errEl    = document.getElementById('loginError');
    try {
        const res  = await fetch(`${API}/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Login failed');
        authToken = data.token;
        localStorage.setItem('gscr_token', authToken);
        errEl.classList.add('hidden');
        updateAuthUI();
        fetchServerWatchlist();
    } catch(e) {
        errEl.textContent = e.message;
        errEl.classList.remove('hidden');
    }
}

// ── Logout ────────────────────────────────────────────────────────────────────
async function doLogout() {
    try {
        await fetch(`${API}/logout`, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
        });
    } catch(_) {}
    authToken = null;
    localStorage.removeItem('gscr_token');
    updateAuthUI();
}

// ── Server Watchlist ──────────────────────────────────────────────────────────
async function fetchServerWatchlist() {
    try {
        const res  = await fetch(`${API}/watchlist`, {
            headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
        });
        const data = await res.json();
        watchlistIds = new Set(data.watchlist.map(w => w.country_id));
        localStorage.setItem('gscr_watchlist', JSON.stringify([...watchlistIds]));
        renderLocalWatchlist();
        syncButtonStates();
    } catch(_) {}
}

// ── Toggle Watchlist ──────────────────────────────────────────────────────────
async function toggleWatchlist(countryId, countryName) {
    const inList = watchlistIds.has(countryId);
    if (authToken) {
        try {
            if (inList) {
                const res  = await fetch(`${API}/watchlist`, {
                    headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
                });
                const data = await res.json();
                const item = data.watchlist.find(w => w.country_id == countryId);
                if (item) {
                    await fetch(`${API}/watchlist/${item.id}`, {
                        method: 'DELETE',
                        headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
                    });
                }
                watchlistIds.delete(countryId);
            } else {
                await fetch(`${API}/watchlist`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ country_id: countryId })
                });
                watchlistIds.add(countryId);
            }
        } catch(e) { console.error(e); }
    } else {
        // Local-only — no login needed to use watchlist
        inList ? watchlistIds.delete(countryId) : watchlistIds.add(countryId);
    }
    localStorage.setItem('gscr_watchlist', JSON.stringify([...watchlistIds]));
    renderLocalWatchlist();
    syncButtonStates();
}

// ── Render Watchlist Panel ────────────────────────────────────────────────────
function renderLocalWatchlist() {
    const container = document.getElementById('watchlistContainer');
    const empty     = document.getElementById('watchlistEmpty');
    container.querySelectorAll('.wl-item').forEach(el => el.remove());

    if (watchlistIds.size === 0) {
        empty.style.display = '';
        return;
    }
    empty.style.display = 'none';

    watchlistIds.forEach(id => {
        const card = document.querySelector(`.watchable-card[data-id="${id}"]`);
        if (!card) return;
        const name = card.querySelector('.font-bold.text-sm')?.textContent?.trim() || '';
        const flag = card.querySelector('img')?.src || '';
        const risk = card.dataset.risk || '';
        const riskColor = risk === 'high' ? 'text-red-400' : risk === 'medium' ? 'text-yellow-400' : 'text-green-400';
        const div  = document.createElement('div');
        div.className = 'wl-item flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl bg-slate-800/50 hover:bg-slate-700/40 transition';
        div.innerHTML = `
            <div class="flex items-center gap-2 min-w-0">
                ${flag ? `<img src="${flag}" class="w-7 h-5 object-cover rounded flex-shrink-0">` : ''}
                <span class="text-sm font-semibold truncate">${name}</span>
                <span class="text-xs ${riskColor} capitalize">${risk}</span>
            </div>
            <button onclick="toggleWatchlist(${id}, '${name.replace(/'/g,"\\'")}')" class="text-yellow-400 hover:text-red-400 transition text-sm flex-shrink-0">✕</button>
        `;
        container.appendChild(div);
    });
}

function syncButtonStates() {
    document.querySelectorAll('.watchlist-btn').forEach(btn => {
        const id = parseInt(btn.id.replace('wbtn-',''));
        const inList = watchlistIds.has(id);
        btn.textContent = inList ? '★' : '☆';
        btn.classList.toggle('text-yellow-400', inList);
        btn.classList.toggle('opacity-100', inList);
        btn.classList.toggle('opacity-40', !inList);
    });
}

// ── Search & Filter ───────────────────────────────────────────────────────────
function applyFilter() {
    const q    = document.getElementById('watchSearchInput').value.toLowerCase();
    const risk = document.getElementById('riskFilter').value;
    let visible = 0;
    document.querySelectorAll('.watchable-card').forEach(card => {
        const nameMatch = card.dataset.name.includes(q);
        const riskMatch = !risk || card.dataset.risk === risk;
        const show = nameMatch && riskMatch;
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('resultsCount').textContent = `Showing ${visible} countries`;
}

document.getElementById('watchSearchInput').addEventListener('input', applyFilter);

init();
</script>
@endpush
