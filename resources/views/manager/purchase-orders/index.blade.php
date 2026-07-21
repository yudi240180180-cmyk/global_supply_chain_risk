@extends('layouts.manager')

@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Orders')
@section('page-desc', 'Initiate, approve, and track purchase requests linked directly to supplier entities and shipments.')

@section('content')
<div class="space-y-6">

    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <i class="bi bi-file-earmark-text text-violet-400"></i> Active Purchase Orders
        </h2>
        <a href="{{ route('manager.purchase-orders.create') }}" class="btn-primary">
            <i class="bi bi-file-earmark-plus"></i> Create Purchase Order
        </a>
    </div>

    {{-- PO Table --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-white/[0.02] border-b border-slate-800 text-slate-400 text-xs uppercase font-semibold">
                    <tr>
                        <th class="p-4">PO Number</th>
                        <th>Supplier</th>
                        <th>Attached Shipment</th>
                        <th>Dates</th>
                        <th>Total Value</th>
                        <th>Status</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($orders as $po)
                    <tr class="hover:bg-white/[0.01] transition">
                        <td class="p-4 font-bold text-white">
                            {{ $po->po_number }}
                            <span class="block text-[10px] text-slate-500 font-normal">Created {{ $po->created_at->format('d M Y') }}</span>
                        </td>
                        <td>
                            <div class="font-medium text-white">{{ $po->supplier->company_name }}</div>
                            <div class="text-xs text-slate-400">{{ $po->supplier->country->name }}</div>
                        </td>
                        <td>
                            @if($po->shipment)
                            <a href="{{ route('manager.shipments.show', $po->shipment) }}" class="text-xs text-violet-400 hover:underline">
                                {{ $po->shipment->shipment_code }}
                            </a>
                            <span class="block text-[10px] text-slate-500">{{ $po->shipment->cargo_name }}</span>
                            @else
                            <span class="text-slate-500 text-xs">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-xs">Order: <span class="text-slate-300">{{ $po->order_date->format('d M Y') }}</span></div>
                            <div class="text-xs mt-0.5">Est. Arrival: <span class="text-amber-400 font-medium">{{ $po->expected_date ? $po->expected_date->format('d M Y') : 'N/A' }}</span></div>
                        </td>
                        <td>
                            <span class="font-bold text-emerald-400">${{ number_format($po->total_amount, 2) }}</span>
                        </td>
                        <td>
                            <span class="px-2.5 py-0.5 rounded-full text-xs bg-{{ $po->status_color }}-500/20 text-{{ $po->status_color }}-400 border border-{{ $po->status_color }}-500/25">
                                {{ $po->status }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('manager.purchase-orders.show', $po) }}" class="btn-secondary py-1.5 px-3 text-xs">
                                <i class="bi bi-eye"></i> Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500">
                            No purchase orders created yet. Click 'Create Purchase Order' to begin.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="p-4 border-t border-slate-800 bg-white/[0.01]">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
