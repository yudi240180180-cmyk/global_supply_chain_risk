@extends('layouts.manager')

@section('title', 'Dashboard')
@section('page-title', 'Import Manager Dashboard')
@section('page-desc', 'Strategic decision support dashboard for international logistics.')

@section('content')
<div class="space-y-6">

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <div class="glass p-6 rounded-2xl flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Total Shipments</p>
                <h3 class="text-3xl font-black mt-2 text-white">{{ $stats['total'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-violet-600/20 flex items-center justify-center text-violet-400 text-xl">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>

        <div class="glass p-6 rounded-2xl flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">On Going</p>
                <h3 class="text-3xl font-black mt-2 text-blue-400">{{ $stats['ongoing'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-600/20 flex items-center justify-center text-blue-400 text-xl">
                <i class="bi bi-arrow-repeat"></i>
            </div>
        </div>

        <div class="glass p-6 rounded-2xl flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Completed</p>
                <h3 class="text-3xl font-black mt-2 text-emerald-400">{{ $stats['completed'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-600/20 flex items-center justify-center text-emerald-400 text-xl">
                <i class="bi bi-check2-circle"></i>
            </div>
        </div>

        <div class="glass p-6 rounded-2xl flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Delayed</p>
                <h3 class="text-3xl font-black mt-2 text-rose-400">{{ $stats['delayed'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-600/20 flex items-center justify-center text-rose-400 text-xl">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>

        <div class="glass p-6 rounded-2xl flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">High Risk</p>
                <h3 class="text-3xl font-black mt-2 text-amber-500">{{ $stats['high_risk'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-500 text-xl">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Column 1 & 2: Shipments and Chart --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Chart --}}
            <div class="glass p-6 rounded-2xl">
                <h3 class="font-bold text-white text-base mb-4"><i class="bi bi-graph-up mr-2 text-violet-400"></i>Shipment Volume Trends</h3>
                <div class="h-64">
                    <canvas id="shipmentTrendsChart"></canvas>
                </div>
            </div>

            {{-- Upcoming ETAs --}}
            <div class="glass p-6 rounded-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-white text-base"><i class="bi bi-calendar-check mr-2 text-blue-400"></i>Upcoming ETAs (In Transit)</h3>
                    <a href="{{ route('manager.shipments.index') }}" class="text-xs text-violet-400 hover:underline">View All Planning</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-semibold">
                                <th class="pb-3">Code</th>
                                <th class="pb-3">Supplier</th>
                                <th class="pb-3">Origin</th>
                                <th class="pb-3">Est. Arrival</th>
                                <th class="pb-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingShipments as $shipment)
                            <tr class="border-b border-slate-800/50 hover:bg-white/5 transition">
                                <td class="py-3 font-semibold text-white">
                                    <a href="{{ route('manager.shipments.show', $shipment) }}" class="hover:text-violet-400">
                                        {{ $shipment->shipment_code }}
                                    </a>
                                </td>
                                <td class="py-3">{{ $shipment->supplier->company_name }}</td>
                                <td class="py-3">{{ $shipment->originPort->name }} ({{ $shipment->originPort->country->name }})</td>
                                <td class="py-3 text-amber-400">{{ $shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y') : '-' }}</td>
                                <td class="py-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs status-{{ strtolower(str_replace(' ', '-', $shipment->tracking_status)) }}">
                                        {{ $shipment->tracking_status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">No shipments in transit at the moment.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent POs --}}
            <div class="glass p-6 rounded-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-white text-base"><i class="bi bi-file-earmark-text mr-2 text-violet-400"></i>Recent Purchase Orders</h3>
                    <a href="{{ route('manager.purchase-orders.index') }}" class="text-xs text-violet-400 hover:underline">View All POs</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-semibold">
                                <th class="pb-3">PO Number</th>
                                <th class="pb-3">Supplier</th>
                                <th class="pb-3">Order Date</th>
                                <th class="pb-3">Total Amount</th>
                                <th class="pb-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPOs as $po)
                            <tr class="border-b border-slate-800/50 hover:bg-white/5 transition">
                                <td class="py-3 font-semibold text-white">
                                    <a href="{{ route('manager.purchase-orders.show', $po) }}" class="hover:text-violet-400">
                                        {{ $po->po_number }}
                                    </a>
                                </td>
                                <td class="py-3">{{ $po->supplier->company_name }}</td>
                                <td class="py-3">{{ $po->order_date->format('d M Y') }}</td>
                                <td class="py-3 text-emerald-400">${{ number_format($po->total_amount, 2) }}</td>
                                <td class="py-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs bg-{{ $po->status_color }}-500/20 text-{{ $po->status_color }}-400 border border-{{ $po->status_color }}-500/25">
                                        {{ $po->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">No purchase orders created yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Column 3: Alerts & Risk Watch --}}
        <div class="space-y-6">

            {{-- Quick Actions --}}
            <div class="glass p-6 rounded-2xl">
                <h3 class="font-bold text-white text-base mb-4">Quick DSS Actions</h3>
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('manager.shipments.create') }}" class="btn-primary justify-center py-3 w-full">
                        <i class="bi bi-plus-circle"></i> Plan New Shipment
                    </a>
                    <a href="{{ route('manager.routes.index') }}" class="btn-secondary justify-center py-3 w-full">
                        <i class="bi bi-map"></i> Recommend Transit Route
                    </a>
                    <a href="{{ route('manager.purchase-orders.create') }}" class="btn-secondary justify-center py-3 w-full">
                        <i class="bi bi-file-plus"></i> Create Purchase Order
                    </a>
                </div>
            </div>

            {{-- High Risk Shipments Alert --}}
            <div class="glass p-6 rounded-2xl border border-rose-500/25 bg-rose-950/10">
                <h3 class="font-bold text-rose-400 text-base mb-3"><i class="bi bi-exclamation-octagon mr-2"></i>High Risk Alerts</h3>
                <div class="space-y-3">
                    @forelse($highRiskShipments as $shipment)
                    <div class="p-3 bg-rose-500/5 hover:bg-rose-500/10 border border-rose-500/20 rounded-xl transition">
                        <div class="flex justify-between items-start">
                            <span class="font-bold text-sm text-white">{{ $shipment->shipment_code }}</span>
                            <span class="badge-high text-[10px] px-2 py-0.5 rounded">Risk: {{ round($shipment->overall_risk_score) }}</span>
                        </div>
                        <p class="text-slate-400 text-xs mt-1 truncate">{{ $shipment->cargo_name }} via {{ $shipment->originPort->name }}</p>
                        <div class="text-[10px] text-rose-400 mt-2 flex items-center gap-1 font-medium">
                            <i class="bi bi-lightbulb"></i>
                            {{ Str::limit($shipment->recommendation, 40) }}
                        </div>
                    </div>
                    @empty
                    <p class="text-slate-500 text-xs">No high risk shipments detected.</p>
                    @endforelse
                </div>
            </div>

            {{-- Currency Alert Widget --}}
            <div class="glass p-6 rounded-2xl">
                <h3 class="font-bold text-white text-base mb-3"><i class="bi bi-currency-exchange mr-2 text-violet-400"></i>Currency Volatility</h3>
                <div class="divide-y divide-slate-800/80">
                    @foreach($currencyRates as $rate)
                    <div class="py-2.5 flex justify-between items-center text-sm">
                        <span class="font-semibold text-slate-300">{{ $rate->currency_code }} / USD</span>
                        <div class="text-right">
                            <span class="text-white font-mono">{{ number_format($rate->rate, 4) }}</span>
                            <span class="text-xs block text-slate-500">Last Synced</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('shipmentTrendsChart').getContext('2d');
        const months = @json(collect($chartData)->pluck('month'));
        const counts = @json(collect($chartData)->pluck('count'));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Shipment Volume',
                    data: counts,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#8b5cf6',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
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
    });
</script>
@endpush
