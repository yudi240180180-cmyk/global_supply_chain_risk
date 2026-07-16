<?php

use App\Models\Country;
use App\Models\CountryEconomicsHistory;
use App\Models\RiskScore;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    $totalCountries = 0;
    $latestRisk = null;
    $highRiskCountries = collect();
    $economicsCount = 0;

    try {
        if (Schema::hasTable('countries')) {
            $totalCountries = Country::count();
        }

        if (Schema::hasTable('risk_scores')) {
            $latestRisk = RiskScore::with('country')->latest('calculated_at')->first();
            $highRiskCountries = RiskScore::with('country')
                ->where('risk_level', 'High')
                ->latest('calculated_at')
                ->take(5)
                ->get();
        }

        if (Schema::hasTable('country_economics_history')) {
            $economicsCount = CountryEconomicsHistory::count();
        }
    } catch (\Throwable $exception) {
        $totalCountries = 0;
        $latestRisk = null;
        $highRiskCountries = collect();
        $economicsCount = 0;
    }

    return view('welcome', compact(
        'totalCountries',
        'latestRisk',
        'highRiskCountries',
        'economicsCount'
    ));
});
