<?php

namespace App\Console\Commands;

use App\Services\SentimentAnalyzerService;
use Illuminate\Console\Command;

class AnalyzeSentiment extends Command
{
    protected $signature = 'analyze:sentiment';
    protected $description = 'Analisis sentimen berita yang belum dianalisis (lexicon-based)';

    public function handle(SentimentAnalyzerService $service): int
    {
        $this->info('Menganalisis sentimen berita...');

        $total = $service->analyzeAllPending();

        $this->info("Selesai. Total {$total} berita berhasil dianalisis.");

        return self::SUCCESS;
    }
}