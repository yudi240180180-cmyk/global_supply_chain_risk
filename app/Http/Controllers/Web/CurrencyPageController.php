<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ExchangeRateHistory;
use Illuminate\Support\Facades\DB;

class CurrencyPageController extends Controller
{
    public function index()
    {
        // Latest rate per currency code
        $latestRates = ExchangeRateHistory::select(
                'currency_code',
                DB::raw('MAX(fetched_at) as latest_fetch')
            )
            ->groupBy('currency_code')
            ->get()
            ->map(function ($row) {
                return ExchangeRateHistory::where('currency_code', $row->currency_code)
                    ->where('fetched_at', $row->latest_fetch)
                    ->first();
            })
            ->filter()
            ->sortBy('currency_code')
            ->values();

        // Attach country info
        $countries = Country::whereNotNull('currency_code')
            ->select('id', 'name', 'currency_code', 'currency_name', 'flag_url', 'code')
            ->get()
            ->keyBy('currency_code');

        // Top movers: currencies with biggest % change between last 2 records
        $topMovers = $this->getTopMovers();

        // Total currencies tracked
        $totalCurrencies = ExchangeRateHistory::distinct('currency_code')->count('currency_code');

        return view('currency.index', compact(
            'latestRates', 'countries', 'topMovers', 'totalCurrencies'
        ));
    }

    private function getTopMovers(): \Illuminate\Support\Collection
    {
        $currencies = ExchangeRateHistory::select('currency_code')
            ->groupBy('currency_code')
            ->havingRaw('COUNT(*) >= 2')
            ->pluck('currency_code');

        $movers = collect();

        foreach ($currencies as $code) {
            $records = ExchangeRateHistory::where('currency_code', $code)
                ->orderByDesc('fetched_at')
                ->limit(2)
                ->get();

            if ($records->count() < 2 || $records[1]->rate_to_usd == 0) {
                continue;
            }

            $change = (($records[0]->rate_to_usd - $records[1]->rate_to_usd) / $records[1]->rate_to_usd) * 100;

            $movers->push([
                'currency_code' => $code,
                'current_rate'  => $records[0]->rate_to_usd,
                'previous_rate' => $records[1]->rate_to_usd,
                'change_pct'    => round($change, 4),
                'fetched_at'    => $records[0]->fetched_at,
            ]);
        }

        return $movers->sortByDesc(fn ($m) => abs($m['change_pct']))->take(10)->values();
    }
}
