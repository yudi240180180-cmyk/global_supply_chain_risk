@extends('layouts.app')

@section('title', 'Global Weather Monitoring')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold">🌤️ Global Weather Monitoring</h1>
            <p class="text-slate-400 mt-1">Real-time weather conditions affecting supply chain logistics</p>
        </div>
        <div class="flex gap-4">
            <div class="glass rounded-xl px-5 py-3 text-center">
                <div class="text-2xl font-bold text-red-400">{{ $highStorm }}</div>
                <div class="text-xs text-slate-400">High Storm Risk</div>
            </div>
            <div class="glass rounded-xl px-5 py-3 text-center">
                <div class="text-2xl font-bold">{{ $countries->count() }}</div>
                <div class="text-xs text-slate-400">Countries Tracked</div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-3 gap-5">
        <div class="glass rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl">🌡️</span>
                <span class="text-slate-400 text-sm">Avg Temperature</span>
            </div>
            <div class="text-4xl font-black">{{ number_format($avgTemp ?? 0, 1) }}°C</div>
        </div>
        <div class="glass rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl">💨</span>
                <span class="text-slate-400 text-sm">Avg Wind Speed</span>
            </div>
            <div class="text-4xl font-black">{{ number_format($avgWind ?? 0, 1) }} km/h</div>
        </div>
        <div class="glass rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl">⛈️</span>
                <span class="text-slate-400 text-sm">Avg Storm Risk</span>
            </div>
            <div class="text-4xl font-black {{ ($avgStormRisk ?? 0) > 50 ? 'text-red-400' : (($avgStormRisk ?? 0) > 25 ? 'text-yellow-400' : 'text-green-400') }}">
                {{ number_format($avgStormRisk ?? 0, 1) }}/100
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">

        {{-- Map --}}
        <div class="glass rounded-2xl p-6 col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">🗺️ Weather Map</h2>
                <div class="flex gap-4 text-xs text-slate-400">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>High Risk</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-yellow-500 inline-block"></span>Medium</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>Low</span>
                </div>
            </div>
            <div id="weatherMap" style="height:460px; border-radius:16px;"></div>
        </div>

        {{-- Alert Panel --}}
        <div class="glass rounded-2xl p-5 flex flex-col">
            <h2 class="text-lg font-bold mb-4">🚨 Storm Alert Ranking</h2>
            <div class="space-y-2 overflow-y-auto scrollbar flex-1" style="max-height:460px">
                @foreach($countries->sortByDesc(fn($c) => optional($c->latestWeather)->storm_risk ?? 0)->take(25) as $country)
                    @php
                        $w     = $country->latestWeather;
                        $storm = $w?->storm_risk ?? 0;
                        $bar   = $storm >= 60 ? 'bg-red-500' : ($storm >= 30 ? 'bg-yellow-500' : 'bg-green-500');
                        $txt   = $storm >= 60 ? 'text-red-400' : ($storm >= 30 ? 'text-yellow-400' : 'text-green-400');
                    @endphp
                    <div class="flex items-center gap-2 px-2 py-2 rounded-xl hover:bg-slate-800/40 transition">
                        @if($country->flag_url)
                            <img src="{{ $country->flag_url }}" class="w-8 h-5 object-cover rounded flex-shrink-0">
                        @else
                            <div class="w-8 h-5 bg-slate-700 rounded flex-shrink-0"></div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium truncate">{{ $country->name }}</div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <div class="flex-1 bg-slate-700 rounded-full h-1">
                                    <div class="h-1 rounded-full {{ $bar }}" style="width:{{ min($storm,100) }}%"></div>
                                </div>
                                <span class="text-xs {{ $txt }} w-7 text-right">{{ round($storm) }}</span>
                            </div>
                        </div>
                        <div class="text-xs text-slate-500 text-right flex-shrink-0">
                            <div>{{ $w?->temperature !== null ? round($w->temperature).'°' : '—' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- Data Table --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-slate-700/50">
            <h2 class="text-xl font-bold">All Countries Weather Data</h2>
            <input id="weatherSearch" type="text" placeholder="Search country…"
                class="bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-blue-500 w-56">
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-800/60">
                    <tr class="text-left text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3">Country</th>
                        <th class="px-5 py-3 text-right">Temp (°C)</th>
                        <th class="px-5 py-3 text-right">Rainfall (mm)</th>
                        <th class="px-5 py-3 text-right">Wind (km/h)</th>
                        <th class="px-5 py-3">Storm Risk</th>
                        <th class="px-5 py-3">Condition</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40" id="weatherBody">
                    @foreach($countries->sortByDesc(fn($c) => optional($c->latestWeather)->storm_risk ?? 0) as $c)
                        @php $w = $c->latestWeather; $storm = $w?->storm_risk ?? 0; @endphp
                        <tr class="weather-row hover:bg-slate-800/30 transition" data-name="{{ strtolower($c->name) }}">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    @if($c->flag_url)
                                        <img src="{{ $c->flag_url }}" class="w-7 h-5 object-cover rounded">
                                    @endif
                                    <a href="{{ route('countries.show', $c->id) }}" class="hover:text-blue-400 font-semibold">{{ $c->name }}</a>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right">{{ $w?->temperature !== null ? number_format($w->temperature,1) : '—' }}</td>
                            <td class="px-5 py-3 text-right">{{ $w?->rainfall !== null ? number_format($w->rainfall,1) : '—' }}</td>
                            <td class="px-5 py-3 text-right">{{ $w?->wind_speed !== null ? number_format($w->wind_speed,1) : '—' }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-24 bg-slate-700 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ $storm>=60?'bg-red-500':($storm>=30?'bg-yellow-500':'bg-green-500') }}"
                                             style="width:{{ min($storm,100) }}%"></div>
                                    </div>
                                    <span class="text-xs w-6">{{ round($storm) }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-slate-300 text-sm">{{ $w?->weather_condition ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// Execute immediately or wait for DOMContentLoaded
function initWeatherMap() {
    console.log('Weather: Initializing weather map...');
    
// ── Weather Map ──────────────────────────────────────────────────────────────
const weatherMap = L.map('weatherMap', { worldCopyJump: true }).setView([20, 0], 2);
setTimeout(() => { weatherMap.invalidateSize(); }, 300);
L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 19
}).addTo(weatherMap);

const wData = {!! Js::from($countries->filter(fn($c) => $c->latitude && $c->longitude)->map(fn($c) => [
    'name' => $c->name, 'lat'  => (float)$c->latitude,  'lng'  => (float)$c->longitude,
    'temp' => $c->latestWeather?->temperature !== null ? (float)$c->latestWeather->temperature : null,
    'wind' => $c->latestWeather?->wind_speed !== null ? (float)$c->latestWeather->wind_speed : null,
    'rain' => $c->latestWeather?->rainfall !== null ? (float)$c->latestWeather->rainfall : null,
    'storm'=> (float)($c->latestWeather?->storm_risk ?? 0),
    'cond' => $c->latestWeather?->weather_condition ?? 'N/A',
])->values()) !!};

wData.forEach(function(c) {
    const color = c.storm >= 60 ? '#ef4444' : (c.storm >= 30 ? '#f59e0b' : '#22c55e');
    L.circleMarker([c.lat, c.lng], {
        radius: 7 + (c.storm / 18), fillColor: color,
        color: '#0f172a', weight: 1.5, opacity: 1, fillOpacity: 0.8
    }).addTo(weatherMap).bindPopup(
        `<div style="font-family:Arial;min-width:170px">
            <b style="font-size:13px">${c.name}</b><br>
            🌡️ ${c.temp !== null ? c.temp.toFixed(1)+'°C' : '—'} &nbsp;
            💨 ${c.wind !== null ? c.wind.toFixed(0)+' km/h' : '—'}<br>
            🌧️ ${c.rain !== null ? c.rain.toFixed(1)+' mm' : '—'}<br>
            ⛈️ Storm Risk: <b style="color:${color}">${c.storm.toFixed(0)}/100</b><br>
            ☁️ ${c.cond}
        </div>`
    );
});

// ── Table Search ─────────────────────────────────────────────────────────────
document.getElementById('weatherSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.weather-row').forEach(row => {
        row.style.display = row.dataset.name.includes(q) ? '' : 'none';
    });
});

} // End initWeatherMap

// Check if DOM is already loaded or wait for it
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWeatherMap);
} else {
    // DOM already loaded, execute immediately
    initWeatherMap();
}
</script>
@endpush
