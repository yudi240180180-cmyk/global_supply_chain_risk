<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * GET /api/countries
     * List semua negara, dengan data ekonomi & cuaca terbaru.
     */
    public function index(Request $request)
    {
        $query = Country::query();

        // Filter opsional by region: /api/countries?region=Asia
        if ($request->has('region')) {
            $query->where('region', $request->region);
        }

        // Search by name: /api/countries?search=indo
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $countries = $query->orderBy('name')->paginate(20);

        return response()->json($countries);
    }

    /**
     * GET /api/countries/{id}
     * Detail 1 negara lengkap dengan data ekonomi, cuaca, dan risk score terbaru.
     */
    public function show($id)
    {
        $country = Country::with([
            'economics' => fn ($q) => $q->latest('fetched_at')->limit(1),
            'weatherHistory' => fn ($q) => $q->latest('fetched_at')->limit(1),
            'riskScores' => fn ($q) => $q->latest('calculated_at')->limit(1),
        ])->find($id);

        if (! $country) {
            return response()->json(['message' => 'Country not found'], 404);
        }

        return response()->json($country);
    }
}