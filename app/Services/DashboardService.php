<?php

namespace App\Services;

use App\Models\Country;
use App\Models\NewsArticle;
use App\Models\Port;
use App\Models\RiskScore;

class DashboardService
{
    public function getDashboardData(): array
    {
        $latestRisk = RiskScore::with('country')
            ->latest('calculated_at');

        return [

            'statistics' => [

                'countries' => Country::count(),

                'ports' => Port::count(),

                'news' => NewsArticle::count(),

                'risk' => [

                    'high' => RiskScore::where('risk_level', 'HIGH')->count(),

                    'medium' => RiskScore::where('risk_level', 'MEDIUM')->count(),

                    'low' => RiskScore::where('risk_level', 'LOW')->count(),

                ],
            ],

            'top_risk_countries' => $latestRisk
                ->orderByDesc('total_score')
                ->take(10)
                ->get(),

            'top_ports' => Port::with('country')
                ->orderByDesc('outflows')
                ->take(10)
                ->get(),

            'latest_news' => NewsArticle::latest('published_at')
                ->take(5)
                ->get(),

            'map' => RiskScore::with('country')
                ->whereNotNull('country_id')
                ->take(200)
                ->get(),
        ];
    }
}