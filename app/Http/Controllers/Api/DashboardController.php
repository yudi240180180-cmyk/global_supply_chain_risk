<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CountryEconomicsHistory;
use App\Models\ExchangeRateHistory;
use App\Models\NewsArticle;
use App\Models\Port;
use App\Models\RiskScore;
use App\Models\WeatherHistory;

class DashboardController extends Controller
{
    public function apiSummary()
    {
        return response()->json([
            'total_countries' => \App\Models\Country::count(),
            'total_ports'     => \App\Models\Port::count(),
            'total_news'      => \App\Models\NewsArticle::count(),
            'high_risk'       => \App\Models\RiskScore::where('risk_level', 'High')
                                    ->where('calculated_at', \App\Models\RiskScore::max('calculated_at'))
                                    ->count(),
            'medium_risk'     => \App\Models\RiskScore::where('risk_level', 'Medium')
                                    ->where('calculated_at', \App\Models\RiskScore::max('calculated_at'))
                                    ->count(),
            'low_risk'        => \App\Models\RiskScore::where('risk_level', 'Low')
                                    ->where('calculated_at', \App\Models\RiskScore::max('calculated_at'))
                                    ->count(),
        ]);
    }

    public function index()
    {
        $totalCountries = Country::count();
        $totalPorts = Port::count();

        $economicsCount = CountryEconomicsHistory::count();
        $exchangeCount = ExchangeRateHistory::count();
        $weatherCount = WeatherHistory::count();
        $highCount = RiskScore::where('risk_level', 'High')->count();

$mediumCount = RiskScore::where('risk_level', 'Medium')->count();
$latestWeather = WeatherHistory::latest()->first();

$latestExchange = ExchangeRateHistory::latest()->first();

$lowCount = RiskScore::where('risk_level', 'Low')->count();
        $newsCount = NewsArticle::count();

        $latestRisk = RiskScore::latest('calculated_at')->first();

        $highRiskCountries = RiskScore::with('country')
    ->orderByDesc('total_score')
    ->take(10)
    ->get();

        $topRiskCountries = RiskScore::with('country')
            ->orderByDesc('total_score')
            ->take(10)
            ->get();

        $latestNews = NewsArticle::latest('published_at')
            ->take(5)
            ->get();

        $topPorts = Port::with('country')
            ->orderByDesc('outflows')
            ->take(10)
            ->get();

        $ports = Port::with('country')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
        
        $riskMap = RiskScore::with('country')
    ->get()
    ->mapWithKeys(function ($risk) {

        if (!$risk->country || empty($risk->country->iso2)) {
            return [];
        }

        return [
            strtoupper($risk->country->iso2) => [
                'score' => (float) $risk->total_score,
                'level' => $risk->risk_level,
            ]
        ];
    });

        return view('dashboard.index', compact(
            'totalCountries',
            'totalPorts',
            'economicsCount',
            'exchangeCount',
            'weatherCount',
            'newsCount',
            'latestRisk',
            'highRiskCountries',
            'topRiskCountries',
            'latestNews',
            'topPorts',
            'ports',
            'highCount',
            'mediumCount',
            'lowCount',
            'latestWeather',
            'riskMap',
'latestExchange',
        ));
    }
}