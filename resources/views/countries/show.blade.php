@extends('layouts.app')

@section('title', $country->name . ' — Country Detail')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="glass rounded-2xl p-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-5">
                @if($country->flag_url)
                    <img src="{{ $country->flag_url }}" alt="{{ $country->name }}"
                         class="w-20 h-14 object-cover rounded-xl shadow-lg">
                @endif
                <div>
                    <h1 class="text-4xl font-bold">{{ $country->name }}</h1>
                    <div class="flex gap-3 mt-2 flex-wrap">
                        <span class="glass px-3 py-1 rounded-full text-sm">{{ $country->region }}</span>
                        <span class="glass px-3 py-1 rounded-full text-sm">{{ $country->capital }}</span>
                        <span class="glass px-3 py-1 rounded-full text-sm">{{ $country->currency_code }} — {{ $country->currency_name }}</span>
                        @if($country->code)
                            <span class="glass px-3 py-1 rounded-full text-sm font-mono">{{ $country->code }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('compare.index') }}?ids={{ $country->id }}"
                   class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 transition text-sm font-semibold">
                    ⚖️ Compare
                </a>
                <a href="{{ route('countries.index') }}"
                   class="px-5 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 transition text-sm">
                    ← Back
                </a>
            </div>
        </div>
    </div>

    {{-- Risk Score Banner --}}
    @php
        $risk      = $country->latestRiskScore;
        $level     = $risk?->risk_level ?? 'N/A';
        $riskColor = match($level) {
            'High'   => 'from-red-600/30 to-red-800/20 border-red-500/50',
            'Medium' => 'from-yellow-600/30 to-yellow-800/20 border-yellow-500/50',
            'Low'    => 'from-green-600/30 to-green-800/20 border-green-500/50',
            default  => 'from-slate-600/20 to-slate-800/20 border-slate-500/50',
        };
        $scoreColor = match($level) {
            'High'   => 'text-red-400',
            'Medium' => 'text-yellow-400',
            'Low'    => 'text-green-400',
            default  => 'text-slate-400',
        };
    @endphp
    <div class="rounded-2xl p-6 bg-gradient-to-r {{ $riskColor }} border">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-slate-400 mb-1">Overall Risk Score</div>
                <div class="text-5xl font-black {{ $scoreColor }}">
                    {{ $risk?->total_score ? number_format($risk->total_score, 1) : '—' }}
                </div>
                <div class="text-xl font-bold mt-1">{{ $level }} Risk</div>
            </div>
            @if($risk)
            <div class="grid grid-cols-2 gap-4 text-center">
                <div class="glass rounded-xl p-4">
                    <div class="text-xs text-slate-400">Weather</div>
                    <div class="text-2xl font-bold">{{ number_format($risk->weather_score, 0) }}</div>
                </div>
                <div class="glass rounded-xl p-4">
                    <div class="text-xs text-slate-400">Economic</div>
                    <div class="text-2xl font-bold">{{ number_format($risk->inflation_score, 0) }}</div>
                </div>
                <div class="glass rounded-xl p-4">
                    <div class="text-xs text-slate-400">Currency</div>
                    <div class="text-2xl font-bold">{{ number_format($risk->currency_score, 0) }}</div>
                </div>
                <div class="glass rounded-xl p-4">
                    <div class="text-xs text-slate-400">News</div>
                    <div class="text-2xl font-bold">{{ number_format($risk->news_score, 0) }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">

        {{-- LEFT: Economics + Currency --}}
        <div class="col-span-2 space-y-6">

            {{-- Economics --}}
            <div class="glass rounded-2xl p-6">
                <h2 class="text-xl font-bold mb-5">📊 Economic Indicators</h2>
                @php $eco = $country->latestEconomics; @endphp
                @if($eco)
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-800/50 rounded-xl p-4">
                        <div class="text-slate-400 text-sm">GDP</div>
                        <div class="text-2xl font-bold mt-1">${{ number_format($eco->gdp / 1e9, 2) }}B</div>
                        <div class="text-xs text-slate-500 mt-1">Year: {{ $eco->data_year }}</div>
                    </div>
                    <div class="bg-slate-800/50 rounded-xl p-4">
                        <div class="text-slate-400 text-sm">Inflation Rate</div>
                        <div class="text-2xl font-bold mt-1 {{ $eco->inflation > 10 ? 'text-red-400' : ($eco->inflation > 5 ? 'text-yellow-400' : 'text-green-400') }}">
                            {{ number_format($eco->inflation, 2) }}%
                        </div>
                    </div>
                    <div class="bg-slate-800/50 rounded-xl p-4">
                        <div class="text-slate-400 text-sm">Population</div>
                        <div class="text-2xl font-bold mt-1">{{ $eco->population ? number_format($eco->population / 1e6, 1) . 'M' : '—' }}</div>
                    </div>
                    <div class="bg-slate-800/50 rounded-xl p-4">
                        <div class="text-slate-400 text-sm">Trade Balance</div>
                        @php $balance = ($eco->exports ?? 0) - ($eco->imports ?? 0); @endphp
                        <div class="text-2xl font-bold mt-1 {{ $balance >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $balance >= 0 ? '+' : '' }}${{ number_format($balance / 1e9, 1) }}B
                        </div>
                    </div>
                    <div class="bg-slate-800/50 rounded-xl p-4">
                        <div class="text-slate-400 text-sm">Exports</div>
                        <div class="text-xl font-bold mt-1">${{ number_format(($eco->exports ?? 0) / 1e9, 1) }}B</div>
                    </div>
                    <div class="bg-slate-800/50 rounded-xl p-4">
                        <div class="text-slate-400 text-sm">Imports</div>
                        <div class="text-xl font-bold mt-1">${{ number_format(($eco->imports ?? 0) / 1e9, 1) }}B</div>
                    </div>
                </div>

                {{-- GDP Trend Chart --}}
                @if($country->economics->count() > 1)
                <div class="mt-5">
                    <h3 class="text-sm text-slate-400 mb-3">GDP Trend</h3>
                    <div style="height:180px">
                        <canvas id="gdpChart"></canvas>
                    </div>
                </div>
                @endif

                @else
                <p class="text-slate-400">No economic data available for this country yet.</p>
                @endif
            </div>

            {{-- Exchange Rate --}}
            @if($exchangeHistory->count())
            <div class="glass rounded-2xl p-6">
                <h2 class="text-xl font-bold mb-2">💱 {{ $country->currency_code }} / USD Exchange Rate</h2>
                @php $latestRate = $exchangeHistory->last(); @endphp
                <div class="text-3xl font-black text-blue-400 mb-5">
                    1 USD = {{ number_format($latestRate->rate_to_usd, 4) }} {{ $country->currency_code }}
                </div>
                <div style="height:200px">
                    <canvas id="exchangeChart"></canvas>
                </div>
            </div>
            @endif

            {{-- Risk Trend --}}
            @if($country->riskScores->count() > 1)
            <div class="glass rounded-2xl p-6">
                <h2 class="text-xl font-bold mb-4">⚠️ Risk Score Trend</h2>
                <div style="height:200px">
                    <canvas id="riskTrendChart"></canvas>
                </div>
            </div>
            @endif

        </div>

        {{-- RIGHT: Weather + Info + Nearby --}}
        <div class="space-y-6">

            {{-- Weather --}}
            @php $weather = $country->latestWeather; @endphp
            <div class="glass rounded-2xl p-5">
                <h2 class="text-lg font-bold mb-4">🌤️ Current Weather</h2>
                @if($weather)
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Condition</span>
                        <span class="font-semibold">{{ $weather->weather_condition ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Temperature</span>
                        <span class="font-bold text-lg">{{ number_format($weather->temperature, 1) }}°C</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Rainfall</span>
                        <span class="font-semibold">{{ number_format($weather->rainfall, 1) }} mm</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Wind Speed</span>
                        <span class="font-semibold">{{ number_format($weather->wind_speed, 1) }} km/h</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Storm Risk</span>
                        <div class="w-24 bg-slate-700 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full {{ $weather->storm_risk > 60 ? 'bg-red-500' : ($weather->storm_risk > 30 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                 style="width: {{ min($weather->storm_risk,100) }}%"></div>
                        </div>
                    </div>
                </div>
                @else
                <p class="text-slate-400 text-sm">No weather data available.</p>
                @endif
            </div>

            {{-- Country Info --}}
            <div class="glass rounded-2xl p-5">
                <h2 class="text-lg font-bold mb-4">ℹ️ Country Info</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Capital</span>
                        <span>{{ $country->capital ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Region</span>
                        <span>{{ $country->region ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Subregion</span>
                        <span>{{ $country->subregion ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Currency</span>
                        <span>{{ $country->currency_code }} ({{ $country->currency_name }})</span>
                    </div>
                    @if($country->latitude && $country->longitude)
                    <div class="flex justify-between">
                        <span class="text-slate-400">Coordinates</span>
                        <span class="text-xs font-mono">{{ number_format($country->latitude, 2) }}, {{ number_format($country->longitude, 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Nearby Countries --}}
            @if($nearby->count())
            <div class="glass rounded-2xl p-5">
                <h2 class="text-lg font-bold mb-4">🌐 Same Region</h2>
                <div class="space-y-3">
                    @foreach($nearby as $nb)
                        @php
                            $nbRisk  = $nb->latestRiskScore;
                            $nbLevel = $nbRisk?->risk_level ?? 'N/A';
                            $nbBadge = match($nbLevel) {
                                'High'   => 'bg-red-500',
                                'Medium' => 'bg-yellow-500',
                                'Low'    => 'bg-green-500',
                                default  => 'bg-slate-600',
                            };
                        @endphp
                        <a href="{{ route('countries.show', $nb->id) }}"
                           class="flex items-center justify-between hover:bg-slate-700/50 rounded-xl p-2 transition">
                            <div class="flex items-center gap-2">
                                @if($nb->flag_url)
                                    <img src="{{ $nb->flag_url }}" class="w-7 h-5 object-cover rounded">
                                @endif
                                <span class="text-sm">{{ $nb->name }}</span>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $nbBadge }}">{{ $nbLevel }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { labels: { color: '#94a3b8' } } },
    scales: {
        x: { ticks: { color: '#64748b' }, grid: { color: '#1e293b' } },
        y: { ticks: { color: '#64748b' }, grid: { color: '#1e293b' } }
    }
};

@if($country->economics->count() > 1)
new Chart(document.getElementById('gdpChart'), {
    type: 'line',
    data: {
        labels: @json($country->economics->pluck('data_year')),
        datasets: [{
            label: 'GDP (USD)',
            data: @json($country->economics->pluck('gdp')),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 3,
        }]
    },
    options: chartDefaults
});
@endif

@if($exchangeHistory->count() > 1)
new Chart(document.getElementById('exchangeChart'), {
    type: 'line',
    data: {
        labels: @json($exchangeHistory->map(fn($r) => $r->fetched_at?->format('d M'))),
        datasets: [{
            label: '{{ $country->currency_code }}/USD',
            data: @json($exchangeHistory->pluck('rate_to_usd')),
            borderColor: '#8b5cf6',
            backgroundColor: 'rgba(139,92,246,0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 2,
        }]
    },
    options: chartDefaults
});
@endif

@if($country->riskScores->count() > 1)
new Chart(document.getElementById('riskTrendChart'), {
    type: 'line',
    data: {
        labels: @json($country->riskScores->reverse()->map(fn($r) => $r->calculated_at?->format('d M'))->values()),
        datasets: [{
            label: 'Risk Score',
            data: @json($country->riskScores->reverse()->pluck('total_score')->values()),
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239,68,68,0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 3,
        }]
    },
    options: chartDefaults
});
@endif
</script>
@endpush
