<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Port;
use App\Services\RouteRecommendationService;
use Illuminate\Http\Request;

class RouteRecommendationController extends Controller
{
    public function __construct(protected RouteRecommendationService $routeService) {}

    public function index()
    {
        $ports = Port::with('country')->orderBy('name')->get();
        return view('manager.routes.index', compact('ports'));
    }

    public function recommend(Request $request)
    {
        $data = $request->validate([
            'origin_port_id'      => 'required|exists:ports,id',
            'destination_port_id' => 'required|exists:ports,id',
        ]);

        if ($data['origin_port_id'] === $data['destination_port_id']) {
            return back()->with('error', 'Origin and destination cannot be the same port.');
        }

        $origin      = Port::with('country')->findOrFail($data['origin_port_id']);
        $destination = Port::with('country')->findOrFail($data['destination_port_id']);

        $routes = $this->routeService->recommend($origin, $destination);

        $ports = Port::with('country')->orderBy('name')->get();

        return view('manager.routes.index', compact('ports', 'origin', 'destination', 'routes'));
    }
}
