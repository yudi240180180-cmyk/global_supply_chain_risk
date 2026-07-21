@extends('layouts.manager')

@section('title', 'Create PO')
@section('page-title', 'Create Purchase Order')
@section('page-desc', 'Draft purchase logs, select supplier entities, add item descriptions, and optional transit linkage.')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('manager.purchase-orders.index') }}" class="btn-secondary py-2 px-3 text-xs">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
        <h2 class="text-xl font-bold text-white">Draft New Purchase Request</h2>
    </div>

    <form method="POST" action="{{ route('manager.purchase-orders.store') }}" class="glass rounded-2xl p-8 border border-white/5 space-y-6">
        @csrf

        {{-- Section 1: Basic Info --}}
        <div>
            <h3 class="text-sm font-bold text-violet-400 uppercase tracking-wider mb-4">1. Supplier & Logistics Connection</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="form-label" for="supplier_id">Select Supplier</label>
                    <select name="supplier_id" id="supplier_id" class="form-input" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->company_name }} ({{ $supplier->country->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="shipment_id">Attach to Shipment (Optional)</label>
                    <select name="shipment_id" id="shipment_id" class="form-input">
                        <option value="">Select Shipment Plan</option>
                        @foreach($shipments as $shipment)
                            <option value="{{ $shipment->id }}" {{ old('shipment_id') == $shipment->id ? 'selected' : '' }}>
                                {{ $shipment->shipment_code }} — {{ $shipment->cargo_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <hr class="border-slate-800">

        {{-- Section 2: Dates and Details --}}
        <div>
            <h3 class="text-sm font-bold text-violet-400 uppercase tracking-wider mb-4">2. Schedule & Notes</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="form-label" for="order_date">Order Date</label>
                    <input type="date" name="order_date" id="order_date" class="form-input" value="{{ old('order_date', date('Y-m-d')) }}" required>
                </div>
                <div>
                    <label class="form-label" for="expected_date">Est. Delivery Date</label>
                    <input type="date" name="expected_date" id="expected_date" class="form-input" value="{{ old('expected_date') }}">
                </div>
                <div class="md:col-span-3">
                    <label class="form-label" for="notes">Notes / Special Instructions</label>
                    <textarea name="notes" id="notes" rows="3" class="form-input" placeholder="Enter transaction reference, delivery instructions, currency buffer parameters, etc.">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <hr class="border-slate-800">

        {{-- Section 3: PO Items --}}
        <div>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-violet-400 uppercase tracking-wider">3. Order Items</h3>
                <button type="button" onclick="addItemRow()" class="btn-secondary py-1.5 px-3 text-xs">
                    <i class="bi bi-plus-lg"></i> Add Item Row
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-semibold">
                            <th class="pb-3 pr-4" style="width: 45%;">Item Name</th>
                            <th class="pb-3 pr-4" style="width: 15%;">Qty</th>
                            <th class="pb-3 pr-4" style="width: 15%;">Unit</th>
                            <th class="pb-3 pr-4" style="width: 20%;">Unit Price (USD)</th>
                            <th class="pb-3 text-right" style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer">
                        {{-- First Row --}}
                        <tr class="item-row border-b border-slate-850 py-3">
                            <td class="py-2 pr-4">
                                <input type="text" name="items[0][name]" class="form-input" placeholder="e.g. OLED Display Module A" required>
                            </td>
                            <td class="py-2 pr-4">
                                <input type="number" name="items[0][qty]" min="1" class="form-input" placeholder="100" required>
                            </td>
                            <td class="py-2 pr-4">
                                <input type="text" name="items[0][unit]" class="form-input" placeholder="pcs" value="pcs" required>
                            </td>
                            <td class="py-2 pr-4">
                                <input type="number" step="0.01" min="0" name="items[0][price]" class="form-input" placeholder="12.50" required>
                            </td>
                            <td class="py-2 text-right">
                                <button type="button" onclick="removeItemRow(this)" class="text-rose-400 hover:text-rose-300 p-1">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Submit --}}
        <div class="pt-4 flex justify-end gap-3">
            <a href="{{ route('manager.purchase-orders.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <i class="bi bi-file-earmark-check"></i> Generate Purchase Order
            </button>
        </div>

    </form>

</div>

<script>
    let rowIndex = 1;

    function addItemRow() {
        const container = document.getElementById('itemsContainer');
        const tr = document.createElement('tr');
        tr.className = 'item-row border-b border-slate-850 py-3';
        tr.innerHTML = `
            <td class="py-2 pr-4">
                <input type="text" name="items[${rowIndex}][name]" class="form-input" placeholder="Item Name" required>
            </td>
            <td class="py-2 pr-4">
                <input type="number" name="items[${rowIndex}][qty]" min="1" class="form-input" placeholder="Qty" required>
            </td>
            <td class="py-2 pr-4">
                <input type="text" name="items[${rowIndex}][unit]" class="form-input" placeholder="pcs" value="pcs" required>
            </td>
            <td class="py-2 pr-4">
                <input type="number" step="0.01" min="0" name="items[${rowIndex}][price]" class="form-input" placeholder="Price" required>
            </td>
            <td class="py-2 text-right">
                <button type="button" onclick="removeItemRow(this)" class="text-rose-400 hover:text-rose-300 p-1">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        container.appendChild(tr);
        rowIndex++;
    }

    function removeItemRow(btn) {
        const row = btn.closest('tr');
        const container = document.getElementById('itemsContainer');
        if (container.querySelectorAll('.item-row').length > 1) {
            row.remove();
        } else {
            alert('A purchase order must contain at least one item.');
        }
    }
</script>
@endsection
