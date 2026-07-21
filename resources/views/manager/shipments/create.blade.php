@extends('layouts.manager')

@section('title', 'Plan Shipment')
@section('page-title', 'Create Shipment Plan')
@section('page-desc', 'Define route, quantity, container types, and let DSS evaluate the risk parameters.')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('manager.shipments.index') }}" class="btn-secondary py-2 px-3 text-xs">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
        <h2 class="text-xl font-bold text-white">New Import Plan</h2>
    </div>

    <form method="POST" action="{{ route('manager.shipments.store') }}" class="glass rounded-2xl p-8 border border-white/5 space-y-6">
        @csrf

        {{-- Cargo Details Section --}}
        <div>
            <h3 class="text-sm font-bold text-violet-400 uppercase tracking-wider mb-4">1. Cargo & Quantity</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="form-label" for="cargo_name">Cargo Name</label>
                    <input type="text" name="cargo_name" id="cargo_name" class="form-input" placeholder="e.g. Lithium-Ion Battery Cells" value="{{ old('cargo_name') }}" required>
                </div>
                <div>
                    <label class="form-label" for="cargo_weight">Cargo Weight (Tons)</label>
                    <input type="number" step="0.01" name="cargo_weight" id="cargo_weight" class="form-input" placeholder="e.g. 18.5" value="{{ old('cargo_weight') }}" required>
                </div>
                <div>
                    <label class="form-label" for="quantity">Quantity (pcs/units)</label>
                    <input type="number" name="quantity" id="quantity" class="form-input" placeholder="e.g. 50" value="{{ old('quantity', 1) }}" required>
                </div>
            </div>
        </div>

        <hr class="border-slate-800">

        {{-- Supplier & Ports --}}
        <div>
            <h3 class="text-sm font-bold text-violet-400 uppercase tracking-wider mb-4">2. Supply Chain Entities</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="form-label" for="supplier_id">Supplier</label>
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
                    <label class="form-label" for="origin_port_id">Origin Port</label>
                    <select name="origin_port_id" id="origin_port_id" class="form-input" required>
                        <option value="">Select Origin Port</option>
                        @foreach($ports as $port)
                            <option value="{{ $port->id }}" {{ old('origin_port_id') == $port->id ? 'selected' : '' }}>
                                {{ $port->name }} ({{ $port->country->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="destination_port_id">Destination Port</label>
                    <select name="destination_port_id" id="destination_port_id" class="form-input" required>
                        <option value="">Select Destination Port</option>
                        @foreach($ports as $port)
                            <option value="{{ $port->id }}" {{ old('destination_port_id') == $port->id ? 'selected' : '' }}>
                                {{ $port->name }} ({{ $port->country->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <hr class="border-slate-800">

        {{-- Logistics Specifications --}}
        <div>
            <h3 class="text-sm font-bold text-violet-400 uppercase tracking-wider mb-4">3. Logistics Specs & Timeline</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="form-label" for="container_type">Container Type</label>
                    <select name="container_type" id="container_type" class="form-input" required>
                        <option value="20FT" {{ old('container_type') == '20FT' ? 'selected' : '' }}>20 FT Standard</option>
                        <option value="40FT" {{ old('container_type') == '40FT' ? 'selected' : '' }}>40 FT Standard</option>
                        <option value="40HC" {{ old('container_type') == '40HC' ? 'selected' : '' }}>40 FT High Cube</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="container_count">Container Count</label>
                    <input type="number" name="container_count" id="container_count" class="form-input" placeholder="e.g. 5" value="{{ old('container_count', 1) }}" required>
                </div>
                <div>
                    <label class="form-label" for="estimated_departure">Est. Departure</label>
                    <input type="date" name="estimated_departure" id="estimated_departure" class="form-input" value="{{ old('estimated_departure') }}">
                </div>
                <div>
                    <label class="form-label" for="estimated_arrival">Est. Arrival</label>
                    <input type="date" name="estimated_arrival" id="estimated_arrival" class="form-input" value="{{ old('estimated_arrival') }}">
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="pt-4 flex justify-end gap-3">
            <a href="{{ route('manager.shipments.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <i class="bi bi-calculator"></i> Run DSS Risk Analysis & Save
            </button>
        </div>

    </form>

</div>
@endsection
