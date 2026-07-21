<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RestCountriesService
{
    /**
     * Sync all countries from restcountries.com v3.1 (FREE, no key needed, ~250 countries).
     * Fallback: uses hardcoded list if API fails.
     */
    public function syncAllCountries(): int
    {
        $totalSynced = 0;

        try {
            // Free public API - no auth needed, returns all ~250 countries
            $response = Http::timeout(30)
                ->get('https://restcountries.com/v3.1/all', [
                    'fields' => 'name,cca2,cca3,currencies,capital,region,subregion,latlng,languages,flags,population',
                ]);

            if ($response->successful()) {
                $countries = $response->json();

                if (is_array($countries) && count($countries) > 10) {
                    foreach ($countries as $item) {
                        $saved = $this->saveCountryV3($item);
                        if ($saved) {
                            $totalSynced++;
                        }
                    }
                    Log::info("RestCountriesService: Synced {$totalSynced} countries from restcountries.com v3.1");
                    return $totalSynced;
                }
            }

            Log::warning('restcountries.com v3.1 failed or empty, trying custom API...');
        } catch (\Throwable $e) {
            Log::warning('restcountries.com v3.1 exception: ' . $e->getMessage());
        }

        // Fallback: try the custom paid API
        try {
            $baseUrl = config('services.restcountries.url');
            $apiKey  = config('services.restcountries.key');

            $offset = 0;
            $limit  = 100;

            do {
                $response = Http::withToken($apiKey)
                    ->timeout(15)
                    ->get($baseUrl, ['limit' => $limit, 'offset' => $offset]);

                if (!$response->successful()) {
                    Log::error('Custom RestCountries API error: ' . $response->status());
                    break;
                }

                $objects = $response->json('data.objects') ?? [];

                foreach ($objects as $item) {
                    $saved = $this->saveCountry($item);
                    if ($saved) {
                        $totalSynced++;
                    }
                }

                $offset += $limit;
            } while (count($objects) === $limit);

            if ($totalSynced > 0) {
                return $totalSynced;
            }
        } catch (\Throwable $e) {
            Log::warning('Custom RestCountries API exception: ' . $e->getMessage());
        }

        // Last resort: seed from hardcoded fallback
        Log::warning('All country APIs failed. Falling back to hardcoded list.');
        return $this->seedHardcoded();
    }

    /**
     * Save a country from restcountries.com v3.1 format.
     */
    protected function saveCountryV3(array $item): ?Country
    {
        $code = $item['cca3'] ?? null;
        if (empty($code)) {
            return null;
        }

        $currencies = $item['currencies'] ?? [];
        $currencyCode = array_key_first($currencies);
        $currencyName = $currencies[$currencyCode]['name'] ?? null;

        $latlng = $item['latlng'] ?? [];
        $lat = $latlng[0] ?? null;
        $lng = $latlng[1] ?? null;

        $capital = $item['capital'][0] ?? null;

        $languages = $item['languages'] ?? [];
        $languageList = array_values($languages);

        $flagUrl = $item['flags']['svg'] ?? $item['flags']['png'] ?? null;

        return Country::updateOrCreate(
            ['code' => $code],
            [
                'name'          => $item['name']['common'] ?? 'Unknown',
                'iso2'          => $item['cca2'] ?? null,
                'iso3'          => $code,
                'region'        => $item['region'] ?? null,
                'subregion'     => $item['subregion'] ?? null,
                'currency_code' => $currencyCode,
                'currency_name' => $currencyName,
                'capital'       => $capital,
                'latitude'      => $lat,
                'longitude'     => $lng,
                'languages'     => $languageList,
                'flag_url'      => $flagUrl,
            ]
        );
    }

    /**
     * Save a country from the custom paid API format.
     */
    protected function saveCountry(array $item): ?Country
    {
        $code = $item['codes']['alpha_3'] ?? null;
        if (empty($code)) {
            return null;
        }

        $currencies = $item['currencies'] ?? [];
        $currencyCode = $currencies[0]['code'] ?? null;
        $currencyName = $currencies[0]['name'] ?? null;

        return Country::updateOrCreate(
            ['code' => $code],
            [
                'name'          => $item['names']['common'] ?? 'Unknown',
                'region'        => $item['region'] ?? null,
                'subregion'     => $item['subregion'] ?? null,
                'currency_code' => $currencyCode,
                'currency_name' => $currencyName,
                'capital'       => $item['capitals'][0]['name'] ?? null,
                'latitude'      => $item['coordinates']['lat'] ?? null,
                'longitude'     => $item['coordinates']['lng'] ?? null,
                'languages'     => $item['languages'] ?? [],
                'flag_url'      => $item['flag']['url_svg'] ?? null,
            ]
        );
    }

    /**
     * Hardcoded fallback: 21 key trading nations.
     */
    protected function seedHardcoded(): int
    {
        $countries = [
            ['name' => 'Indonesia',           'code' => 'IDN', 'iso2' => 'ID', 'currency_code' => 'IDR', 'latitude' => -0.789,  'longitude' => 113.921],
            ['name' => 'Singapore',           'code' => 'SGP', 'iso2' => 'SG', 'currency_code' => 'SGD', 'latitude' => 1.352,   'longitude' => 103.820],
            ['name' => 'Malaysia',            'code' => 'MYS', 'iso2' => 'MY', 'currency_code' => 'MYR', 'latitude' => 4.210,   'longitude' => 101.975],
            ['name' => 'China',               'code' => 'CHN', 'iso2' => 'CN', 'currency_code' => 'CNY', 'latitude' => 35.861,  'longitude' => 104.195],
            ['name' => 'South Korea',         'code' => 'KOR', 'iso2' => 'KR', 'currency_code' => 'KRW', 'latitude' => 35.908,  'longitude' => 127.767],
            ['name' => 'Japan',               'code' => 'JPN', 'iso2' => 'JP', 'currency_code' => 'JPY', 'latitude' => 36.205,  'longitude' => 138.252],
            ['name' => 'India',               'code' => 'IND', 'iso2' => 'IN', 'currency_code' => 'INR', 'latitude' => 20.594,  'longitude' => 78.963],
            ['name' => 'Australia',           'code' => 'AUS', 'iso2' => 'AU', 'currency_code' => 'AUD', 'latitude' => -25.274, 'longitude' => 133.775],
            ['name' => 'Germany',             'code' => 'DEU', 'iso2' => 'DE', 'currency_code' => 'EUR', 'latitude' => 51.166,  'longitude' => 10.452],
            ['name' => 'Netherlands',         'code' => 'NLD', 'iso2' => 'NL', 'currency_code' => 'EUR', 'latitude' => 52.133,  'longitude' => 5.292],
            ['name' => 'Belgium',             'code' => 'BEL', 'iso2' => 'BE', 'currency_code' => 'EUR', 'latitude' => 50.502,  'longitude' => 4.470],
            ['name' => 'France',              'code' => 'FRA', 'iso2' => 'FR', 'currency_code' => 'EUR', 'latitude' => 46.228,  'longitude' => 2.214],
            ['name' => 'Spain',               'code' => 'ESP', 'iso2' => 'ES', 'currency_code' => 'EUR', 'latitude' => 40.463,  'longitude' => -3.749],
            ['name' => 'Italy',               'code' => 'ITA', 'iso2' => 'IT', 'currency_code' => 'EUR', 'latitude' => 41.872,  'longitude' => 12.567],
            ['name' => 'United States',       'code' => 'USA', 'iso2' => 'US', 'currency_code' => 'USD', 'latitude' => 37.090,  'longitude' => -95.713],
            ['name' => 'Canada',              'code' => 'CAN', 'iso2' => 'CA', 'currency_code' => 'CAD', 'latitude' => 56.131,  'longitude' => -106.347],
            ['name' => 'Brazil',              'code' => 'BRA', 'iso2' => 'BR', 'currency_code' => 'BRL', 'latitude' => -14.235, 'longitude' => -51.926],
            ['name' => 'United Arab Emirates','code' => 'ARE', 'iso2' => 'AE', 'currency_code' => 'AED', 'latitude' => 23.424,  'longitude' => 53.848],
            ['name' => 'Saudi Arabia',        'code' => 'SAU', 'iso2' => 'SA', 'currency_code' => 'SAR', 'latitude' => 23.886,  'longitude' => 45.079],
            ['name' => 'South Africa',        'code' => 'ZAF', 'iso2' => 'ZA', 'currency_code' => 'ZAR', 'latitude' => -30.559, 'longitude' => 22.938],
            ['name' => 'Taiwan',              'code' => 'TWN', 'iso2' => 'TW', 'currency_code' => 'TWD', 'latitude' => 23.698,  'longitude' => 120.961],
        ];

        $count = 0;
        foreach ($countries as $c) {
            Country::updateOrCreate(
                ['code' => $c['code']],
                array_merge($c, ['iso3' => $c['code']])
            );
            $count++;
        }

        return $count;
    }
}