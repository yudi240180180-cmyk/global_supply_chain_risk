<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PortService;
use Illuminate\Http\Request;
use App\Http\Resources\PortResource;

class PortController extends Controller
{
    protected PortService $service;

    public function __construct(PortService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/ports
     */
   public function index(Request $request)
{
    $ports = $this->service->getAll([
        'country' => $request->country,
        'status' => $request->status,
        'search' => $request->search,
        'all' => $request->boolean('all'),
    ]);

    if ($request->boolean('all')) {
        return PortResource::collection($ports);
    }

    return PortResource::collection($ports);
}
    /**
     * GET /api/ports/{id}  — JSON for API consumers
     */
    public function show($id)
    {
        $port = $this->service->getById($id);
        return PortResource::make($port);
    }

    /**
     * Web page for a port — rendered view
     */
    public function showPage($id)
{
    $port = $this->service->getById($id);

    return view('ports.show', compact('port'));
}
    /**
     * GET /api/ports/nearest
     */
    public function nearest(Request $request)
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        return response()->json(
            $this->service->nearest(
                $request->latitude,
                $request->longitude
            )
        );
    }
}