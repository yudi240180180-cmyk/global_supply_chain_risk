@extends('layouts.app')

@section('title', $port->name)

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="glass rounded-2xl p-8">

        <div class="flex justify-between items-center">

            <div>

                <h1 class="text-5xl font-bold">
                    🚢 {{ $port->name }}
                </h1>

                <p class="text-slate-400 mt-3 text-lg">
                    {{ optional($port->country)->name ?? 'Unknown Country' }}
                </p>

            </div>

            <a href="/"
               class="px-5 py-3 rounded-xl bg-slate-700 hover:bg-slate-600 transition">

                ← Back Dashboard

            </a>

        </div>

    </div>

    {{-- Content --}}
    <div class="grid grid-cols-3 gap-6">

        {{-- LEFT --}}
        <div class="col-span-2 space-y-6">

            {{-- Information --}}
            <div class="glass rounded-2xl p-6">

                <h2 class="text-2xl font-bold mb-6">
                    Port Information
                </h2>

                <table class="w-full">

                    <tbody class="divide-y divide-slate-700">

                        <tr class="h-14">
                            <td class="font-semibold w-52">Country</td>
                            <td>{{ optional($port->country)->name ?? 'N/A' }}</td>
                        </tr>

                        <tr class="h-14">
                            <td class="font-semibold">UNLOCODE</td>
                            <td>{{ $port->locode ?: 'N/A' }}</td>
                        </tr>

                        <tr class="h-14">
                            <td class="font-semibold">Port Type</td>
                            <td>{{ $port->port_type ?: 'International' }}</td>
                        </tr>

                        <tr class="h-14">
                            <td class="font-semibold">Status</td>
                            <td>

                                @php
                                    $status = $port->status ?: 'Operational';
                                @endphp

                                <span class="px-3 py-1 rounded-full
                                    @if($status=='Operational')
                                        bg-green-500
                                    @elseif($status=='Busy')
                                        bg-yellow-500
                                    @elseif($status=='Closed')
                                        bg-red-500
                                    @else
                                        bg-slate-500
                                    @endif">

                                    {{ $status }}

                                </span>

                            </td>
                        </tr>

                        <tr class="h-14">
                            <td class="font-semibold">Outflows</td>
                            <td>{{ number_format($port->outflows ?? 0) }}</td>
                        </tr>

                        <tr class="h-14">
                            <td class="font-semibold">Latitude</td>
                            <td>{{ number_format($port->latitude,4) }}</td>
                        </tr>

                        <tr class="h-14">
                            <td class="font-semibold">Longitude</td>
                            <td>{{ number_format($port->longitude,4) }}</td>
                        </tr>

                        <tr class="h-14">
                            <td class="font-semibold">Created</td>
                            <td>{{ $port->created_at?->format('d M Y H:i') }}</td>
                        </tr>

                        <tr class="h-14">
                            <td class="font-semibold">Last Update</td>
                            <td>{{ $port->updated_at?->format('d M Y H:i') }}</td>
                        </tr>

                    </tbody>

                </table>

            </div>

            {{-- Map --}}
            <div class="glass rounded-2xl p-6">

                <h2 class="text-2xl font-bold mb-5">

                    Port Location

                </h2>

                <div id="port-map"
                     style="height:500px;border-radius:18px;">
                </div>

            </div>

        </div>

        {{-- RIGHT --}}
        <div class="space-y-6">

            {{-- Summary --}}
            <div class="glass rounded-2xl p-6">

                <h2 class="text-xl font-bold mb-5">
                    Summary
                </h2>

                <div class="space-y-5">

                    <div class="flex justify-between">
                        <span>Country</span>
                        <strong>{{ optional($port->country)->name }}</strong>
                    </div>

                    <div class="flex justify-between">
                        <span>Status</span>
                        <strong>{{ $port->status ?: 'Operational' }}</strong>
                    </div>

                    <div class="flex justify-between">
                        <span>Type</span>
                        <strong>{{ $port->port_type ?: 'International' }}</strong>
                    </div>

                    <div class="flex justify-between">
                        <span>Outflows</span>
                        <strong>{{ number_format($port->outflows ?? 0) }}</strong>
                    </div>

                </div>

            </div>

            {{-- Coordinates --}}
            <div class="glass rounded-2xl p-6">

                <h2 class="text-xl font-bold mb-4">

                    Coordinates

                </h2>

                <div class="space-y-3">

                    <div>
                        <div class="text-slate-400 text-sm">
                            Latitude
                        </div>

                        <div class="font-bold text-xl">
                            {{ number_format($port->latitude,4) }}
                        </div>
                    </div>

                    <div>
                        <div class="text-slate-400 text-sm">
                            Longitude
                        </div>

                        <div class="font-bold text-xl">
                            {{ number_format($port->longitude,4) }}
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>

const map = L.map('port-map').setView(
[
{{ $port->latitude }},
{{ $port->longitude }}
],10);

L.tileLayer(
'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
{
    attribution:'© OpenStreetMap'
}).addTo(map);

L.marker([
{{ $port->latitude }},
{{ $port->longitude }}
]).addTo(map)
.bindPopup("<b>{{ $port->name }}</b>")
.openPopup();

</script>

@endpush