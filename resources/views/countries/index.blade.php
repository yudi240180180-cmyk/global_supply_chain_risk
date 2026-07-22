@extends('layouts.app')

@section('title', 'Countries')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold">🌍 Global Countries</h1>
            <p class="text-slate-400 mt-1">Monitor economic & risk indicators per country</p>
            <p class="text-xs text-slate-500 mt-1">Rendering {{ $countries->count() }} countries in grid (scroll down to see all)</p>
        </div>
        <div class="glass rounded-xl px-5 py-3 text-center">
            <div class="text-2xl font-bold">{{ $countries->count() }}</div>
            <div class="text-xs text-slate-400">Countries Tracked</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="glass rounded-2xl p-5">
        <div class="flex gap-4 flex-wrap items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-slate-400 mb-1">Search Country</label>
                <input id="searchInput" type="text" placeholder="e.g. Germany, Indonesia…"
                    class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-blue-500">
            </div>
            <div class="min-w-[160px]">
                <label class="block text-xs text-slate-400 mb-1">Region</label>
                <select id="regionFilter" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                    <option value="">All Regions</option>
                    @foreach($regions as $region)
                        <option value="{{ $region }}">{{ $region }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[160px]">
                <label class="block text-xs text-slate-400 mb-1">Risk Level</label>
                <select id="riskFilter" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                    <option value="">All Levels</option>
                    <option value="High">High Risk</option>
                    <option value="Medium">Medium Risk</option>
                    <option value="Low">Low Risk</option>
                    <option value="N/A">No Data</option>
                </select>
            </div>
            <button onclick="clearFilters()"
                class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 transition text-sm">
                Clear
            </button>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-4 gap-4">
        @php
            $highCount   = $countries->filter(fn($c) => optional($c->latestRiskScore)->risk_level === 'High')->count();
            $medCount    = $countries->filter(fn($c) => optional($c->latestRiskScore)->risk_level === 'Medium')->count();
            $lowCount    = $countries->filter(fn($c) => optional($c->latestRiskScore)->risk_level === 'Low')->count();
        @endphp
        <div class="glass rounded-2xl p-5 text-center border-t-4 border-slate-500">
            <div class="text-3xl font-bold">{{ $countries->count() }}</div>
            <div class="text-slate-400 text-sm mt-1">Total Countries</div>
        </div>
        <div class="glass rounded-2xl p-5 text-center border-t-4 border-red-500">
            <div class="text-3xl font-bold text-red-400">{{ $highCount }}</div>
            <div class="text-slate-400 text-sm mt-1">High Risk</div>
        </div>
        <div class="glass rounded-2xl p-5 text-center border-t-4 border-yellow-500">
            <div class="text-3xl font-bold text-yellow-400">{{ $medCount }}</div>
            <div class="text-slate-400 text-sm mt-1">Medium Risk</div>
        </div>
        <div class="glass rounded-2xl p-5 text-center border-t-4 border-green-500">
            <div class="text-3xl font-bold text-green-400">{{ $lowCount }}</div>
            <div class="text-slate-400 text-sm mt-1">Low Risk</div>
        </div>
    </div>

    {{-- Country Grid --}}
    <div id="countryGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

        @foreach($countries as $country)
            @php
                $risk    = $country->latestRiskScore;
                $eco     = $country->latestEconomics;
                $weather = $country->latestWeather;
                $level   = $risk?->risk_level ?? 'N/A';
                $levelClass = match($level) {
                    'High'   => 'bg-red-500/20 border-red-500/40 text-red-400',
                    'Medium' => 'bg-yellow-500/20 border-yellow-500/40 text-yellow-400',
                    'Low'    => 'bg-green-500/20 border-green-500/40 text-green-400',
                    default  => 'bg-slate-500/20 border-slate-500/40 text-slate-400',
                };
                $badgeClass = match($level) {
                    'High'   => 'bg-red-500',
                    'Medium' => 'bg-yellow-500',
                    'Low'    => 'bg-green-500',
                    default  => 'bg-slate-600',
                };
            @endphp

            <div class="country-card glass rounded-2xl p-5 card-hover border {{ $levelClass }}"
                 data-name="{{ strtolower($country->name) }}"
                 data-region="{{ $country->region }}"
                 data-risk="{{ $level }}">

                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        @if($country->flag_url)
                            <img src="{{ $country->flag_url }}" alt="{{ $country->name }}"
                                 class="w-10 h-7 object-cover rounded-md shadow">
                        @else
                            <div class="w-10 h-7 rounded-md bg-slate-700 flex items-center justify-center text-lg">🏳</div>
                        @endif
                        <div>
                            <h3 class="font-bold text-lg leading-tight">{{ $country->name }}</h3>
                            <span class="text-xs text-slate-400">{{ $country->region }}</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $badgeClass }}">
                        {{ $level }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                    <div class="bg-slate-800/50 rounded-xl p-3">
                        <div class="text-slate-400 text-xs">GDP</div>
                        <div class="font-semibold mt-0.5">
                            {{ $eco?->gdp ? '$' . number_format($eco->gdp / 1e9, 1) . 'B' : '—' }}
                        </div>
                    </div>
                    <div class="bg-slate-800/50 rounded-xl p-3">
                        <div class="text-slate-400 text-xs">Inflation</div>
                        <div class="font-semibold mt-0.5">
                            {{ $eco?->inflation ? number_format($eco->inflation, 1) . '%' : '—' }}
                        </div>
                    </div>
                    <div class="bg-slate-800/50 rounded-xl p-3">
                        <div class="text-slate-400 text-xs">Temp</div>
                        <div class="font-semibold mt-0.5">
                            {{ $weather?->temperature ? number_format($weather->temperature, 1) . '°C' : '—' }}
                        </div>
                    </div>
                    <div class="bg-slate-800/50 rounded-xl p-3">
                        <div class="text-slate-400 text-xs">Risk Score</div>
                        <div class="font-bold mt-0.5">
                            {{ $risk?->total_score ? number_format($risk->total_score, 1) : '—' }}
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('countries.show', $country->id) }}"
                       class="flex-1 text-center py-2 rounded-xl bg-blue-600 hover:bg-blue-700 transition text-sm font-semibold">
                        View Detail
                    </a>
                    <a href="{{ route('compare.index') }}?ids={{ $country->id }}"
                       class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 transition text-sm">
                        Compare
                    </a>
                </div>

            </div>
        @endforeach

    </div>

    <div id="noResults" class="hidden text-center py-20 text-slate-400">
        <div class="text-5xl mb-4">🔍</div>
        <div class="text-xl">No countries match your filters.</div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const searchInput  = document.getElementById('searchInput');
