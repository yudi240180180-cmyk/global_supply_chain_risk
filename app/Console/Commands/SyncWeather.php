<?php

namespace App\Console\Commands;

use App\Services\WeatherService;
use Illuminate\Console\Command;

class SyncWeather extends Command
{
    protected $signature = 'sync:weather';
    protected $description = 'Fetch dan simpan data cuaca real-time dari Open-Meteo API';

    public function handle(WeatherService $service): int
    {
        $this->info('Mengambil data cuaca dari Open-Meteo API...');

        $total = $service->syncAllCountries();

        $this->info("Selesai. Total {$total} negara berhasil disimpan data cuacanya.");

        return self::SUCCESS;
    }
}