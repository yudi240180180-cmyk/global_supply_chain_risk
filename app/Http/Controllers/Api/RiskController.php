<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RiskScore;
use App\Models\Country;
use Illuminate\Http\Request;

class RiskController extends Controller
{
    /**
     * GET /api/risk
     * List risk score terbaru untuk semua negara.
     * Query params: ?level=High|Medium|Low, ?sort=desc|asc (by total_score)
     */
    public function index(Request $request)
    {
        // Ambil timestamp batch risk score terbaru
        $latestBatch = RiskScore::max('calculated_at');

        $query = RiskScore::with('country')
            ->where('calculated_at', $latestBatch);

        if ($request->has('level')) {
            $query->where('risk_level', $request->level);
        }

        $sort = $request->get('sort', 'desc');
        $query->orderBy('total_score', $sort === 'asc' ? 'asc' : 'desc');

        $risks = $query->paginate(20);

        return response()->json($risks);
    }

    /**
     * GET /api/risk/{countryId}
     * Riwayat risk score 1 negara (untuk grafik trend).
     */
    public function show($countryId)
    {
        $country = Country::find($countryId);

        if (! $country) {
            return response()->json(['message' => 'Country not found'], 404);
        }

        $history = RiskScore::where('country_id', $countryId)
            ->orderByDesc('calculated_at')
            ->limit(30) // 30 data terakhir untuk grafik
            ->get();

        return response()->json([
            'country' => $country->only(['id', 'name', 'code', 'flag_url']),
            'history' => $history,
        ]);
    }

    /**
     * GET /api/risk/summary
     * Ringkasan jumlah negara per risk level (untuk dashboard chart).
     */
    public function summary()
    {
        $latestBatch = RiskScore::max('calculated_at');

        $summary = RiskScore::where('calculated_at', $latestBatch)
            ->selectRaw('risk_level, count(*) as total')
            ->groupBy('risk_level')
            ->pluck('total', 'risk_level');

        return response()->json($summary);
    }
}