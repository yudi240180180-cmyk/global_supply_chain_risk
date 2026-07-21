<?php

namespace App\Services;

use App\Models\Country;
use App\Models\CountryEconomicsHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WorldBankService
{
    protected string $baseUrl;

    protected array $indicators = [
        'gdp' => 'NY.GDP.MKTP.CD',
        'inflation' => 'FP.CPI.TOTL.ZG',
        'population' => 'SP.POP.TOTL',
        'exports' => 'NE.EXP.GNFS.CD',
        'imports' => 'NE.IMP.GNFS.CD',
    ];

    // Top 50 most important countries for supply chain
    protected array $priorityCountries = [
        'CN', 'US', 'DE', 'JP', 'GB', 'FR', 'IN', 'IT', 'BR', 'CA',
        'KR', 'AU', 'MX', 'ID', 'NL', 'SA', 'TR', 'CH', 'TW', 'PL',
        'SE', 'BE', 'TH', 'NG', 'AT', 'NO', 'AE', 'SG', 'IL', 'MY',
        'ZA', 'PH', 'EG', 'DK', 'HK', 'CL', 'CO', 'FI', 'BD', 'VN',
        'RU', 'ES', 'PT', 'NZ', 'PK', 'IQ', 'QA', 'KW', 'OM', 'MM',
    ];

    public function __construct()
    {
        $this->baseUrl = config('services.worldbank.url');
    }
public function syncAllCountries(bool $priorityOnly = false): int
{
    $query = Country::query();

    if ($priorityOnly) {
        $query->whereIn('code', $this->priorityCountries);
        $this->baseUrl = config('services.worldbank.url');
    }

    $countries = $query->get();
    $totalSynced = 0;

    foreach ($countries as $country) {
        if (empty($country->code)) {
            continue;
        }

        $alreadySynced = CountryEconomicsHistory::where('country_id', $country->id)->exists();
        if ($alreadySynced) {
            continue;
        }

        try {
            $data = $this->fetchEconomicsForCountry($country->code);

            if ($data) {
                CountryEconomicsHistory::create([
                    'country_id' => $country->id,
                    'gdp' => $data['gdp'],
                    'inflation' => $data['inflation'],
                    'population' => $data['population'],
                    'exports' => $data['exports'],
                    'imports' => $data['imports'],
                    'data_year' => $data['year'],
                    'fetched_at' => now(),
                ]);
                $totalSynced++;
            }
        } catch (Throwable $e) {
            Log::error("World Bank sync gagal untuk {$country->name} ({$country->code}): " . $e->getMessage());
            continue;
        }

        usleep(100000); // 0.1 detik (lebih cepat dari 0.3)
    }

    return $totalSynced;
}

    protected function fetchEconomicsForCountry(string $countryCode): ?array
    {
        $result = [];
        $year = null;

        foreach ($this->indicators as $key => $indicatorCode) {
            $response = Http::timeout(5)
                ->retry(1, 200)
                ->get("{$this->baseUrl}/country/{$countryCode}/indicator/{$indicatorCode}", [
                    'format' => 'json',
                    'mrv' => 1,
                ]);

            if (! $response->successful()) {
                Log::warning("World Bank API gagal untuk {$countryCode} - {$indicatorCode}");
                $result[$key] = null;
                continue;
            }

            $json = $response->json();
            $value = $json[1][0]['value'] ?? null;

            $result[$key] = $value;

            if ($value !== null && $year === null) {
                $year = $json[1][0]['date'] ?? null;
            }
        }

        if (collect($result)->every(fn ($v) => $v === null)) {
            return null;
        }

        $result['year'] = $year;

        return $result;
    }
}