@extends('layouts.app')

@section('title','Dashboard')

@section('content')

<div class="space-y-8">

    <div>
        <h1 class="text-4xl font-bold">
            Executive Dashboard
        </h1>

        <p class="text-slate-400 mt-2">
            Global Supply Chain Risk Monitoring Platform
        </p>
    </div>

    {{-- Statistic Cards --}}
    <div class="grid grid-cols-4 gap-6">

        <div class="glass rounded-2xl p-6 card-hover">
            <div class="text-slate-400">Countries</div>
            <div class="text-4xl font-bold mt-2">
                {{ $totalCountries }}
            </div>
        </div>

        <div class="glass rounded-2xl p-6 card-hover">
            <div class="text-slate-400">Ports</div>
            <div class="text-4xl font-bold mt-2">
                {{ $totalPorts }}
            </div>
        </div>

        <div class="glass rounded-2xl p-6 card-hover">
            <div class="text-slate-400">Economic Data</div>
            <div class="text-4xl font-bold mt-2">
                {{ $economicsCount }}
            </div>
        </div>

        <div class="glass rounded-2xl p-6 card-hover">
            <div class="text-slate-400">News</div>
            <div class="text-4xl font-bold mt-2">
                {{ $newsCount }}
            </div>
        </div>

    </div>

    {{-- Map + Risk --}}
    <div class="grid grid-cols-3 gap-6">

        <div class="glass rounded-2xl p-6 col-span-2">

            <div class="flex justify-between mb-5">

                <h2 class="text-2xl font-bold">
                    🌍 Global Ports Map
                </h2>

                <span class="text-slate-400">
                    {{ $totalPorts }} Ports
                </span>

            </div>

            <div id="world-map" style="height: 480px; width: 100%;"></div>

        </div>

        <div class="glass rounded-2xl p-6">

            <h2 class="text-xl font-bold mb-5">
                ⚠️ High Risk Countries
            </h2>

            <div class="space-y-4 overflow-y-auto scrollbar max-h-[500px]">

                @foreach($highRiskCountries as $risk)

                    <div class="flex justify-between">

                        <div>

                            <div class="font-semibold">
                                {{ optional($risk->country)->name ?? '-' }}
                            </div>

                            <div class="text-sm text-slate-400">
                                {{ number_format($risk->total_score,2) }}
                            </div>

                        </div>

                        <span
                            class="bg-red-500 px-3 py-1 rounded-full text-sm">

                            <span class="
px-3 py-1 rounded-full text-sm font-semibold
@if($risk->risk_level=='High')
    bg-red-500
@elseif($risk->risk_level=='Medium')
    bg-yellow-500
@else
    bg-green-500
@endif
">
    {{ $risk->risk_level }}
</span>

                        </span>

                    </div>

                @endforeach

            </div>

        </div>

    </div>

    {{-- News + Ports --}}
    <div class="grid grid-cols-2 gap-6 mt-6">

    <div class="glass rounded-2xl p-6">

    <h2 class="text-2xl font-bold mb-5">
        📊 Risk Distribution
    </h2>

    <div style="height:320px;">
        <canvas id="riskChart"></canvas>
    </div>

</div>

<div class="glass rounded-2xl p-6">

        <h2 class="text-2xl font-bold mb-5">

            🌍 Platform Summary

        </h2>

        <div class="space-y-5">

            <div class="flex justify-between">

                <span>Total Countries</span>

                <strong>{{ $totalCountries }}</strong>

            </div>

            <div class="flex justify-between">

                <span>Total Ports</span>

                <strong>{{ $totalPorts }}</strong>

            </div>

            <div class="flex justify-between">

                <span>Economic Data</span>

                <strong>{{ $economicsCount }}</strong>

            </div>

            <div class="flex justify-between">

                <span>News</span>

                <strong>{{ $newsCount }}</strong>

            </div>

            <div class="flex justify-between">

                <span>High Risk Countries</span>

                <strong>{{ $highRiskCountries->count() }}</strong>

            </div>

        </div>

    </div>

</div>
    <div class="grid grid-cols-2 gap-6">

        <div class="glass rounded-2xl p-6">

            <h2 class="text-2xl font-bold mb-5">
                📰 Latest News
            </h2>

            <div class="space-y-5">

                @foreach($latestNews as $news)

                    <div class="border-b border-slate-700 pb-3">

                        <div class="font-semibold">

                            {{ $news->title }}

                        </div>

                        <div class="text-sm text-slate-400 mt-2">

