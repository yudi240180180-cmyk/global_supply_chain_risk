@extends('layouts.app')

@section('title','Dashboard')

@section('content')

<div class="space-y-8">
    <h1 class="text-4xl font-bold">Executive Dashboard</h1>
    
    <div class="grid grid-cols-4 gap-6">
        <div class="glass rounded-2xl p-6">
            <div class="text-slate-400">Countries</div>
            <div class="text-4xl font-bold mt-2">{{ $totalCountries }}</div>
        </div>
        <div class="glass rounded-2xl p-6">
            <div class="text-slate-400">Ports</div>
            <div class="text-4xl font-bold mt-2">{{ $totalPorts }}</div>
        </div>
        <div class="glass rounded-2xl p-6">
            <div class="text-slate-400">Economic Data</div>
            <div class="text-4xl font-bold mt-2">{{ $economicsCount }}</div>
        </div>
        <div class="glass rounded-2xl p-6">
            <div class="text-slate-400">News</div>
            <div class="text-4xl font-bold mt-2">{{ $newsCount }}</div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="glass rounded-2xl p-6 col-span-2">
            <h2 class="text-2xl font-bold mb-5">🌍 Global Ports Map</h2>
            <div id="world-map" style="height: 480px; width: 100%; background: #1e293b;"></div>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="text-xl font-bold mb-5">⚠️ High Risk Countries</h2>
            <div class="space-y-4">
                @foreach($highRiskCountries as $risk)
                <div class="flex justify-between">
                    <div>
                        <div class="font-semibold">{{ optional($risk->country)->name ?? '-' }}</div>
                        <div class="text-sm text-slate-400">{{ number_format($risk->total_score,2) }}</div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm bg-red-500">{{ $risk->risk_level }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6">
        <div class="glass rounded-2xl p-6">
            <h2 class="text-2xl font-bold mb-5">📊 Risk Distribution</h2>
            <canvas id="riskChart" style="height:300px;"></canvas>
        </div>
        
        <div class="glass rounded-2xl p-6">
            <h2 class="text-2xl font-bold mb-5">🚢 Top Ports</h2>
            @foreach($topPorts as $port)
            <div class="flex justify-between mb-3">
                <div>
                    <div class="font-semibold">{{ $port->name }}</div>
                    <div class="text-sm text-slate-400">{{ optional($port->country)->name }}</div>
                </div>
                <div class="font-bold">{{ number_format($port->outflows) }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
console.log('Script started');
console.log('Leaflet available:', typeof L !== 'undefined');
console.log('Chart available:', typeof Chart !== 'undefined');

// Risk Chart
const ctx = document.getElementById('riskChart');
if (ctx && typeof Chart !== 'undefined') {
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['High', 'Medium', 'Low'],
            datasets: [{
                data: [{{ $highCount }}, {{ $mediumCount }}, {{ $lowCount }}],
                backgroundColor: ['#ef4444', '#f59e0b', '#22c55e']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { color: 'white' } } }
        }
    });
}

// Leaflet Map
const mapEl = document.getElementById('world-map');
if (mapEl && typeof L !== 'undefined') {
    const map = L.map('world-map').setView([20, 0], 2);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 18
    }).addTo(map);
    
    const ports = @json($ports);
    console.log('Ports loaded:', ports.length);
    
    ports.forEach(port => {
        if (!port.latitude || !port.longitude) return;
        
        const lat = parseFloat(port.latitude);
        const lng = parseFloat(port.longitude);
        
        L.circleMarker([lat, lng], {
            radius: 6,
            color: '#3b82f6',
            fillColor: '#3b82f6',
            fillOpacity: 0.8
        }).addTo(map).bindPopup(`<b>${port.name}</b><br>${port.country ? port.country.name : ''}`);
    });
    
    setTimeout(() => map.invalidateSize(), 500);
} else {
    console.error('Map element or Leaflet not found');
}
</script>

@endsection
