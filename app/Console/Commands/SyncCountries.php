<?php

namespace App\Console\Commands;

use App\Services\RestCountriesService;
use Illuminate\Console\Command;

class SyncCountries extends Command
{
    protected $signature = 'sync:countries';
    protected $description = 'Fetch dan simpan data semua negara dari REST Countries API';

    public function handle(RestCountriesService $service): int
    {
        $this->info('Mengambil data negara dari REST Countries API...');

        $total = $service->syncAllCountries();

        $this->info("Selesai. Total {$total} negara berhasil disimpan/diupdate.");

        return self::SUCCESS;
    }
}