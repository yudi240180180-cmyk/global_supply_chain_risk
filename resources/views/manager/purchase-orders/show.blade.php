@extends('layouts.manager')

@section('title', 'Purchase Order Details')
@section('page-title')
    Purchase Order: {{ $purchaseOrder->po_number }}
@endsection
@section('page-desc', 'View items specifications, total contract amount, supplier details, and manage status transitions.')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Back Bar --}}
    <div class="flex justify-between items-center">
        <a href="{{ route('manager.purchase-orders.index') }}" class="btn-secondary py-2 px-3 text-xs">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
        <span class="px-3 py-1 rounded text-xs font-bold bg-{{ $purchaseOrder->status_color }}-500/20 text-{{ $purchaseOrder->status_color }}-400 border border-{{ $purchaseOrder->status_color }}-500/25">
            {{ $purchaseOrder->status }}
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Column 1 & 2: PO Details --}}
        <div class="md:col-span-2 space-y-6">

            {{-- PO General Info --}}
            <div class="glass p-6 rounded-2xl border border-white/5 space-y-4">
                <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Contract Specifications</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-slate-500 text-xs font-semibold block uppercase">Order Date</span>
                        <span class="font-medium text-white">{{ $purchaseOrder->order_date->format('d M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 text-xs font-semibold block uppercase">Est. Delivery</span>
                        <span class="font-medium text-amber-400 font-semibold">{{ $purchaseOrder->expected_date ? $purchaseOrder->expected_date->format('d M Y') : 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 text-xs font-semibold block uppercase">Linked Shipment</span>
                        @if($purchaseOrder->shipment)
                            <a href="{{ route('manager.shipments.show', $purchaseOrder->shipment) }}" class="font-semibold text-violet-400 hover:underline">
                                {{ $purchaseOrder->shipment->shipment_code }}
                            </a>
                        @else
                            <span class="text-slate-400">None</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-500 text-xs font-semibold block uppercase">Total Amount</span>
                        <span class="font-bold text-emerald-400 text-base">${{ number_format($purchaseOrder->total_amount, 2) }} {{ $purchaseOrder->currency_code }}</span>
                    </div>
                </div>

                @if($purchaseOrder->notes)
                <div class="pt-3 border-t border-slate-800">
                    <span class="text-slate-500 text-xs font-semibold block uppercase">Special Instructions / Notes</span>
                    <p class="text-slate-300 text-xs mt-1 leading-relaxed">{{ $purchaseOrder->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Items List --}}
            <div class="glass p-6 rounded-2xl border border-white/5">
                <h3 class="font-bold text-white text-base mb-4">Contract Items</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-semibold">
                                <th class="pb-3">Item Name</th>
                                <th class="pb-3 text-right">Qty</th>
                                <th class="pb-3 text-right">Unit Price</th>
                                <th class="pb-3 text-right">Total Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50">
                            @foreach($purchaseOrder->items as $item)
                            <tr>
                                <td class="py-3 text-white font-medium">{{ $item->item_name }}</td>
                                <td class="py-3 text-right">{{ $item->quantity }} {{ $item->unit }}</td>
                                <td class="py-3 text-right">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-3 text-right text-emerald-400">${{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                            <tr class="font-bold text-white bg-white/[0.01]">
                                <td colspan="3" class="py-4 text-right">Total Value:</td>
                                <td class="py-4 text-right text-emerald-400">${{ number_format($purchaseOrder->total_amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Column 3: Actions & Supplier --}}
        <div class="space-y-6">

            {{-- Status Transition Action --}}
            <div class="glass p-6 rounded-2xl border border-white/5 space-y-4">
                <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Transition status</h3>

                <form method="POST" action="{{ route('manager.purchase-orders.status', $purchaseOrder) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="form-label" for="status">Select State</label>
                        <select name="status" id="status" class="form-input" required>
                            @foreach(['Draft', 'Approved', 'Shipped', 'Completed', 'Cancelled'] as $state)
                                <option value="{{ $state }}" {{ $purchaseOrder->status === $state ? 'selected' : '' }}>
                                    {{ $state }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn-primary w-full justify-center">
                        <i class="bi bi-arrow-repeat"></i> Update PO Status
                    </button>
                </form>
            </div>

            {{-- Supplier details --}}
            <div class="glass p-6 rounded-2xl border border-white/5 space-y-4">
                <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Supplier Information</h3>
                <div class="space-y-2 text-sm">
                    <div class="font-semibold text-white text-base">{{ $purchaseOrder->supplier->company_name }}</div>
                    <div class="text-xs text-slate-400">{{ $purchaseOrder->supplier->contact_person }} (Contact)</div>
                    <div class="text-xs text-slate-400">Email: {{ $purchaseOrder->supplier->email }}</div>
                    <div class="text-xs text-slate-400">Phone: {{ $purchaseOrder->supplier->phone }}</div>
                    <div class="text-xs text-slate-500 pt-2">{{ $purchaseOrder->supplier->address }}</div>

                    <div class="pt-3 flex gap-2">
                        <a href="{{ route('manager.suppliers.show', $purchaseOrder->supplier) }}" class="btn-secondary py-1.5 px-3 text-xs w-full justify-center">
                            <i class="bi bi-person"></i> View Supplier Profile
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
