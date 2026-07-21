@extends('layouts.manager')

@section('title', 'Shipments')
@section('page-title', 'Shipment Planner')
@section('page-desc', 'Manage and plan international shipments, analyze risks, and track transit progress.')

@section('content')
<div class="space-y-6">

    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <i class="bi bi-box-seam text-violet-400"></i> Active shipments
        </h2>
        <a href="{{ route('manager.shipments.create') }}" class="btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Plan
        </a>
    </div>

    {{-- Shipments Table/List --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-white/[0.02] border-b border-slate-800 text-slate-400 text-xs uppercase font-semibold">
                    <tr>
                        <th class="p-4">Code</th>
                        <th>Cargo Details</th>
                        <th>Route (Origin → Dest)</th>
                        <th>ETA / Status</th>
                        <th>Risk Assessment</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($shipments as $shipment)
                    <tr class="hover:bg-white/[0.01] transition">
                        <td class="p-4 font-semibold text-white">
                            <span class="block">{{ $shipment->shipment_code }}</span>
                            <span class="text-[10px] text-slate-500 font-normal">Created {{ $shipment->created_at->format('d M Y') }}</span>
                        </td>
                        <td>
                            <div class="font-medium text-white">{{ $shipment->cargo_name }}</div>
                            <div class="text-xs text-slate-400">
                                {{ $shipment->container_count }}x {{ $shipment->container_type }} ({{ number_format($shipment->cargo_weight, 1) }} Tons)
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <span class="text-white font-medium">{{ $shipment->originPort->name }}</span>
                                <span class="text-slate-500">→</span>
                                <span class="text-white font-medium">{{ $shipment->destinationPort->name }}</span>
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                Distance: {{ number_format($shipment->distance_km) }} km | Est. {{ $shipment->estimated_days }} Days
                            </div>
                        </td>
                        <td>
                            <div class="text-white font-medium">
                                {{ $shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y') : 'N/A' }}
                            </div>
                            <div class="mt-1">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold status-{{ strtolower(str_replace(' ', '-', $shipment->tracking_status)) }}">
                                    {{ $shipment->tracking_status }}
                                </span>
                            </div>
                        </td>
                        <td>
                            @if($shipment->overall_risk_score !== null)
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-xs font-bold badge-{{ strtolower($shipment->risk_level) }}">
                                    {{ $shipment->risk_level }} ({{ round($shipment->overall_risk_score) }})
                                </span>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1 max-w-[200px] truncate" title="{{ $shipment->recommendation }}">
                                {{ $shipment->recommendation }}
                            </p>
                            @else
                            <span class="text-slate-500 text-xs">Unassessed</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('manager.shipments.show', $shipment) }}" class="btn-secondary py-1.5 px-3 text-xs">
                                    <i class="bi bi-eye"></i> Details & DSS
                                </a>
                                <a href="{{ route('manager.shipments.track', $shipment) }}" class="btn-primary py-1.5 px-3 text-xs">
                                    <i class="bi bi-geo-alt"></i> Track & Status
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500">
                            No shipments planned yet. Click 'Create New Plan' to start.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($shipments->hasPages())
        <div class="p-4 border-t border-slate-800 bg-white/[0.01]">
            {{ $shipments->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
