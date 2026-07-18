<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\WeatherHistory;

class WeatherPageController extends Controller
{
    public function index()
    {
        // Latest weather per country (for the map markers)
        $countries = Country::with(['latestWeather'])
            ->whereHas('weatherHistory')
            ->orderBy('name')
            ->get();

        // Stats
        $avgTemp      = WeatherHistory::avg('temperature');
        $avgWind      = WeatherHistory::avg('wind_speed');
        $avgStormRisk = WeatherHistory::avg('storm_risk');
        $highStorm    = WeatherHistory::where('storm_risk', '>=', 60)->count();

        return view('weather.index', compact(
            'countries', 'avgTemp', 'avgWind', 'avgStormRisk', 'highStorm'
        ));
    }
}
