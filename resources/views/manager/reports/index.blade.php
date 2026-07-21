@extends('layouts.manager')

@section('title', 'Reports')
@section('page-title', 'Supply Chain Reports')
@section('page-desc', 'Deep aggregate performance analytics, risk mitigation evaluations, and spending indicators.')

@section('content')
<div class="space-y-6">

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

        <div class="glass p-6 rounded-2xl">
            <span class="text-slate-400 text-xs font-semibold block uppercase">Total Purchase Orders</span>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-3xl font-black text-white">{{ $poStats['total'] }}</span>
                <span class="text-xs text-slate-500">Draft / Approved: {{ $poStats['draft'] }} / {{ $poStats['approved'] }}</span>
            </div>
        </div>

        <div class="glass p-6 rounded-2xl">
            <span class="text-slate-400 text-xs font-semibold block uppercase">Total Logistics Spending</span>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-3xl font-black text-emerald-400">${{ number_format($totalCost, 0) }}</span>
                <span class="text-xs text-slate-500">USD total</span>
            </div>
        </div>

        <div class="glass p-6 rounded-2xl">
            <span class="text-slate-400 text-xs font-semibold block uppercase">PO Financial Value</span>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-3xl font-black text-emerald-400">${{ number_format($poStats['total_value'], 0) }}</span>
                <span class="text-xs text-slate-500">Contract value</span>
            </div>
        </div>

        <div class="glass p-6 rounded-2xl">
            <span class="text-slate-400 text-xs font-semibold block uppercase">Avg Completion Rate</span>
            <div class="flex items-baseline gap-2 mt-2">
                @php
                    $total = $poStats['total'];
                    $rate = $total > 0 ? round(($poStats['completed'] / $total) * 100, 1) : 0;
                @endphp
                <span class="text-3xl font-black text-white">{{ $rate }}%</span>
                <span class="text-xs text-slate-500">completed POs</span>
            </div>
        </div>

    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Monthly Shipment Trends Chart --}}
        <div class="lg:col-span-2 glass p-6 rounded-2xl">
            <h3 class="font-bold text-white text-base mb-4"><i class="bi bi-graph-up mr-2 text-violet-400"></i>Monthly Logistics Volatility</h3>
            <div class="h-80">
                <canvas id="monthlyVolumeChart"></canvas>
            </div>
        </div>

        {{-- Risk Index Distribution --}}
        <div class="glass p-6 rounded-2xl flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-white text-base mb-4"><i class="bi bi-pie-chart mr-2 text-rose-400"></i>Risk Index Profile</h3>
                <div class="h-60 relative flex items-center justify-center">
                    <canvas id="riskDistChart"></canvas>
                </div>
            </div>
            <div class="flex justify-around text-xs text-slate-400 mt-4 border-t border-slate-800 pt-3">
                <div class="text-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block mr-1"></span>
                    <span>High: {{ $riskDist['High'] }}</span>
                </div>
                <div class="text-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block mr-1"></span>
                    <span>Med: {{ $riskDist['Medium'] }}</span>
                </div>
                <div class="text-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block mr-1"></span>
                    <span>Low: {{ $riskDist['Low'] }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Top Suppliers Performance Table --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 glass p-6 rounded-2xl">
            <h3 class="font-bold text-white text-base mb-4"><i class="bi bi-people mr-2 text-violet-400"></i>Corporate Supplier Rankings</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-semibold">
                            <th class="pb-3">Supplier Partner</th>
                            <th class="pb-3">Type</th>
                            <th class="pb-3">Origin Country</th>
                            <th class="pb-3 text-right">Corporate Rating</th>
                            <th class="pb-3 text-right">Total Transits</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @forelse($topSuppliers as $supplier)
                        <tr class="hover:bg-white/[0.01] transition">
                            <td class="py-3 font-semibold text-white">
                                <a href="{{ route('manager.suppliers.show', $supplier) }}" class="hover:text-violet-400">
                                    {{ $supplier->company_name }}
                                </a>
                            </td>
                            <td class="py-3 text-xs text-slate-400">{{ $supplier->supplier_type }}</td>
                            <td class="py-3 text-xs">
                                <span>{{ $supplier->country->flag }}</span>
                                <span class="ml-1">{{ $supplier->country->name }}</span>
                            </td>
                            <td class="py-3 text-right font-medium text-amber-400">
                                <i class="bi bi-star-fill text-xs mr-0.5"></i>{{ number_format($supplier->rating, 1) }}
                            </td>
                            <td class="py-3 text-right font-semibold text-white">
                                {{ $supplier->shipments_count }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">No active suppliers found with shipment data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Status breakdown summary --}}
        <div class="glass p-6 rounded-2xl">
            <h3 class="font-bold text-white text-base mb-4"><i class="bi bi-list-stars mr-2 text-violet-400"></i>Transit Phase Breakdown</h3>
            <div class="space-y-3.5">
                @php
                    $allPhases = ['Planning', 'Ready', 'Loading', 'Departed', 'At Sea', 'Arrived', 'Completed', 'Delayed', 'Cancelled'];
                @endphp
                @foreach($allPhases as $phase)
                    @php
                        $count = $statusBreakdown[$phase] ?? 0;
                        $totalShipments = array_sum($statusBreakdown->toArray());
                        $pct = $totalShipments > 0 ? round(($count / $totalShipments) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-300">
                            <span>{{ $phase }}</span>
                            <span>{{ $count }} ({{ $pct }}%)</span>
                        </div>
                        <div class="h-1.5 bg-slate-800 rounded-full mt-1.5 overflow-hidden">
                            <div class="h-full bg-violet-400" style="width: {{ $pct }}%"></div>
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
    document.addEventListener("DOMContentLoaded", function() {
        // Line chart for monthly volumes
        const lineCtx = document.getElementById('monthlyVolumeChart').getContext('2d');
        const months = @json(collect($monthlyShipments)->pluck('month'));
        const totals = @json(collect($monthlyShipments)->pluck('total'));
        const completeds = @json(collect($monthlyShipments)->pluck('completed'));
        const delayeds = @json(collect($monthlyShipments)->pluck('delayed'));

        new Chart(lineCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Total Planned',
                        data: totals,
                        backgroundColor: 'rgba(99, 102, 241, 0.4)',
                        borderColor: '#6366f1',
                        borderWidth: 1.5,
                    },
                    {
                        label: 'Completed',
                        data: completeds,
                        backgroundColor: 'rgba(16, 185, 129, 0.4)',
                        borderColor: '#10b981',
                        borderWidth: 1.5,
                    },
                    {
                        label: 'Delayed',
                        data: delayeds,
                        backgroundColor: 'rgba(239, 68, 68, 0.4)',
                        borderColor: '#ef4444',
                        borderWidth: 1.5,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#94a3b8' }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#94a3b8' }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#94a3b8', stepSize: 1 },
                        beginAtZero: true
                    }
                }
            }
        });

        // Doughnut chart for risk distribution
        const pieCtx = document.getElementById('riskDistChart').getContext('2d');
        const riskHigh = parseInt("{{ $riskDist['High'] }}");
        const riskMed = parseInt("{{ $riskDist['Medium'] }}");
        const riskLow = parseInt("{{ $riskDist['Low'] }}");

        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['High Risk', 'Medium Risk', 'Low Risk'],
                datasets: [{
                    data: [riskHigh, riskMed, riskLow],
                    backgroundColor: ['#ef4444', '#f59e0b', '#10b981'],
                    borderWidth: 2,
                    borderColor: '#020817',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '70%'
            }
        });
    });
</script>
@endpush
