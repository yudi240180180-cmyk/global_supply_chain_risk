<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Port;
use App\Services\ShippingCostService;
use Illuminate\Http\Request;

class CostEstimatorController extends Controller
{
    public function __construct(protected ShippingCostService $costService) {}

    public function index()
    {
        $ports = Port::with('country')->orderBy('name')->get();
        return view('manager.cost-estimator.index', compact('ports'));
    }

    public function calculate(Request $request)
    {
        $data = $request->validate([
            'origin_port_id'      => 'required|exists:ports,id',
            'destination_port_id' => 'required|exists:ports,id',
            'container_count'     => 'required|integer|min:1|max:100',
            'container_type'      => 'required|in:20FT,40FT,40HC',
            'cargo_value'         => 'nullable|numeric|min:0',
            'commodity'           => 'required|string',
        ]);

        $origin      = Port::with('country')->findOrFail($data['origin_port_id']);
        $destination = Port::with('country')->findOrFail($data['destination_port_id']);

        $result = $this->costService->calculate(
            $origin,
            $destination,
            (int) $data['container_count'],
            $data['container_type'],
            (float) ($data['cargo_value'] ?? 0),
            $data['commodity']
        );

        $ports = Port::with('country')->orderBy('name')->get();

        return view('manager.cost-estimator.index', compact(
            'ports', 'origin', 'destination', 'result', 'data'
        ));
    }
}
