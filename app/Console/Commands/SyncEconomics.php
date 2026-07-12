<?php

namespace App\Console\Commands;

use App\Services\WorldBankService;
use Illuminate\Console\Command;

class SyncEconomics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:economics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch dan simpan data GDP, inflasi, populasi dari World Bank API';

    /**
     * Execute the console command.
     */
    public function handle(WorldBankService $service): int
    {
        $this->info('Mengambil data ekonomi dari World Bank API (ini bisa makan waktu beberapa menit)...');

        $total = $service->syncAllCountries();

        $this->info("Selesai. Total {$total} negara berhasil disimpan datanya.");

        return self::SUCCESS;
    }
}