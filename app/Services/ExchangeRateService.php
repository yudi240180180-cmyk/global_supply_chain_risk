<?php

namespace App\Services;

use App\Models\ExchangeRateHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.exchangerate.url');
        $this->apiKey = config('services.exchangerate.key');
    }

    public function syncRates(): int
    {
        $response = Http::timeout(15)
            ->retry(2, 300)
            ->get("{$this->baseUrl}/{$this->apiKey}/latest/USD");

        if (! $response->successful()) {
            Log::error('ExchangeRate API gagal: ' . $response->body());
            return 0;
        }

        $json = $response->json();

        if (($json['result'] ?? null) !== 'success') {
            Log::error('ExchangeRate API response tidak sukses: ' . $response->body());
            return 0;
        }

        $rates = $json['conversion_rates'] ?? [];
        $now = now();
        $totalSynced = 0;

        foreach ($rates as $currencyCode => $rate) {
            ExchangeRateHistory::create([
                'currency_code' => $currencyCode,
                'rate_to_usd' => $rate,
                'fetched_at' => $now,
            ]);
            $totalSynced++;
        }

        return $totalSynced;
    }
}