<?php

namespace App\Console\Commands;

use App\Services\WorldBankService;
use Illuminate\Console\Command;

class SyncEconomics extends Command
{
    protected $signature = 'sync:economics {--priority : Only sync top 50 important countries for speed}';

    protected $description = 'Fetch dan simpan data GDP, inflasi, populasi dari World Bank API';

    public function handle(WorldBankService $service): int
    {
        $priorityOnly = $this->option('priority');

        if ($priorityOnly) {
            $this->info('Mengambil data ekonomi untuk 50 negara prioritas saja...');
        } else {
            $this->info('Mengambil data ekonomi dari World Bank API (ini bisa makan waktu beberapa menit)...');
        }

        $total = $service->syncAllCountries($priorityOnly);

        $this->info("Selesai. Total {$total} negara berhasil disimpan datanya.");

        return self::SUCCESS;
    }
}
