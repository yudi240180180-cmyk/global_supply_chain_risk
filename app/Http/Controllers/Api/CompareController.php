<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ExchangeRateHistory;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    /**
     * GET /api/compare?ids=1,2
     * Return full profile for up to 2 countries side by side.
     */
    public function compare(Request $request)
    {
        // Support both ?ids=1,2 and ?ids[]=1&ids[]=2
        if (!$request->filled('ids')) {
            return response()->json(['message' => 'ids parameter is required'], 422);
        }

        $ids = array_filter(array_map('intval', array_slice(explode(',', $request->ids), 0, 2)));

        if (count($ids) < 1) {
            return response()->json(['message' => 'Please provide at least one valid country ID'], 422);
        }

        $countries = Country::with([
            'latestRiskScore',
            'latestEconomics',
            'latestWeather',
        ])->whereIn('id', $ids)->get();

        if ($countries->isEmpty()) {
            return response()->json(['message' => 'No countries found'], 404);
        }

        $result = $countries->map(function ($country) {
            $eco      = $country->latestEconomics;
            $weather  = $country->latestWeather;
            $risk     = $country->latestRiskScore;

            // Exchange rate
            $exchangeRate = $country->currency_code
                ? ExchangeRateHistory::where('currency_code', $country->currency_code)
                    ->latest('fetched_at')->value('rate_to_usd')
                : null;

            return [
                'id'            => $country->id,
                'name'          => $country->name,
                'code'          => $country->code,
                'flag_url'      => $country->flag_url,
                'region'        => $country->region,
                'capital'       => $country->capital,
                'currency_code' => $country->currency_code,
                'currency_name' => $country->currency_name,

                'economics' => $eco ? [
                    'gdp'        => $eco->gdp,
                    'inflation'  => $eco->inflation,
                    'population' => $eco->population,
                    'exports'    => $eco->exports,
                    'imports'    => $eco->imports,
                    'data_year'  => $eco->data_year,
                ] : null,

                'weather' => $weather ? [
                    'temperature'       => $weather->temperature,
                    'rainfall'          => $weather->rainfall,
                    'wind_speed'        => $weather->wind_speed,
                    'storm_risk'        => $weather->storm_risk,
                    'weather_condition' => $weather->weather_condition,
                ] : null,

                'risk' => $risk ? [
                    'total_score'     => $risk->total_score,
                    'risk_level'      => $risk->risk_level,
                    'weather_score'   => $risk->weather_score,
                    'inflation_score' => $risk->inflation_score,
                    'currency_score'  => $risk->currency_score,
                    'news_score'      => $risk->news_score,
                    'calculated_at'   => $risk->calculated_at?->toDateTimeString(),
                ] : null,

                'exchange_rate_usd' => $exchangeRate,
            ];
        });

        return response()->json($result);
    }
}