const regionFilter = document.getElementById('regionFilter');
const riskFilter   = document.getElementById('riskFilter');
const cards        = document.querySelectorAll('.country-card');
const noResults    = document.getElementById('noResults');

// Add a visible results counter
const resultCounter = document.createElement('div');
resultCounter.id = 'resultCounter';
resultCounter.className = 'text-sm text-slate-400 mt-2';
resultCounter.textContent = `Showing ${cards.length} of ${cards.length} countries`;
document.querySelector('#countryGrid').insertAdjacentElement('beforebegin', resultCounter);

function filterCards() {
    const search = searchInput.value.toLowerCase().trim();
    const region = regionFilter.value;
    const risk   = riskFilter.value;
    let visible  = 0;

    cards.forEach(card => {
        const matchName   = !search || card.dataset.name.includes(search);
        const matchRegion = !region || card.dataset.region === region;
        const matchRisk   = !risk   || card.dataset.risk === risk;

        if (matchName && matchRegion && matchRisk) {
            card.style.display = '';
            visible++;
        } else {
            card.style.display = 'none';
        }
    });

    noResults.classList.toggle('hidden', visible > 0);
    resultCounter.textContent = `Showing ${visible} of ${cards.length} countries`;
}

function clearFilters() {
    searchInput.value  = '';
    regionFilter.value = '';
    riskFilter.value   = '';
    filterCards();
}

searchInput.addEventListener('input', filterCards);
regionFilter.addEventListener('change', filterCards);
riskFilter.addEventListener('change', filterCards);
</script>
@endpush
