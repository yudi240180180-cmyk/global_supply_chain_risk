<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Port;
use App\Models\Country;

class PortsPageController extends Controller
{
    public function index()
    {
        $totalPorts     = Port::count();
        $operational    = Port::where('status', 'Operational')->orWhereNull('status')->count();
        $totalCountries = Country::whereHas('ports')->count();

        // All ports with coordinates for map (no pagination — we need them all for the Leaflet map)
        $ports = Port::with('country')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get();

        // Countries with ports (for filter dropdown)
        $countries = Country::whereHas('ports')
            ->orderBy('name')
            ->select('id', 'name', 'code')
            ->get();

        return view('ports.index', compact(
            'ports', 'countries', 'totalPorts', 'operational', 'totalCountries'
        ));
    }

    public function show($id)
    {
        $port = Port::with('country')->findOrFail($id);

        // Nearby ports (within same country)
        $nearbyPorts = Port::with('country')
            ->where('country_id', $port->country_id)
            ->where('id', '!=', $port->id)
            ->limit(5)
            ->get();

        return view('ports.show', compact('port', 'nearbyPorts'));
    }
}
