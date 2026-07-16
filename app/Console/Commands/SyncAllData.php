<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use App\Services\NewsService;
use App\Services\RestCountriesService;
use App\Services\RiskScoringService;
use App\Services\WeatherService;
use App\Services\WorldBankService;
use Illuminate\Console\Command;

class SyncAllData extends Command
{
    protected $signature = 'sync:all';
    protected $description = 'Run the full real-data sync pipeline for countries, economics, weather, news, FX rates, and risk scores';

    public function handle(
        RestCountriesService $countriesService,
        WorldBankService $economicsService,
        WeatherService $weatherService,
        NewsService $newsService,
        ExchangeRateService $exchangeRateService,
        RiskScoringService $riskScoringService,
    ): int {
        $this->info('Starting full data sync pipeline...');

        $countries = $countriesService->syncAllCountries();
        $this->info("Countries synced: {$countries}");

        $economics = $economicsService->syncAllCountries();
        $this->info("Economics synced: {$economics}");

        $weather = $weatherService->syncAllCountries();
        $this->info("Weather records synced: {$weather}");

        $news = $newsService->syncNews();
        $this->info("News articles synced: {$news}");

        $rates = $exchangeRateService->syncRates();
        $this->info("Exchange rates synced: {$rates}");

        $riskScores = $riskScoringService->calculateAllCountries();
        $this->info("Risk scores calculated: {$riskScores}");

        $this->info('Pipeline completed successfully.');

        return self::SUCCESS;
    }
}
