@extends('layouts.app')

@section('title', 'Watchlist — Favorite Countries')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold">⭐ Favorite Monitoring List</h1>
            <p class="text-slate-400 mt-1">Save countries to watch — requires login to persist across sessions</p>
        </div>
        <button onclick="toggleAuthPanel()"
            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 transition font-semibold" id="authToggleBtn">
            🔐 Login to Save Watchlist
        </button>
    </div>

    {{-- Auth Panel --}}
    <div id="authPanel" class="hidden glass rounded-2xl p-6 max-w-md">
        <div id="loginForm">
            <h3 class="text-lg font-bold mb-4">Login</h3>
            <div class="space-y-3">
                <input id="loginEmail" type="email" placeholder="Email"
                    class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500">
                <input id="loginPassword" type="password" placeholder="Password"
                    class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500">
                <button onclick="doLogin()" class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 transition font-semibold">Login</button>
            </div>
            <div id="loginError" class="hidden mt-3 text-red-400 text-sm"></div>
        </div>
    </div>

    {{-- Logged-in status bar --}}
    <div id="statusBar" class="hidden glass rounded-2xl px-5 py-3 flex items-center justify-between">
        <span class="text-green-400 font-semibold">✅ Logged in — Your watchlist is synced</span>
        <button onclick="doLogout()" class="px-4 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 transition text-sm">Logout</button>
    </div>

    <div class="grid grid-cols-3 gap-6">

        {{-- My Watchlist --}}
        <div class="glass rounded-2xl p-5">
            <h2 class="text-lg font-bold mb-4">📋 My Watchlist</h2>
            <div id="watchlistContainer" class="space-y-2 min-h-[200px]">
                <div id="watchlistEmpty" class="text-center py-10 text-slate-400">
                    <div class="text-4xl mb-2">📭</div>
                    <div class="text-sm">No countries added yet.</div>
                    <div class="text-xs mt-1">Click the ⭐ on any country to add it.</div>
                </div>
            </div>
        </div>

        {{-- All Countries --}}
        <div class="col-span-2 space-y-4">
            <div class="glass rounded-2xl p-4">
                <input id="watchSearchInput" type="text" placeholder="Search countries…"
                    class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-3" id="allCountriesGrid">
                @foreach($countries as $country)
                    @php
                        $risk    = $country->latestRiskScore;
                        $eco     = $country->latestEconomics;
                        $level   = $risk?->risk_level ?? 'N/A';
                        $bdg = match($level) {
                            'High'   => 'text-red-400',
                            'Medium' => 'text-yellow-400',
                            'Low'    => 'text-green-400',
                            default  => 'text-slate-400',
                        };
                    @endphp
                    <div class="watchable-card glass rounded-xl p-4 hover:border-blue-500/40 border border-transparent transition"
                         data-id="{{ $country->id }}"
                         data-name="{{ strtolower($country->name) }}">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                @if($country->flag_url)
                                    <img src="{{ $country->flag_url }}" class="w-8 h-6 object-cover rounded flex-shrink-0">
                                @endif
                                <div class="min-w-0">
                                    <div class="font-semibold text-sm truncate">{{ $country->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $country->region }}</div>
                                </div>
                            </div>
                            <button onclick="toggleWatchlist({{ $country->id }}, '{{ $country->name }}')"
                                class="watchlist-btn flex-shrink-0 text-xl ml-2 opacity-40 hover:opacity-100 transition"
                                id="wbtn-{{ $country->id }}" title="Add to watchlist">
                                ☆
                            </button>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-slate-800/50 rounded-lg p-2">
                                <div class="text-slate-500">Risk</div>
                                <div class="font-bold {{ $bdg }}">{{ $level }}</div>
                            </div>
                            <div class="bg-slate-800/50 rounded-lg p-2">
                                <div class="text-slate-500">Inflation</div>
                                <div class="font-bold">{{ $eco?->inflation ? number_format($eco->inflation,1).'%' : '—' }}</div>
                            </div>
                        </div>
                        <div class="mt-2 flex gap-2">
                            <a href="{{ route('countries.show', $country->id) }}"
                               class="flex-1 text-center py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 transition text-xs">
                                Detail
                            </a>
                            <a href="{{ route('compare.index') }}?ids={{ $country->id }}"
                               class="px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 transition text-xs">
                                Compare
                            </a>
                        </div>
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
let authToken = localStorage.getItem('gscr_token') || null;
let watchlistIds = new Set(JSON.parse(localStorage.getItem('gscr_watchlist') || '[]'));
const API = '/api';

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
    document.getElementById('authToggleBtn').textContent = loggedIn ? '✅ Logged In' : '🔐 Login to Save Watchlist';
    document.getElementById('authToggleBtn').disabled = loggedIn;
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
        // local only
        inList ? watchlistIds.delete(countryId) : watchlistIds.add(countryId);
    }

    localStorage.setItem('gscr_watchlist', JSON.stringify([...watchlistIds]));
    renderLocalWatchlist();
    syncButtonStates();
}

// ── Render ────────────────────────────────────────────────────────────────────
function renderLocalWatchlist() {
    const container = document.getElementById('watchlistContainer');
    const empty     = document.getElementById('watchlistEmpty');

    if (watchlistIds.size === 0) {
        empty.style.display = '';
        // remove any previously rendered items
        container.querySelectorAll('.wl-item').forEach(el => el.remove());
        return;
    }

    empty.style.display = 'none';
    container.querySelectorAll('.wl-item').forEach(el => el.remove());

    watchlistIds.forEach(id => {
        const card = document.querySelector(`.watchable-card[data-id="${id}"]`);
        if (!card) return;
        const name = card.querySelector('.font-semibold').textContent;
        const flag = card.querySelector('img')?.src || '';
        const div  = document.createElement('div');
        div.className = 'wl-item flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl bg-slate-800/50 hover:bg-slate-700/40 transition';
        div.innerHTML = `
            <div class="flex items-center gap-2">
                ${flag ? `<img src="${flag}" class="w-7 h-5 object-cover rounded">` : ''}
                <a href="/countries/${id}" class="text-sm font-semibold hover:text-blue-400 transition">${name}</a>
            </div>
            <button onclick="toggleWatchlist(${id}, '${name}')" class="text-yellow-400 hover:text-red-400 transition text-sm">✕</button>
        `;
        container.appendChild(div);
    });
}

function syncButtonStates() {
    document.querySelectorAll('.watchlist-btn').forEach(btn => {
        const id = parseInt(btn.id.replace('wbtn-',''));
        btn.textContent = watchlistIds.has(id) ? '★' : '☆';
        btn.classList.toggle('text-yellow-400', watchlistIds.has(id));
        btn.classList.toggle('opacity-100', watchlistIds.has(id));
        btn.classList.toggle('opacity-40', !watchlistIds.has(id));
    });
}

// ── Search ────────────────────────────────────────────────────────────────────
document.getElementById('watchSearchInput').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.watchable-card').forEach(card => {
        card.style.display = card.dataset.name.includes(q) ? '' : 'none';
    });
});

init();
</script>
@endpush
