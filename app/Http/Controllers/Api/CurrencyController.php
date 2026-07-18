<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRateHistory;
use App\Models\Country;
use Illuminate\Support\Facades\DB;

class CurrencyController extends Controller
{
    /**
     * GET /api/currency
     * Latest rate for every tracked currency.
     */
    public function index()
    {
        $latest = ExchangeRateHistory::select(
                'currency_code',
                DB::raw('MAX(fetched_at) as latest_fetch')
            )
            ->groupBy('currency_code')
            ->get()
            ->map(function ($row) {
                $record = ExchangeRateHistory::where('currency_code', $row->currency_code)
                    ->where('fetched_at', $row->latest_fetch)
                    ->first();

                $country = Country::where('currency_code', $row->currency_code)
                    ->select('name', 'flag_url', 'code')
                    ->first();

                return [
                    'currency_code' => $record->currency_code,
                    'rate_to_usd'   => $record->rate_to_usd,
                    'fetched_at'    => $record->fetched_at?->toDateTimeString(),
                    'country_name'  => $country?->name,
                    'country_code'  => $country?->code,
                    'flag_url'      => $country?->flag_url,
                ];
            })
            ->sortBy('currency_code')
            ->values();

        return response()->json($latest);
    }

    /**
     * GET /api/currency/{currencyCode}
     * Latest single rate.
     */
    public function show($currencyCode)
    {
        $record = ExchangeRateHistory::where('currency_code', strtoupper($currencyCode))
            ->latest('fetched_at')
            ->first();

        if (! $record) {
            return response()->json(['message' => 'Currency not found'], 404);
        }

        return response()->json($record);
    }

    /**
     * GET /api/currency/{currencyCode}/history
     * Historical rates for charting.
     */
    public function history($currencyCode)
    {
        $history = ExchangeRateHistory::where('currency_code', strtoupper($currencyCode))
            ->orderBy('fetched_at', 'asc')
            ->limit(60)
            ->get(['currency_code', 'rate_to_usd', 'fetched_at']);

        if ($history->isEmpty()) {
            return response()->json(['message' => 'No history found'], 404);
        }

        return response()->json([
            'currency_code' => strtoupper($currencyCode),
            'history'       => $history,
        ]);
    }
}
