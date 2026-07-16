<?php

namespace Tests\Feature;

use App\Services\ExchangeRateService;
use App\Services\NewsService;
use App\Services\RestCountriesService;
use App\Services\RiskScoringService;
use App\Services\WeatherService;
use App\Services\WorldBankService;
use Mockery;
use Tests\TestCase;

class SyncPipelineTest extends TestCase
{
    public function test_sync_pipeline_runs_all_data_sources(): void
    {
        $countriesService = Mockery::mock(RestCountriesService::class);
        $countriesService->expects('syncAllCountries')->once()->andReturn(10);

        $economicsService = Mockery::mock(WorldBankService::class);
        $economicsService->expects('syncAllCountries')->once()->andReturn(8);

        $weatherService = Mockery::mock(WeatherService::class);
        $weatherService->expects('syncAllCountries')->once()->andReturn(6);

        $newsService = Mockery::mock(NewsService::class);
        $newsService->expects('syncNews')->once()->andReturn(12);

        $ratesService = Mockery::mock(ExchangeRateService::class);
        $ratesService->expects('syncRates')->once()->andReturn(120);

        $riskService = Mockery::mock(RiskScoringService::class);
        $riskService->expects('calculateAllCountries')->once()->andReturn(5);

        $this->app->instance(RestCountriesService::class, $countriesService);
        $this->app->instance(WorldBankService::class, $economicsService);
        $this->app->instance(WeatherService::class, $weatherService);
        $this->app->instance(NewsService::class, $newsService);
        $this->app->instance(ExchangeRateService::class, $ratesService);
        $this->app->instance(RiskScoringService::class, $riskService);

        $this->artisan('sync:all')
            ->expectsOutputToContain('Pipeline completed successfully')
            ->assertSuccessful();
    }
}
