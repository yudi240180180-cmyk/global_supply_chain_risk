@extends('layouts.manager')

@section('title', 'Cost Estimator')
@section('page-title', 'Logistics Cost Estimator')
@section('page-desc', 'Compute complex oceanic freight costs, customs duty tariffs, maritime insurance, and currency buffers.')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form Card --}}
        <div class="glass p-6 rounded-2xl border border-white/5 bg-gradient-to-br from-slate-950/20 to-indigo-950/10 h-fit">
            <h3 class="font-bold text-white text-base mb-4"><i class="bi bi-calculator mr-2 text-violet-400"></i>Cost Parameter Inputs</h3>

            <form method="POST" action="{{ route('manager.cost-estimator.calculate') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label" for="origin_port_id">Origin Port</label>
                    <select name="origin_port_id" id="origin_port_id" class="form-input" required>
                        <option value="">Select Origin Port</option>
                        @foreach($ports as $port)
                            <option value="{{ $port->id }}" {{ isset($data['origin_port_id']) && $data['origin_port_id'] == $port->id ? 'selected' : '' }}>
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
                            <option value="{{ $port->id }}" {{ isset($data['destination_port_id']) && $data['destination_port_id'] == $port->id ? 'selected' : '' }}>
                                {{ $port->name }} ({{ $port->country->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label" for="container_type">Container Type</label>
                        <select name="container_type" id="container_type" class="form-input" required>
                            <option value="20FT" {{ isset($data['container_type']) && $data['container_type'] == '20FT' ? 'selected' : '' }}>20FT Standard</option>
                            <option value="40FT" {{ isset($data['container_type']) && $data['container_type'] == '40FT' ? 'selected' : '' }}>40FT Standard</option>
                            <option value="40HC" {{ isset($data['container_type']) && $data['container_type'] == '40HC' ? 'selected' : '' }}>40FT High Cube</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="container_count">Count</label>
                        <input type="number" name="container_count" id="container_count" class="form-input" min="1" max="100" value="{{ $data['container_count'] ?? 1 }}" required>
                    </div>
                </div>

                <div>
                    <label class="form-label" for="cargo_value">Total Cargo Value (USD)</label>
                    <input type="number" step="0.01" name="cargo_value" id="cargo_value" class="form-input" placeholder="e.g. 50000" value="{{ $data['cargo_value'] ?? '' }}">
                </div>

                <div>
                    <label class="form-label" for="commodity">Commodity Class</label>
                    <select name="commodity" id="commodity" class="form-input" required>
                        @php
                            $commodities = [
                                'electronics' => 'Electronics & Tech Accessories',
                                'textile'     => 'Textiles & Garments',
                                'automotive'  => 'Automotive Parts & Vehicles',
                                'food'        => 'Perishable Foodstuffs',
                                'chemicals'   => 'Chemicals & Hazardous Cargo',
                                'machinery'   => 'Industrial Machinery & Tooling',
                                'general'     => 'General Dry Goods',
                            ];
                        @endphp
                        @foreach($commodities as $key => $label)
                            <option value="{{ $key }}" {{ isset($data['commodity']) && $data['commodity'] == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn-primary w-full justify-center py-3">
                    <i class="bi bi-coin"></i> Compute Estimates
                </button>
            </form>
        </div>

        {{-- Results Breakdown Card --}}
        <div class="lg:col-span-2 space-y-6">
            @if(isset($result))
            <div class="glass p-8 rounded-2xl border border-white/5 bg-gradient-to-br from-indigo-950/10 to-slate-950/20 space-y-6">
                <div class="flex justify-between items-center border-b border-slate-800 pb-4">
                    <div>
                        <span class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Estimated Total Cost</span>
                        <h2 class="text-4xl font-black text-emerald-400 mt-1">${{ number_format($result['total_cost'], 2) }} <span class="text-sm font-semibold text-slate-500">{{ $result['currency_code'] }}</span></h2>
                    </div>
                    <div class="text-right text-sm">
                        <span class="text-slate-400 block font-medium">Distance: {{ number_format($result['distance_km']) }} km</span>
                        <span class="text-amber-400 block font-semibold mt-1">Transit Time: {{ $result['estimated_days'] }} Days</span>
                    </div>
                </div>

                {{-- Breakdown Table --}}
                <div class="space-y-4">
                    <h4 class="text-xs font-bold text-violet-400 uppercase tracking-wider">Breakdown of Shipping Charges</h4>
                    <div class="divide-y divide-slate-800 text-sm">
                        <div class="py-3 flex justify-between">
                            <span class="text-slate-300">Ocean Freight Base Rate</span>
                            <span class="font-medium text-white">${{ number_format($result['ocean_freight'], 2) }}</span>
                        </div>
                        <div class="py-3 flex justify-between">
                            <span class="text-slate-300">Marine Insurance Protection (0.5% cargo value)</span>
                            <span class="font-medium text-white">${{ number_format($result['insurance'], 2) }}</span>
                        </div>
                        <div class="py-3 flex justify-between">
                            <span class="text-slate-300">Import Duty & Commodity Tax</span>
                            <span class="font-medium text-white">${{ number_format($result['import_tax'], 2) }}</span>
                        </div>
                        <div class="py-3 flex justify-between">
                            <span class="text-slate-300">Currency Adjustment Factor (CAF)</span>
                            <span class="font-medium text-white">${{ number_format($result['currency_adjustment'], 2) }}</span>
                        </div>
                        <div class="py-3 flex justify-between">
                            <span class="text-slate-300">Local Handling Charges</span>
                            <span class="font-medium text-white">${{ number_format($result['handling_fee'], 2) }}</span>
                        </div>
                        <div class="py-3 flex justify-between">
                            <span class="text-slate-300">Port Tariffs & Terminal Handling</span>
                            <span class="font-medium text-white">${{ number_format($result['port_charges'], 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-900/50 border border-slate-800 rounded-xl text-xs text-slate-500 leading-relaxed">
                    <i class="bi bi-info-circle mr-1"></i> These figures are generated dynamically based on routing coordinates, regional tax tables, and currency rate variance, and serve as budget recommendations for purchase ordering.
                </div>
            </div>
            @else
            <div class="glass p-8 rounded-2xl border border-white/5 flex flex-col items-center justify-center text-center h-full min-h-[300px]">
                <div class="w-16 h-16 rounded-full bg-violet-600/10 border border-violet-500/20 flex items-center justify-center text-violet-400 text-2xl mb-4">
                    <i class="bi bi-coin"></i>
                </div>
                <h4 class="font-bold text-white text-base">Estimate Cost Breakdown</h4>
                <p class="text-slate-500 text-xs mt-2 max-w-sm">Enter origin, destination, container parameters, and click compute to evaluate logistics budgets.</p>
            </div>
            @endif
        </div>

    </div>

</div>
@endsection
