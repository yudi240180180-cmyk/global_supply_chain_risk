<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ExchangeRateHistory;

class CountryPageController extends Controller
{
    public function index()
    {
        $countries = Country::with(['latestRiskScore', 'latestEconomics', 'latestWeather'])
            ->orderBy('name')
            ->get();

        $regions = Country::select('region')->whereNotNull('region')
            ->distinct()->orderBy('region')->pluck('region');

        return view('countries.index', compact('countries', 'regions'));
    }

    public function show($id)
    {
        $country = Country::with([
            'latestRiskScore',
            'latestEconomics',
            'latestWeather',
            'economics' => fn ($q) => $q->orderBy('data_year', 'asc')->limit(10),
            'riskScores' => fn ($q) => $q->latest('calculated_at')->limit(15),
            'weatherHistory' => fn ($q) => $q->latest('fetched_at')->limit(10),
        ])->findOrFail($id);

        // Exchange rate history for this country's currency
        $exchangeHistory = $country->currency_code
            ? ExchangeRateHistory::where('currency_code', $country->currency_code)
                ->orderBy('fetched_at', 'asc')
                ->limit(30)
                ->get()
            : collect();

        // Nearby countries (same region)
        $nearby = Country::with('latestRiskScore')
            ->where('region', $country->region)
            ->where('id', '!=', $country->id)
            ->limit(6)
            ->get();

        return view('countries.show', compact('country', 'exchangeHistory', 'nearby'));
    }
}
