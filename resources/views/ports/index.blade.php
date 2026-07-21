@extends('layouts.app')

@section('title', 'Port Location Dashboard')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold">🚢 Port Location Dashboard</h1>
            <p class="text-slate-400 mt-1">Interactive global port map — search, filter, explore logistics hubs worldwide</p>
        </div>
        <div class="flex gap-4">
            <div class="glass rounded-xl px-5 py-3 text-center">
                <div class="text-2xl font-bold">{{ $totalPorts }}</div>
                <div class="text-xs text-slate-400">Total Ports</div>
            </div>
            <div class="glass rounded-xl px-5 py-3 text-center">
                <div class="text-2xl font-bold">{{ $totalCountries }}</div>
                <div class="text-xs text-slate-400">Countries</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="glass rounded-2xl p-5">
        <div class="flex gap-4 flex-wrap items-end">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs text-slate-400 mb-1">Search Port / LOCODE</label>
                <input id="portSearch" type="text" placeholder="e.g. Singapore, SGSIN…"
                    class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div class="min-w-[180px]">
                <label class="block text-xs text-slate-400 mb-1">Filter by Country</label>
                <select id="countryFilter" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                    <option value="">All Countries</option>
                    @foreach($countries as $c)
                        <option value="{{ strtolower($c->name) }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <button onclick="clearPortFilters()" class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 transition text-sm">
                Clear
            </button>
            <div class="ml-auto text-sm text-slate-400">
                Showing <span id="visibleCount">{{ $ports->count() }}</span> of {{ $ports->count() }} ports
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">

        {{-- Map --}}
        <div class="col-span-2 glass rounded-2xl p-5">
            <div id="portMap" style="height:560px; border-radius:14px;"></div>
        </div>

        {{-- Port List --}}
        <div class="glass rounded-2xl p-5 flex flex-col">
            <h2 class="text-lg font-bold mb-3">Port List</h2>
            <div class="overflow-y-auto scrollbar flex-1" style="max-height:560px" id="portListContainer">
                <div id="portList" class="space-y-1">
                    @foreach($ports as $port)
                        <div class="port-item flex items-center gap-2 px-3 py-2.5 rounded-xl hover:bg-slate-700/50 cursor-pointer transition"
                             data-name="{{ strtolower($port->name) }}"
                             data-locode="{{ strtolower($port->locode ?? '') }}"
                             data-country="{{ strtolower(optional($port->country)->name ?? '') }}"
                             data-lat="{{ $port->latitude }}"
                             data-lng="{{ $port->longitude }}"
                             data-id="{{ $port->id }}"
                             onclick="flyToPort({{ $port->latitude }}, {{ $port->longitude }}, '{{ addslashes($port->name) }}')">
                            <div class="w-2 h-2 rounded-full bg-blue-400 flex-shrink-0"></div>
                            <div class="min-w-0">
                                <div class="text-sm font-semibold truncate">{{ $port->name }}</div>
                                <div class="text-xs text-slate-500 truncate">
                                    {{ optional($port->country)->name ?? '—' }}
                                    @if($port->locode) · {{ $port->locode }} @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div id="noPortResults" class="hidden text-center py-10 text-slate-400 text-sm">
                    No ports match your search.
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
// ── Map Init ─────────────────────────────────────────────────────────────────
const portMap = L.map('portMap', { worldCopyJump: true }).setView([20, 0], 2);
setTimeout(() => { portMap.invalidateSize(); }, 300);
L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 19
}).addTo(portMap);

const markers = L.markerClusterGroup({ chunkedLoading: true, maxClusterRadius: 50 });

const portIcon = L.divIcon({
    className: '',
    html: '<div style="width:10px;height:10px;background:#3b82f6;border:2px solid #fff;border-radius:50%;box-shadow:0 0 6px rgba(59,130,246,0.8)"></div>',
    iconSize: [10, 10], iconAnchor: [5, 5],
});

const allPortData = {!! Js::from($ports->map(fn($p) => [
    'id'      => $p->id,
    'name'    => $p->name,
    'locode'  => $p->locode,
    'country' => optional($p->country)->name ?? '—',
    'lat'     => (float)$p->latitude,
    'lng'     => (float)$p->longitude,
    'type'    => $p->port_type ?? 'International',
    'status'  => $p->status ?? 'Operational',
    'out'     => (float)($p->outflows ?? 0),
])->values()) !!};

const layerMap = {};

allPortData.forEach(function(p) {
    const m = L.marker([p.lat, p.lng], { icon: portIcon })
        .bindPopup(`
            <div style="min-width:180px;font-family:Arial">
                <b style="font-size:13px">🚢 ${p.name}</b><br>
                <span style="color:#64748b;font-size:11px">${p.country}</span><br><br>
                <b>LOCODE:</b> ${p.locode || 'N/A'}<br>
                <b>Type:</b> ${p.type}<br>
                <b>Status:</b> ${p.status}<br>
                <b>Outflows:</b> ${p.out.toLocaleString()}<br><br>
                <a href="/ports/${p.id}" style="color:#3b82f6;font-size:12px">View Details →</a>
            </div>
        `);
    markers.addLayer(m);
    layerMap[p.id] = m;
});

portMap.addLayer(markers);

// ── Fly to port ──────────────────────────────────────────────────────────────
function flyToPort(lat, lng, name) {
    portMap.flyTo([lat, lng], 10, { duration: 1.5 });
}

// ── Filters ──────────────────────────────────────────────────────────────────
const portSearchEl   = document.getElementById('portSearch');
const countryFilterEl = document.getElementById('countryFilter');
const portListEl     = document.getElementById('portList');
const noPortResults  = document.getElementById('noPortResults');
const visibleCount   = document.getElementById('visibleCount');

function filterPorts() {
    const q       = portSearchEl.value.toLowerCase().trim();
    const country = countryFilterEl.value.toLowerCase();
    const items   = portListEl.querySelectorAll('.port-item');
    let visible   = 0;

    items.forEach(item => {
        const matchQ = !q || item.dataset.name.includes(q) || item.dataset.locode.includes(q);
        const matchC = !country || item.dataset.country === country;
        item.style.display = (matchQ && matchC) ? '' : 'none';
        if (matchQ && matchC) visible++;
    });

    visibleCount.textContent = visible;
    noPortResults.classList.toggle('hidden', visible > 0);
}

function clearPortFilters() {
    portSearchEl.value    = '';
    countryFilterEl.value = '';
    filterPorts();
}

portSearchEl.addEventListener('input', filterPorts);
countryFilterEl.addEventListener('change', filterPorts);
</script>
@endpush
