<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RiskScore;
use App\Models\Country;

class RiskPageController extends Controller
{
    public function index()
    {
        $latestBatch = RiskScore::max('calculated_at');

        $risks = RiskScore::with('country')
            ->where('calculated_at', $latestBatch)
            ->orderByDesc('total_score')
            ->get();

        $highCount   = $risks->where('risk_level', 'High')->count();
        $mediumCount = $risks->where('risk_level', 'Medium')->count();
        $lowCount    = $risks->where('risk_level', 'Low')->count();

        $avgScore = $risks->avg('total_score');

        // Risk trend: last 10 batches, global average per batch
        $trendData = RiskScore::selectRaw('DATE(calculated_at) as day, AVG(total_score) as avg_score')
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->limit(30)
            ->get();

        return view('risk.index', compact(
            'risks', 'highCount', 'mediumCount', 'lowCount', 'avgScore', 'trendData', 'latestBatch'
        ));
    }
}
