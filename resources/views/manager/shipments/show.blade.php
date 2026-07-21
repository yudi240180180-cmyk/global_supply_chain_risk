@extends('layouts.manager')

@section('title', 'Shipment Details')
@section('page-title')
    Shipment DSS Details: {{ $shipment->shipment_code }}
@endsection
@section('page-desc', 'Full decision support risk assessment, route rendering, and recommendation details.')

@section('content')
<div class="space-y-6">

    {{-- Header back bar --}}
    <div class="flex justify-between items-center">
        <a href="{{ route('manager.shipments.index') }}" class="btn-secondary py-2 px-3 text-xs">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
        <div class="flex gap-2">
            <a href="{{ route('manager.shipments.track', $shipment) }}" class="btn-primary">
                <i class="bi bi-geo-alt"></i> Track Status & Timeline
            </a>
        </div>
    </div>

    {{-- DSS Risk Summary Banner --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Risk Gauge Card --}}
        <div class="glass p-6 rounded-2xl border border-{{ $shipment->risk_color }}-500/30 flex flex-col justify-between">
            <div>
                <span class="text-xs uppercase tracking-wider text-slate-400 font-semibold">DSS Overall Risk Index</span>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-5xl font-black text-white">{{ round($shipment->overall_risk_score) }}</span>
                    <span class="text-sm font-semibold text-slate-500">/ 100</span>
                </div>
                <div class="mt-2">
                    <span class="px-3 py-1 rounded text-xs font-bold badge-{{ strtolower($shipment->risk_level) }}">
                        {{ $shipment->risk_level }} RISK LEVEL
                    </span>
                </div>
            </div>

            <div class="mt-6 space-y-2">
                <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 via-amber-500 to-rose-500" style="width: {{ $shipment->overall_risk_score }}%"></div>
                </div>
                <div class="flex justify-between text-[10px] text-slate-500">
                    <span>Low (0)</span>
                    <span>Medium (35)</span>
                    <span>High (65)</span>
                </div>
            </div>
        </div>

        {{-- AI Recommendation Card --}}
        <div class="lg:col-span-2 glass p-6 rounded-2xl border border-white/5 flex flex-col justify-between bg-gradient-to-br from-violet-950/20 to-indigo-950/20">
            <div>
                <span class="text-xs uppercase tracking-wider text-violet-400 font-semibold"><i class="bi bi-cpu mr-1"></i>Decision Engine Recommendation</span>
                <h3 class="text-xl font-bold mt-2 text-white">
                    @if($shipment->risk_level === 'High')
                        ⚠️ Action Required: Hold & Delay Shipment
                    @elseif($shipment->risk_level === 'Medium')
                        🟡 Alert: Proceed with Enhanced Monitoring
                    @else
                        ✅ Approved: Direct Clearance
                    @endif
                </h3>
                <p class="text-slate-300 text-sm mt-3 leading-relaxed">
                    {{ $shipment->recommendation }}
                </p>
            </div>

            @if($shipment->recommendations->first() && $shipment->recommendations->first()->risk_factors)
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($shipment->recommendations->first()->risk_factors as $factor)
                    <span class="bg-white/5 border border-white/10 rounded-xl px-3 py-1 text-xs text-slate-300 flex items-center gap-1.5">
                        <span>{{ $factor['icon'] ?? '⚠️' }}</span>
                        <span>{{ $factor['label'] }}</span>
                    </span>
                @endforeach
            </div>
            @endif
        </div>

    </div>

    {{-- Detail Risk Breakdown --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        @php
            $breakdown = [
                ['label' => 'Weather / Storm', 'score' => $shipment->weather_risk, 'icon' => 'bi-cloud-lightning-rain', 'color' => 'sky'],
                ['label' => 'Currency Exchange', 'score' => $shipment->currency_risk, 'icon' => 'bi-currency-exchange', 'color' => 'indigo'],
                ['label' => 'Economic Index', 'score' => $shipment->economic_risk, 'icon' => 'bi-graph-down', 'color' => 'amber'],
                ['label' => 'News Intelligence', 'score' => $shipment->news_risk, 'icon' => 'bi-newspaper', 'color' => 'rose'],
                ['label' => 'Port Congestion', 'score' => $shipment->port_congestion_risk, 'icon' => 'bi-anchor', 'color' => 'teal'],
            ];
        @endphp
        @foreach($breakdown as $item)
        <div class="glass p-5 rounded-2xl border border-white/5 flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider leading-none">{{ $item['label'] }}</span>
                <i class="bi {{ $item['icon'] }} text-slate-500"></i>
            </div>
            <div class="mt-4">
                <span class="text-2xl font-black text-white">{{ round($item['score']) }}</span>
                <span class="text-xs text-slate-500">/ 100</span>
                <div class="w-full h-1 bg-slate-800 rounded-full mt-2 overflow-hidden">
                    <div class="h-full bg-{{ $item['color'] }}-400" style="width: {{ $item['score'] }}%"></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Route Rendering & Shipment Profile --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Column 1 & 2: Map and Log --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Leaflet Map --}}
            <div class="glass p-6 rounded-2xl">
                <h3 class="font-bold text-white text-base mb-4"><i class="bi bi-map mr-2 text-violet-400"></i>Active Shipment Transit Route</h3>
                <div id="routeMap" style="height: 380px;" class="rounded-xl overflow-hidden border border-slate-800"></div>
            </div>

            {{-- Status Log Timeline --}}
            <div class="glass p-6 rounded-2xl">
                <h3 class="font-bold text-white text-base mb-4"><i class="bi bi-list-task mr-2 text-violet-400"></i>Audit Trail & Tracking Logs</h3>
                <div class="relative pl-6 border-l-2 border-slate-800 space-y-6">
                    @forelse($shipment->statusLogs as $log)
                    <div class="relative">
                        {{-- Bullet marker --}}
                        <div class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full border-2 border-slate-900 bg-violet-500 shadow-[0_0_8px_rgba(139,92,246,0.6)]"></div>
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="font-semibold text-white text-sm bg-white/5 border border-white/10 px-2 py-0.5 rounded">
                                    {{ $log->status }}
                                </span>
                                <p class="text-xs text-slate-400 mt-2">{{ $log->notes }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-slate-500 block">{{ $log->logged_at->format('d M Y H:i') }}</span>
                                <span class="text-[10px] text-slate-500">By: {{ $log->loggedBy?->name ?? 'System' }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-slate-500 text-xs">No status logs recorded.</p>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Column 3: Logistics details & Costs --}}
        <div class="space-y-6">

            {{-- Shipment Profile --}}
            <div class="glass p-6 rounded-2xl space-y-4">
                <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Shipment Profile</h3>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-slate-500 text-xs font-semibold block uppercase">Cargo Name</span>
                        <span class="font-medium text-white">{{ $shipment->cargo_name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 text-xs font-semibold block uppercase">Total Weight</span>
                        <span class="font-medium text-white">{{ number_format($shipment->cargo_weight, 1) }} Tons</span>
                    </div>
                    <div>
                        <span class="text-slate-500 text-xs font-semibold block uppercase">Containers</span>
                        <span class="font-medium text-white">{{ $shipment->container_count }}x {{ $shipment->container_type }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 text-xs font-semibold block uppercase">Distance / Days</span>
                        <span class="font-medium text-white">{{ number_format($shipment->distance_km) }} km / {{ $shipment->estimated_days }} Days</span>
                    </div>
                    <div>
                        <span class="text-slate-500 text-xs font-semibold block uppercase">Supplier Entity</span>
                        <span class="font-medium text-white">{{ $shipment->supplier->company_name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 text-xs font-semibold block uppercase">Origin Port</span>
                        <span class="font-medium text-white">{{ $shipment->originPort->name }} ({{ $shipment->originPort->country->iso3 ?? $shipment->originPort->country->name }})</span>
                    </div>
                    <div>
                        <span class="text-slate-500 text-xs font-semibold block uppercase">Est. Departure</span>
                        <span class="font-medium text-white">{{ $shipment->estimated_departure ? $shipment->estimated_departure->format('d M Y') : 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 text-xs font-semibold block uppercase">Est. Arrival</span>
                        <span class="font-medium text-white text-amber-400 font-semibold">{{ $shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            {{-- Shipping Cost Estimator breakdown --}}
            <div class="glass p-6 rounded-2xl space-y-4">
                <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Shipping Cost Estimate</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Estimated Total Cost</span>
                        <span class="font-bold text-emerald-400">${{ number_format($shipment->shipping_cost, 2) }}</span>
                    </div>
                    <div class="text-[10px] text-slate-500 italic">
                        *Includes base ocean freight, standard cargo handling, and regional port tariffs.
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const originName = "{{ $shipment->originPort->name }}";
        const originLat = parseFloat("{{ $shipment->originPort->latitude }}");
        const originLng = parseFloat("{{ $shipment->originPort->longitude }}");

        const destName = "{{ $shipment->destinationPort->name }}";
        const destLat = parseFloat("{{ $shipment->destinationPort->latitude }}");
        const destLng = parseFloat("{{ $shipment->destinationPort->longitude }}");

        // Map setup
        const map = L.map('routeMap').setView([ (originLat + destLat) / 2, (originLng + destLng) / 2 ], 3);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; CartoDB'
        }).addTo(map);

        // Custom Icons
        const originIcon = L.divIcon({
            html: '<div style="background-color: #6366f1; width: 14px; height: 14px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 10px rgba(99,102,241,0.8)"></div>',
            className: ''
        });

        const destIcon = L.divIcon({
            html: '<div style="background-color: #22d3ee; width: 14px; height: 14px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 10px rgba(34,211,238,0.8)"></div>',
            className: ''
        });

        L.marker([originLat, originLng], {icon: originIcon}).addTo(map).bindPopup("<b>Origin Port:</b> " + originName);
        L.marker([destLat, destLng], {icon: destIcon}).addTo(map).bindPopup("<b>Destination Port:</b> " + destName);

        // Draw Route Line
        const latlngs = [
            [originLat, originLng],
            [destLat, destLng]
        ];
        const polyline = L.polyline(latlngs, {
            color: '#8b5cf6',
            weight: 3,
            dashArray: '5, 8'
        }).addTo(map);

        map.fitBounds(polyline.getBounds(), {padding: [50, 50]});
    });
</script>
@endpush
