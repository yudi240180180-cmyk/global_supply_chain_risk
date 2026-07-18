<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeatherHistory;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function index(Request $request)
    {
        $query = WeatherHistory::with('country')->latest('fetched_at');

        if ($request->has('country_code')) {
            $query->whereHas('country', fn ($q) => $q->where('code', $request->query('country_code')));
        }

        $data = $query->paginate(20);

        return response()->json($data->through(fn ($item) => [
            'country_id' => $item->country_id,
            'country_name' => $item->country?->name,
            'country_code' => $item->country?->code,
            'temperature' => $item->temperature,
            'rainfall' => $item->rainfall,
            'wind_speed' => $item->wind_speed,
            'storm_risk' => $item->storm_risk,
            'weather_condition' => $item->weather_condition,
            'fetched_at' => $item->fetched_at?->toDateTimeString(),
        ]));
    }

    public function showCountryWeather($countryCode)
    {
        $history = WeatherHistory::with('country')
            ->whereHas('country', fn ($q) => $q->where('code', $countryCode))
            ->latest('fetched_at')
            ->get();

        if ($history->isEmpty()) {
            return response()->json(['message' => 'No weather history found for country code ' . $countryCode], 404);
        }

        return response()->json([
            'country' => [
                'id' => $history->first()->country?->id,
                'name' => $history->first()->country?->name,
                'code' => $history->first()->country?->code,
            ],
            'history' => $history->map(fn ($item) => [
                'temperature' => $item->temperature,
                'rainfall' => $item->rainfall,
                'wind_speed' => $item->wind_speed,
                'storm_risk' => $item->storm_risk,
                'weather_condition' => $item->weather_condition,
                'fetched_at' => $item->fetched_at?->toDateTimeString(),
            ]),
        ]);
    }
}