{{ \Illuminate\Support\Str::limit($news->content_snippet,120) }}
                        </div>

                    </div>

                @endforeach

            </div>

        </div>

        <div class="glass rounded-2xl p-6">

            <h2 class="text-2xl font-bold mb-5">

                🚢 Top Ports

            </h2>

            <div class="space-y-4">

                @foreach($topPorts as $port)

                    <div class="flex justify-between">

                        <div>

                            <div class="font-semibold">

                                {{ $port->name }}

                            </div>

                            <div class="text-sm text-slate-400">

                                {{ optional($port->country)->name }}

                            </div>

                        </div>

                        <div class="font-bold">

                            {{ number_format($port->outflows) }}

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
// Execute immediately or wait for DOMContentLoaded
function initDashboard() {
    console.log('Dashboard: Initializing charts and maps...');
    
    const ctx = document.getElementById('riskChart');

if (ctx && typeof Chart !== 'undefined') {

    new Chart(ctx, {

        type: 'doughnut',

        data: {

            labels: ['High', 'Medium', 'Low'],

            datasets: [{

                data: [
                    {{ $highCount }},
                    {{ $mediumCount }},
                    {{ $lowCount }}
                ],

                backgroundColor: [
                    '#ef4444',
                    '#f59e0b',
                    '#22c55e'
                ],

                borderWidth: 0

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {

                    labels: {
                        color: 'white'
                    }

                }

            }

        }

    });

}
const map = L.map('world-map',{

    worldCopyJump:true,

    zoomControl:true

}).setView([20,0],2);
setTimeout(() => { map.invalidateSize(); }, 300);
const riskMap = @json($riskMap);

L.tileLayer(
'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
{
    attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom:19
}).addTo(map);
fetch('/data/world.geojson')

.then(response => response.json())

.then(world => {

    L.geoJSON(world, {

        style: function(feature){

const code = feature.id;
            let color = '#22c55e';

            if(riskMap[code]){

                if(riskMap[code].level === 'High'){

                    color = '#ef4444';

                }else if(riskMap[code].level === 'Medium'){

                    color = '#f59e0b';

                }

            }

            return{

                color:'#475569',

                weight:1,

                fillColor:color,

                fillOpacity:.35

            };

        },

        onEachFeature:function(feature,layer){

const code = feature.id;
            if(riskMap[code]){

                layer.bindTooltip(

                    `<b>${feature.properties.name}</b>
                    <br>
                    Risk : ${riskMap[code].level}
                    <br>
                    Score : ${riskMap[code].score}`

                );

            }

        }

    }).addTo(map);

});

const bounds=[];

const cluster=L.markerClusterGroup({

    showCoverageOnHover:false,

    spiderfyOnMaxZoom:true,

    maxClusterRadius:60,

    disableClusteringAtZoom:8

});

const ports=@json($ports);

ports.forEach(port=>{

    if(port.latitude==null||port.longitude==null)
        return;

    const lat=parseFloat(port.latitude);
    const lng=parseFloat(port.longitude);

    bounds.push([lat,lng]);

    let color='#22c55e';

    if(port.outflows>100000){

        color='#ef4444';

    }else if(port.outflows>30000){

        color='#f59e0b';

    }

const marker = L.circleMarker([lat, lng], {
    radius: port.outflows > 100000 ? 10 :
            port.outflows > 30000 ? 8 : 6,
    color: color,
    fillColor: color,
    fillOpacity: 0.9
});

cluster.addLayer(marker);

marker.bindPopup(`
<div style="
width:260px;
font-family:Arial,sans-serif;
">

<div style="
font-size:18px;
font-weight:bold;
margin-bottom:10px;
color:#0f172a;
">

🚢 ${port.name}

</div>

<table style="
width:100%;
font-size:13px;
border-collapse:collapse;
">

<tr>

<td style="padding:6px 0;"><b>Country</b></td>

<td style="text-align:right">
${port.country?.name ?? '-'}
</td>

</tr>

<tr>

<td style="padding:6px 0;"><b>UNLOCODE</b></td>

<td style="text-align:right">
${port.locode ?? '-'}
</td>

</tr>

<tr>

<td style="padding:6px 0;"><b>Status</b></td>

<td style="text-align:right">

<span style="
background:#22c55e;
color:white;
padding:3px 8px;
border-radius:20px;
font-size:11px;
">

${port.status ?? 'Operational'}

</span>

</td>

</tr>

<tr>

<td style="padding:6px 0;"><b>Outflows</b></td>

<td style="text-align:right">

${Number(port.outflows).toLocaleString()}

</td>

</tr>

<tr>

<td style="padding:6px 0;"><b>Latitude</b></td>

<td style="text-align:right">

${lat.toFixed(4)}

</td>

</tr>

<tr>

<td style="padding:6px 0;"><b>Longitude</b></td>

<td style="text-align:right">

${lng.toFixed(4)}

</td>

</tr>

</table>

<hr style="
margin:12px 0;
">

<a href="/ports/${port.id}"
style="
display:block;
width:100%;
padding:10px;
background:#2563eb;
color:white;
text-align:center;
border-radius:8px;
text-decoration:none;
">
Open Detail
</a>

</div>

`);

marker.on('mouseover', function () {
    this.openPopup();
});

});   // <-- MENUTUP ports.forEach()

map.addLayer(cluster);

if (bounds.length) {
    map.fitBounds(bounds, {
        padding: [40, 40]
    });
}

const legend = L.control({
    position: 'bottomright'
});

legend.onAdd = function () {

    const div = L.DomUtil.create('div');

    div.style.background = 'white';
    div.style.padding = '15px';
    div.style.borderRadius = '12px';
    div.style.boxShadow = '0 10px 20px rgba(0,0,0,.25)';
    div.style.lineHeight = '24px';

    div.innerHTML = `
        <b style="font-size:15px">Port Activity</b><br>
        🔴 High (>100k)<br>
        🟠 Medium (>30k)<br>
        🟢 Low
    `;

    return div;
};

legend.addTo(map);

} // End initDashboard

// Check if DOM is already loaded or wait for it
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboard);
} else {
    // DOM already loaded, execute immediately
    initDashboard();
}
</script>

@endpush