<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Supplier;
use App\Models\Port;
use App\Services\ShipmentService;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    protected ShipmentService $shipmentService;

    public function __construct(ShipmentService $shipmentService)
    {
        $this->shipmentService = $shipmentService;
    }

    public function index()
    {
        $shipments = Shipment::with([
            'supplier',
            'originPort.country',
            'destinationPort.country'
        ])
        ->latest()
        ->paginate(15);

        return view('shipments.index', compact('shipments'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('company_name')->get();

        $ports = Port::with('country')
            ->orderBy('name')
            ->get();

        return view('shipments.create', compact(
            'suppliers',
            'ports'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([

            'supplier_id'=>'required',

            'origin_port_id'=>'required',

            'destination_port_id'=>'required',

            'cargo_name'=>'required',

            'cargo_weight'=>'required|numeric',

            'container_count'=>'required|integer|min:1',

            'container_type'=>'required',

        ]);

        $origin = Port::findOrFail(
            $request->origin_port_id
        );

        $destination = Port::findOrFail(
            $request->destination_port_id
        );

        $result = $this->shipmentService->calculate(
            $origin,
            $destination,
            $request->container_count
        );

        Shipment::create([

            'supplier_id'=>$request->supplier_id,

            'origin_port_id'=>$request->origin_port_id,

            'destination_port_id'=>$request->destination_port_id,

            'cargo_name'=>$request->cargo_name,

            'cargo_weight'=>$request->cargo_weight,

            'container_count'=>$request->container_count,

            'container_type'=>$request->container_type,

            'distance_km'=>$result['distance_km'],

            'estimated_days'=>$result['estimated_days'],

            'shipping_cost'=>$result['shipping_cost'],

            'status'=>'Planning',

        ]);

        return redirect()
            ->route('shipments.index')
            ->with('success','Shipment created successfully.');
    }
}