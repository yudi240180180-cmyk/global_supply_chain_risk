<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PortImportService;

class SyncPortsCommand extends Command
{
    /**
     * Nama command Artisan
     */
    protected $signature = 'ports:sync';

    /**
     * Deskripsi command
     */
    protected $description = 'Sync World Bank Global International Ports';

    protected PortImportService $importService;

    public function __construct(PortImportService $importService)
    {
        parent::__construct();

        $this->importService = $importService;
    }

    public function handle()
    {
        $this->info('========================================');
        $this->info(' Global Ports Synchronization');
        $this->info('========================================');

        try {

            $result = $this->importService->import();

            $this->newLine();

            $this->info("Inserted : {$result['inserted']}");
            $this->info("Updated  : {$result['updated']}");
            $this->info("Total    : {$result['total']}");

            $this->newLine();

            $this->info("Ports synchronized successfully.");

            return self::SUCCESS;

        } catch (\Throwable $e) {

            $this->error($e->getMessage());

            return self::FAILURE;

        }
    }
}