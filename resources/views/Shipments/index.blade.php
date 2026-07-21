@extends('layouts.app')

@section('title','Shipments')

@section('content')

<div class="space-y-6">

    <div class="flex justify-between items-center">

        <div>

            <h1 class="text-4xl font-bold">

                🚢 Shipment Management

            </h1>

            <p class="text-slate-400">

                International Shipment List

            </p>

        </div>

        <a

            href="{{ route('shipments.create') }}"

            class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl">

            + New Shipment

        </a>

    </div>

    <div class="glass rounded-2xl overflow-hidden">

        <table class="w-full">

            <thead class="bg-slate-800">

                <tr>

                    <th class="p-4 text-left">Supplier</th>

                    <th>Origin</th>

                    <th>Destination</th>

                    <th>Distance</th>

                    <th>Days</th>

                    <th>Cost</th>

                    <th>Status</th>

                </tr>

            </thead>

            <tbody>

            @foreach($shipments as $shipment)

                <tr class="border-t border-slate-700">

                    <td class="p-4">

                        {{ $shipment->supplier->company_name }}

                    </td>

                    <td>

                        {{ $shipment->originPort->name }}

                    </td>

                    <td>

                        {{ $shipment->destinationPort->name }}

                    </td>

                    <td>

                        {{ number_format($shipment->distance_km,0) }} km

                    </td>

                    <td>

                        {{ $shipment->estimated_days }} Days

                    </td>

                    <td>

                        $

                        {{ number_format($shipment->shipping_cost,0) }}

                    </td>

                    <td>

                        <span class="px-3 py-1 rounded bg-blue-600">

                            {{ $shipment->status }}

                        </span>

                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>

    <div>

        {{ $shipments->links() }}

    </div>

</div>

@endsection