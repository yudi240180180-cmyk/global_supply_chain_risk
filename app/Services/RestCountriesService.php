<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RestCountriesService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.restcountries.url');
        $this->apiKey = config('services.restcountries.key');
    }

    /**
     * Ambil semua negara dari API dan simpan/update ke tabel countries.
     * Free plan max limit=100 per request, jadi kita looping pakai offset.
     */
    public function syncAllCountries(): int
    {
        $totalSynced = 0;
        $limit = 100;
        $offset = 0;

        do {
            $response = Http::withToken($this->apiKey)
                ->get($this->baseUrl, [
                    'limit' => $limit,
                    'offset' => $offset,
                ]);

            if (! $response->successful()) {
                Log::error('RestCountries API error: ' . $response->body());
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

        } while (count($objects) === $limit); // lanjut kalau masih penuh 100 (berarti ada lagi)

        return $totalSynced;
    }

    /**
     * Simpan 1 negara ke database (insert kalau baru, update kalau sudah ada).
     */
    protected function saveCountry(array $item): ?Country
{
    $code = $item['codes']['alpha_3'] ?? null;

    // Skip kalau tidak ada kode negara, supaya tidak tabrakan/tertimpa
    if (empty($code)) {
        Log::warning('Skip negara tanpa kode: ' . ($item['names']['common'] ?? 'unknown'));
        return null;
    }
$currencies = $item['currencies'] ?? [];
    $currencyCode = $currencies[0]['code'] ?? null;
    $currencyName = $currencies[0]['name'] ?? null;

    return Country::updateOrCreate(
        ['code' => $code],
        [
            'name' => $item['names']['common'] ?? 'Unknown',
            'region' => $item['region'] ?? null,
            'subregion' => $item['subregion'] ?? null,
            'currency_code' => $currencyCode,
            'currency_name' => $currencyName,
            'capital' => $item['capitals'][0]['name'] ?? null,
            'latitude' => $item['coordinates']['lat'] ?? null,
            'longitude' => $item['coordinates']['lng'] ?? null,
            'languages' => $item['languages'] ?? [],
            'flag_url' => $item['flag']['url_svg'] ?? null,
        ]
    );
}
}