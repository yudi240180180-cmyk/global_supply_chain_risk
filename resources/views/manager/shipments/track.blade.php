@extends('layouts.manager')

@section('title', 'Track Shipment')
@section('page-title')
    Shipment Tracking: {{ $shipment->shipment_code }}
@endsection
@section('page-desc', 'Track the current logistics phase, update transit status, and view risk progression timeline.')

@section('content')
<div class="space-y-6">

    <div class="flex justify-between items-center">
        <a href="{{ route('manager.shipments.show', $shipment) }}" class="btn-secondary py-2 px-3 text-xs">
            <i class="bi bi-arrow-left"></i> Back to Details
        </a>
        <span class="px-3 py-1 rounded text-xs font-bold badge-{{ strtolower($shipment->risk_level) }}">
            Current Risk: {{ round($shipment->overall_risk_score) }} ({{ $shipment->risk_level }})
        </span>
    </div>

    {{-- Progress Bar Card --}}
    <div class="glass p-8 rounded-2xl border border-white/5 space-y-6 bg-gradient-to-r from-slate-950/20 to-indigo-950/10">
        <div class="flex justify-between items-center">
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Transit Progress</span>
                <span class="text-2xl font-black text-white mt-1 block">{{ $shipment->tracking_progress }}%</span>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Est. Arrival Date</span>
                <span class="text-sm font-semibold text-amber-400 mt-1 block">
                    {{ $shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y') : 'N/A' }}
                </span>
            </div>
        </div>

        {{-- Visual Progress --}}
        <div class="relative py-4">
            <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-violet-500 via-indigo-500 to-cyan-400 transition-all duration-500" style="width: {{ $shipment->tracking_progress }}%"></div>
            </div>

            {{-- Progress Steps Indicators --}}
            @php
                $steps = [
                    'Planning'  => 'Planning',
                    'Ready'     => 'Ready',
                    'Loading'   => 'Loading',
                    'Departed'  => 'Departed',
                    'At Sea'    => 'At Sea',
                    'Arrived'   => 'Arrived',
                    'Completed' => 'Completed',
                ];
                $activeClass = 'bg-violet-500 border-white text-white shadow-[0_0_10px_rgba(139,92,246,0.8)]';
                $inactiveClass = 'bg-slate-900 border-slate-700 text-slate-500';
            @endphp
            <div class="flex justify-between mt-4">
                @foreach($steps as $key => $label)
                    @php
                        $isPassed = $shipment->tracking_progress >= match($key) {
                            'Planning'  => 0,
                            'Ready'     => 15,
                            'Loading'   => 30,
                            'Departed'  => 45,
                            'At Sea'    => 65,
                            'Arrived'   => 85,
                            'Completed' => 100,
                        };
                    @endphp
                    <div class="flex flex-col items-center">
                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center text-[10px] font-bold transition-all duration-300 {{ $isPassed ? $activeClass : $inactiveClass }}">
                            @if($isPassed && $key !== $shipment->tracking_status)
                                <i class="bi bi-check"></i>
                            @else
                                <i class="bi bi-dot"></i>
                            @endif
                        </div>
                        <span class="text-[10px] mt-2 font-medium {{ $shipment->tracking_status === $key ? 'text-white font-bold' : 'text-slate-500' }}">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Main Grid: Update Status and Chart --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Update Status Form --}}
        <div class="glass p-6 rounded-2xl border border-white/5 space-y-6">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Update Transit Phase</h3>

            @if(count($nextStatuses) > 0)
            <form method="POST" action="{{ route('manager.shipments.status', $shipment) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label" for="tracking_status">Next Target Phase</label>
                    <select name="tracking_status" id="tracking_status" class="form-input" required>
                        <option value="">Select Next Status</option>
                        @foreach($nextStatuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label" for="notes">Timeline Log Note</label>
                    <textarea name="notes" id="notes" rows="4" class="form-input" placeholder="Enter log updates, routing adjustments, customs notifications, or delay updates..." required></textarea>
                </div>

                <button type="submit" class="btn-primary w-full justify-center py-3">
                    <i class="bi bi-arrow-right-circle"></i> Commit Status Update
                </button>
            </form>
            @else
            <div class="p-4 bg-slate-900/50 border border-slate-800 rounded-xl text-center">
                <i class="bi bi-check2-all text-emerald-400 text-3xl block mb-2"></i>
                <p class="text-sm font-semibold text-white">Transit Completed</p>
                <p class="text-xs text-slate-500 mt-1">This shipment has reached its final destination and status updates are locked.</p>
            </div>
            @endif
        </div>

        {{-- Risk Progression Timeline Chart --}}
        <div class="lg:col-span-2 glass p-6 rounded-2xl border border-white/5">
            <h3 class="font-bold text-white text-base mb-4"><i class="bi bi-graph-up-red mr-2 text-violet-400"></i>Shipment Risk Timeline</h3>
            <div class="h-64">
                <canvas id="riskTimelineChart"></canvas>
            </div>
            <p class="text-[10px] text-slate-500 mt-4 leading-relaxed">
                *The DSS Engine recalculates currency adjustments, news sentiment indexes, and meteorological warnings dynamically at each phase change to track cumulative risk trends.
            </p>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('riskTimelineChart').getContext('2d');
        const labels = @json($riskTimeline->pluck('label'));
        const riskScores = @json($riskTimeline->pluck('risk'));
        const statuses = @json($riskTimeline->pluck('status'));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Risk Index',
                    data: riskScores,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.2,
                    pointBackgroundColor: '#ef4444',
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const idx = context.dataIndex;
                                return `Risk Score: ${riskScores[idx]} (${statuses[idx]})`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#94a3b8' }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#94a3b8' },
                        min: 0,
                        max: 100
                    }
                }
            }
        });
    });
</script>
@endpush
