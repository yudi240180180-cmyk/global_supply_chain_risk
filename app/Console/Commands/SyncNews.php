<?php

namespace App\Console\Commands;

use App\Services\NewsService;
use Illuminate\Console\Command;

class SyncNews extends Command
{
    protected $signature = 'sync:news';
    protected $description = 'Fetch dan simpan berita terkait logistik/ekonomi dari GNews API';

    public function handle(NewsService $service): int
    {
        $this->info('Mengambil berita dari GNews API...');

        $total = $service->syncNews();

        $this->info("Selesai. Total {$total} berita berhasil disimpan/diupdate.");

        return self::SUCCESS;
    }
}