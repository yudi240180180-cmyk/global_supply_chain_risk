@extends('layouts.app')

@section('title','Create Shipment')

@section('content')

<div class="max-w-5xl mx-auto space-y-8">

    <div>
        <h1 class="text-4xl font-bold">
            🚢 Create Shipment
        </h1>

        <p class="text-slate-400 mt-2">
            Create a new international shipment.
        </p>
    </div>

    <div class="glass rounded-2xl p-8">

        <form action="{{ route('shipments.store') }}" method="POST">

            @csrf

            <div class="grid md:grid-cols-2 gap-6">

                {{-- Supplier --}}

                <div>

                    <label class="block mb-2 text-sm text-slate-300">
                        Supplier
                    </label>

                    <select
                        name="supplier_id"
                        class="w-full rounded-xl bg-slate-800 border border-slate-600 px-4 py-3">

                        @foreach($suppliers as $supplier)

                            <option value="{{ $supplier->id }}">

                                {{ $supplier->company_name }}

                             {{ $supplier->country?->name ?? 'Unknown Country' }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- Cargo --}}

                <div>

                    <label class="block mb-2 text-sm text-slate-300">

                        Cargo Name

                    </label>

                    <input

                        type="text"

                        name="cargo_name"

                        class="w-full rounded-xl bg-slate-800 border border-slate-600 px-4 py-3"

                        placeholder="Example : Electronics"

                        required>

                </div>

                {{-- Origin Port --}}

                <div>

                    <label class="block mb-2 text-sm text-slate-300">

                        Origin Port

                    </label>

                    <select

                        name="origin_port_id"

                        class="w-full rounded-xl bg-slate-800 border border-slate-600 px-4 py-3">

                        @foreach($ports as $port)

<option value="{{ $port->id }}">

    {{ $port->name }}

    -

    {{ $port->country?->name ?? 'Unknown Country' }}

</option>

@endforeach

</select>