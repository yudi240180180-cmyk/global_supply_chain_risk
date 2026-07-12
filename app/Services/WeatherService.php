<?php

namespace App\Services;

use App\Models\Country;
use App\Models\WeatherHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WeatherService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.openmeteo.url');
    }

    public function syncAllCountries(): int
    {
        $countries = Country::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $totalSynced = 0;

        foreach ($countries as $country) {
            try {
                $data = $this->fetchWeather($country->latitude, $country->longitude);

                if ($data) {
                    WeatherHistory::create([
                        'country_id' => $country->id,
                        'temperature' => $data['temperature'],
                        'rainfall' => $data['rainfall'],
                        'wind_speed' => $data['wind_speed'],
                        'storm_risk' => $this->calculateStormRisk($data),
                        'weather_condition' => $this->determineCondition($data),
                        'fetched_at' => now(),
                    ]);
                    $totalSynced++;
                }
            } catch (Throwable $e) {
                Log::error("Weather sync gagal untuk {$country->name}: " . $e->getMessage());
                continue;
            }

            usleep(200000); // jeda 0.2 detik antar request, sopan ke API gratis
        }

        return $totalSynced;
    }

    protected function fetchWeather(float $lat, float $lon): ?array
    {
        $response = Http::timeout(15)
            ->retry(2, 300)
            ->get($this->baseUrl, [
                'latitude' => $lat,
                'longitude' => $lon,
                'current' => 'temperature_2m,wind_speed_10m,precipitation,rain',
            ]);

        if (! $response->successful()) {
            return null;
        }

        $current = $response->json('current');

        if (! $current) {
            return null;
        }

        return [
            'temperature' => $current['temperature_2m'] ?? null,
            'wind_speed' => $current['wind_speed_10m'] ?? null,
            'rainfall' => $current['rain'] ?? $current['precipitation'] ?? null,
        ];
    }

    /**
     * Algoritma sederhana buatan sendiri: hitung skor risiko badai (0-100)
     * berdasarkan kombinasi angin kencang + curah hujan tinggi.
     * INI CONTOH — kamu bebas ubah formulanya sendiri biar beda dari mahasiswa lain.
     */
    protected function calculateStormRisk(array $data): float
    {
        $windScore = min(($data['wind_speed'] ?? 0) / 60 * 100, 100); // normalisasi ke skala 100 (asumsi 60 km/j = ekstrem)
        $rainScore = min(($data['rainfall'] ?? 0) / 20 * 100, 100);   // asumsi 20mm = ekstrem

        return round(($windScore * 0.6) + ($rainScore * 0.4), 2);
    }

    protected function determineCondition(array $data): string
    {
        $rain = $data['rainfall'] ?? 0;
        $wind = $data['wind_speed'] ?? 0;

        if ($wind > 50) return 'Storm';
        if ($rain > 10) return 'Heavy Rain';
        if ($rain > 0) return 'Rain';
        return 'Clear';
    }
}