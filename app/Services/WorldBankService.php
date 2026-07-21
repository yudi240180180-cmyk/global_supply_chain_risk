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
    }
    $countries = $query->get()->keyBy('code');
    $totalSynced = 0;

    // Don't filter - always sync all countries
    // $alreadySynced = CountryEconomicsHistory::pluck('country_id')->toArray();
    // $countries = $countries->filter(fn($c) => !in_array($c->id, $alreadySynced) && !empty($c->code));

    if ($countries->isEmpty()) {
        return 0;
    }

    // Fetch per indikator untuk SEMUA negara sekaligus (batch request)
    $allData = [];
    foreach ($this->indicators as $key => $indicatorCode) {
        $page = 1;
        do {
            try {
                $response = Http::timeout(30)
                    ->get("{$this->baseUrl}/country/all/indicator/{$indicatorCode}", [
                        'format' => 'json',
                        'mrv'    => 1,
                        'per_page' => 300,
                        'page'   => $page,
                    ]);

                if (!$response->successful()) break;

                $json = $response->json();
                $records = $json[1] ?? [];
                $pages = $json[0]['pages'] ?? 1;

                foreach ($records as $record) {
                    $code = $record['countryiso3code'] ?? null;
                    if (!$code || $record['value'] === null) continue;
                    $allData[$code][$key] = $record['value'];
                    if (empty($allData[$code]['year'])) {
                        $allData[$code]['year'] = $record['date'] ?? null;
                    }
                }
                $page++;
            } catch (Throwable $e) {
                Log::error("World Bank batch fetch gagal untuk {$indicatorCode}: " . $e->getMessage());
                break;
            }
        } while ($page <= $pages);

        sleep(1); // jeda antar indikator
    }

    // Simpan ke database
    foreach ($countries as $code => $country) {
        $data = $allData[$code] ?? null;
        if (!$data) continue;
        if (collect($data)->except('year')->every(fn($v) => $v === null)) continue;

        try {
            CountryEconomicsHistory::create([
                'country_id' => $country->id,
                'gdp'        => $data['gdp'] ?? null,
                'inflation'  => $data['inflation'] ?? null,
                'population' => $data['population'] ?? null,
                'exports'    => $data['exports'] ?? null,
                'imports'    => $data['imports'] ?? null,
                'data_year'  => $data['year'] ?? null,
                'fetched_at' => now(),
            ]);
            $totalSynced++;
        } catch (Throwable $e) {
            Log::error("Gagal simpan ekonomi {$country->name}: " . $e->getMessage());
        }
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