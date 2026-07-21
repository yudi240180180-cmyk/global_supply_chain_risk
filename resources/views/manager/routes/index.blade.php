@extends('layouts.manager')

@section('title', 'Route Recommendations')
@section('page-title', 'Route Intelligence Recommendation')
@section('page-desc', 'Compare multi-leg transit routes, intermediate hubs, distance metrics, and cumulative risk indices.')

@section('content')
<div class="space-y-6">

    {{-- Input Form --}}
    <div class="glass p-6 rounded-2xl border border-white/5 bg-gradient-to-r from-slate-950/20 to-indigo-950/10">
        <h3 class="font-bold text-white text-base mb-4"><i class="bi bi-search mr-2 text-violet-400"></i>Find Optimized Transit Paths</h3>
        <form method="POST" action="{{ route('manager.routes.recommend') }}" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            @csrf
            <div>
                <label class="form-label" for="origin_port_id">Origin Port</label>
                <select name="origin_port_id" id="origin_port_id" class="form-input" required>
                    <option value="">Select Origin Port</option>
                    @foreach($ports as $port)
                        <option value="{{ $port->id }}" {{ isset($origin) && $origin->id == $port->id ? 'selected' : '' }}>
                            {{ $port->name }} ({{ $port->country->name }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="destination_port_id">Destination Port</label>
                <select name="destination_port_id" id="destination_port_id" class="form-input" required>
                    <option value="">Select Destination Port</option>
                    @foreach($ports as $port)
                        <option value="{{ $port->id }}" {{ isset($destination) && $destination->id == $port->id ? 'selected' : '' }}>
                            {{ $port->name }} ({{ $port->country->name }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary py-3 justify-center">
                <i class="bi bi-cpu"></i> Analyze Routes & Evaluate
            </button>
        </form>
    </div>

    @if(isset($routes))
    {{-- Results Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Map View --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="glass p-6 rounded-2xl">
                <h3 class="font-bold text-white text-base mb-4"><i class="bi bi-map mr-2 text-violet-400"></i>Geographical Waypoint Layout</h3>
                <div id="recommendationMap" style="height: 420px;" class="rounded-xl overflow-hidden border border-slate-800"></div>
            </div>
        </div>

        {{-- Recommendations Side Cards --}}
        <div class="space-y-4">
            <h3 class="font-bold text-white text-base px-1">Evaluation Results</h3>

            @foreach($routes as $index => $route)
            <div class="glass p-5 rounded-2xl border border-white/5 hover:border-violet-500/30 transition cursor-pointer select-card {{ $index === 0 ? 'border-violet-500 bg-violet-950/10' : '' }}"
                 onclick="renderSelectedRoute({{ $index }})">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-violet-400">{{ $route['label'] }}</span>
                        <h4 class="font-bold text-white mt-1">{{ count($route['stops']) - 2 }} Transit Waypoints</h4>
                    </div>
                    <span class="px-2 py-0.5 rounded text-xs font-bold badge-{{ strtolower($route['risk_level']) }}">
                        Risk: {{ $route['risk_score'] }}
                    </span>
                </div>

                <div class="mt-4 space-y-2 text-xs text-slate-400">
                    <div class="flex justify-between">
                        <span>Total Distance:</span>
                        <span class="text-white font-medium">{{ number_format($route['total_distance']) }} km</span>
                    </div>
                    <div class="flex justify-between">
                        <span>ETA Transit:</span>
                        <span class="text-white font-medium">{{ $route['estimated_days'] }} Days</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Est. Ocean Freight:</span>
                        <span class="text-emerald-400 font-medium">${{ number_format($route['est_cost_usd']) }}</span>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-800 space-y-2">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Transit Leg Details</span>
                    <div class="flex flex-wrap items-center gap-1.5 text-xs text-slate-300">
                        @foreach($route['stops'] as $stopIndex => $stop)
                            <span>{{ $stop['name'] }}</span>
                            @if(!$loop->last)
                                <i class="bi bi-chevron-right text-slate-600 text-[10px]"></i>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
    @endif

</div>
@endsection

@push('scripts')
@if(isset($routes))
<script>
    const routesData = @json($routes);
    let map = null;
    let currentLayers = [];

    document.addEventListener("DOMContentLoaded", function() {
        // Map setup
        map = L.map('recommendationMap');

        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; CartoDB'
        }).addTo(map);

        // Render first route by default
        renderSelectedRoute(0);
    });

    function renderSelectedRoute(index) {
        const route = routesData[index];

        // Clear previous layers
        currentLayers.forEach(layer => map.removeLayer(layer));
        currentLayers = [];

        // Update cards visual
        const cards = document.querySelectorAll('.select-card');
        cards.forEach((card, idx) => {
            if (idx === index) {
                card.classList.add('border-violet-500', 'bg-violet-950/10');
            } else {
                card.classList.remove('border-violet-500', 'bg-violet-950/10');
            }
        });

        // Add Markers and Line
        const latlngs = [];

        // Colors
        const colors = ['#6366f1', '#a855f7', '#22d3ee', '#10b981'];

        route.stops.forEach((stop, stopIdx) => {
            const isOrigin = stopIdx === 0;
            const isDest = stopIdx === route.stops.length - 1;

            const iconColor = isOrigin ? '#6366f1' : (isDest ? '#22d3ee' : '#a855f7');
            const customIcon = L.divIcon({
                html: `<div style="background-color: ${iconColor}; width: 14px; height: 14px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 10px ${iconColor}"></div>`,
                className: ''
            });

            const marker = L.marker([stop.lat, stop.lng], {icon: customIcon})
                .addTo(map)
                .bindPopup(`<b>${isOrigin ? 'Origin' : (isDest ? 'Destination' : 'Transit Hub')}:</b> ${stop.name}`);

            currentLayers.push(marker);
            latlngs.push([stop.lat, stop.lng]);
        });

        const polyline = L.polyline(latlngs, {
            color: '#8b5cf6',
            weight: 4,
            dashArray: route.type === 'direct' ? '0' : '4, 6'
        }).addTo(map);

        currentLayers.push(polyline);

        map.fitBounds(polyline.getBounds(), {padding: [50, 50]});
    }
</script>
@endif
@endpush
