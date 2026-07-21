@extends('layouts.manager')

@section('title', 'Supplier Profile')
@section('page-title')
    Supplier Profile: {{ $supplier->company_name }}
@endsection
@section('page-desc', 'Evaluate logistical performance metrics, average risk indexes, and active shipment history.')

@section('content')
<div class="space-y-6">

    {{-- Back Bar --}}
    <div class="flex justify-between items-center">
        <a href="{{ route('manager.suppliers.index') }}" class="btn-secondary py-2 px-3 text-xs">
            <i class="bi bi-arrow-left"></i> Back to Directory
        </a>
    </div>

    {{-- Metrics Header Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

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
                <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Delayed</p>
                <h3 class="text-3xl font-black mt-2 text-rose-400">{{ $stats['delayed'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-600/20 flex items-center justify-center text-rose-400 text-xl">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>

        <div class="glass p-6 rounded-2xl flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Avg Shipment Risk</p>
                <h3 class="text-3xl font-black mt-2 text-white">{{ $stats['avg_risk'] }}<span class="text-sm text-slate-500 font-bold">/100</span></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 text-xl">
                <i class="bi bi-shield-check"></i>
            </div>
        </div>

    </div>

    {{-- Info Breakdown --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Supplier Profile Card --}}
        <div class="glass p-6 rounded-2xl border border-white/5 space-y-4 h-fit">
            <div class="flex justify-between items-start border-b border-slate-800 pb-4">
                <div>
                    <h3 class="font-bold text-white text-lg">{{ $supplier->company_name }}</h3>
                    <div class="flex items-center gap-1.5 text-xs text-slate-400 mt-1">
                        <span class="text-lg">{{ $supplier->country->flag ?? '🌍' }}</span>
                        <span>{{ $supplier->country->name }}</span>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold badge-{{ strtolower($supplier->risk_level) }}">
                    {{ $supplier->risk_level }} Risk
                </span>
            </div>

            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-slate-500 text-xs font-semibold block uppercase">Contact Person</span>
                    <span class="font-medium text-white">{{ $supplier->contact_person ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 text-xs font-semibold block uppercase">Email</span>
                    <span class="font-medium text-white">{{ $supplier->email ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 text-xs font-semibold block uppercase">Phone</span>
                    <span class="font-medium text-white">{{ $supplier->phone ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 text-xs font-semibold block uppercase">Partner Rating</span>
                    <div class="flex items-center gap-0.5 text-amber-400 mt-0.5">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= round($supplier->rating))
                                <i class="bi bi-star-fill text-xs"></i>
                            @else
                                <i class="bi bi-star text-xs text-slate-600"></i>
                            @endif
                        @endfor
                        <span class="text-xs text-slate-400 ml-1 font-semibold">({{ number_format($supplier->rating, 1) }})</span>
                    </div>
                </div>
                <div>
                    <span class="text-slate-500 text-xs font-semibold block uppercase">Supplier Type</span>
                    <span class="font-medium text-white">{{ $supplier->supplier_type }}</span>
                </div>
                <div>
                    <span class="text-slate-500 text-xs font-semibold block uppercase">Office Address</span>
                    <p class="text-slate-300 text-xs mt-1 leading-relaxed">{{ $supplier->address ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        {{-- Shipment History Table --}}
        <div class="lg:col-span-2 glass p-6 rounded-2xl border border-white/5 space-y-4">
            <h3 class="font-bold text-white text-base">Logistical History (Last 10 Shipments)</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-semibold">
                            <th class="pb-3">Code</th>
                            <th class="pb-3">Origin / Destination</th>
                            <th class="pb-3">Risk Level</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3 text-right">DSS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        @forelse($shipments as $shipment)
                        <tr class="hover:bg-white/[0.01] transition">
                            <td class="py-3 font-semibold text-white">
                                <a href="{{ route('manager.shipments.show', $shipment) }}" class="hover:text-violet-400">
                                    {{ $shipment->shipment_code }}
                                </a>
                            </td>
                            <td class="py-3 text-xs">
                                <span class="block text-slate-300">{{ $shipment->originPort->name }}</span>
                                <span class="block text-slate-500 mt-0.5">→ {{ $shipment->destinationPort->name }}</span>
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold badge-{{ strtolower($shipment->risk_level) }}">
                                    {{ $shipment->risk_level }} ({{ round($shipment->overall_risk_score) }})
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold status-{{ strtolower(str_replace(' ', '-', $shipment->tracking_status)) }}">
                                    {{ $shipment->tracking_status }}
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                <a href="{{ route('manager.shipments.show', $shipment) }}" class="text-violet-400 hover:underline text-xs">
                                    Analyze
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">No shipment logs found for this partner.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
