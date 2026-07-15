<?php

namespace App\Console\Commands;

use App\Services\RiskScoringService;
use Illuminate\Console\Command;

class CalculateRiskScores extends Command
{
    protected $signature = 'calculate:risk';
    protected $description = 'Hitung Risk Score untuk semua negara (Risk Amplification Model)';

    public function handle(RiskScoringService $service): int
    {
        $this->info('Menghitung risk score semua negara...');

        $total = $service->calculateAllCountries();

        $this->info("Selesai. Total {$total} negara berhasil dihitung risk score-nya.");

        return self::SUCCESS;
    }
}