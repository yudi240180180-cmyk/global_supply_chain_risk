<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

class SyncExchangeRates extends Command
{
    protected $signature = 'sync:rates';
    protected $description = 'Fetch dan simpan kurs mata uang real-time dari ExchangeRate API';

    public function handle(ExchangeRateService $service): int
    {
        $this->info('Mengambil kurs mata uang dari ExchangeRate API...');

        $total = $service->syncRates();

        $this->info("Selesai. Total {$total} mata uang berhasil disimpan.");

        return self::SUCCESS;
    }
}