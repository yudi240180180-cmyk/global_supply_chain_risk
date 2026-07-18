<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Watchlist;
use Illuminate\Http\Request;

class WatchlistPageController extends Controller
{
    public function index(Request $request)
    {
        // For demo purposes without full auth middleware on web, we show the
        // watchlist page with all countries available to add, and the
        // frontend handles auth state via the API token stored in localStorage.
        $countries = Country::with(['latestRiskScore', 'latestEconomics', 'latestWeather'])
            ->orderBy('name')
            ->get();

        return view('watchlist.index', compact('countries'));
    }
}
