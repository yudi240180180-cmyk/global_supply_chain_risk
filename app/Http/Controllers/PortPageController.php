<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Models\RiskScore;
use App\Models\NewsArticle;

class PortPageController extends Controller
{
    public function show($id)
    {
        $port = Port::with('country')->findOrFail($id);

        $risk = RiskScore::where('country_id', $port->country_id)
            ->latest('calculated_at')
            ->first();

        $news = NewsArticle::latest('published_at')
            ->take(5)
            ->get();

        return view('ports.show', compact(
            'port',
            'risk',
            'news'
        ));
    }
}