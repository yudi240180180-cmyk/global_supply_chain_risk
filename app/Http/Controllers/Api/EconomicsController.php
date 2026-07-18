<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CountryEconomicsHistory;
use Illuminate\Http\Request;

class EconomicsController extends Controller
{
    public function index(Request $request)
    {
        $query = CountryEconomicsHistory::with('country')->latest('fetched_at');

        if ($request->has('country_code')) {
            $query->whereHas('country', fn ($q) => $q->where('code', $request->query('country_code')));
        }

        $data = $query->paginate(20);

        return response()->json($data->through(fn ($item) => [
            'country_id' => $item->country_id,
            'country_name' => $item->country?->name,
            'country_code' => $item->country?->code,
            'gdp' => $item->gdp,
            'inflation' => $item->inflation,
            'population' => $item->population,
            'exports' => $item->exports,
            'imports' => $item->imports,
            'data_year' => $item->data_year,
            'fetched_at' => $item->fetched_at?->toDateTimeString(),
        ]));
    }

    public function showCountryEconomics($countryCode)
    {
        $history = CountryEconomicsHistory::with('country')
            ->whereHas('country', fn ($q) => $q->where('code', $countryCode))
            ->latest('fetched_at')
            ->get();

        if ($history->isEmpty()) {
            return response()->json(['message' => 'No economics history found for country code ' . $countryCode], 404);
        }

        return response()->json([
            'country' => [
                'id' => $history->first()->country?->id,
                'name' => $history->first()->country?->name,
                'code' => $history->first()->country?->code,
            ],
            'history' => $history->map(fn ($item) => [
                'gdp' => $item->gdp,
                'inflation' => $item->inflation,
                'population' => $item->population,
                'exports' => $item->exports,
                'imports' => $item->imports,
                'data_year' => $item->data_year,
                'fetched_at' => $item->fetched_at?->toDateTimeString(),
            ]),
        ]);
    }
}
