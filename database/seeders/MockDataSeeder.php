<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\CountryEconomicsHistory;
use App\Models\WeatherHistory;
use App\Models\ExchangeRateHistory;
use App\Models\NewsArticle;
use App\Models\NewsSentiment;
use App\Services\RiskScoringService;
use App\Services\SentimentAnalyzerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MockDataSeeder extends Seeder
{
    public function run(): void
    {
        $countries = Country::all();
        if ($countries->isEmpty()) {
            return;
        }

        $now = now();
        $yesterday = now()->subDay();

        // 1. Exchange Rates
        $currencies = $countries->pluck('currency_code')->filter()->unique();
        foreach ($currencies as $currency) {
            if (ExchangeRateHistory::where('currency_code', $currency)->exists()) {
                continue;
            }

            $baseRate = 1.0;
            if ($currency === 'USD') {
                $baseRate = 1.0;
            } elseif ($currency === 'EUR') {
                $baseRate = 0.92;
            } elseif ($currency === 'GBP') {
                $baseRate = 0.78;
            } elseif ($currency === 'SGD') {
                $baseRate = 1.34;
            } elseif ($currency === 'IDR') {
                $baseRate = 16200.0;
            } elseif ($currency === 'MYR') {
                $baseRate = 4.70;
            } elseif ($currency === 'CNY') {
                $baseRate = 7.25;
            } elseif ($currency === 'JPY') {
                $baseRate = 155.0;
            } elseif ($currency === 'KRW') {
                $baseRate = 1370.0;
            } elseif ($currency === 'INR') {
                $baseRate = 83.5;
            } elseif ($currency === 'AUD') {
                $baseRate = 1.50;
            } else {
                $baseRate = rand(5, 200) / 10.0;
            }

            ExchangeRateHistory::create([
                'currency_code' => $currency,
                'rate_to_usd' => $baseRate,
                'fetched_at' => $yesterday,
            ]);

            $volatility = (rand(-30, 30) / 1000.0);
            $todayRate = $baseRate * (1 + $volatility);

            ExchangeRateHistory::create([
                'currency_code' => $currency,
                'rate_to_usd' => $todayRate,
                'fetched_at' => $now,
            ]);
        }

        // 2. Economics
        foreach ($countries as $country) {
            if (CountryEconomicsHistory::where('country_id', $country->id)->exists()) {
                continue;
            }

            $gdp = rand(10, 500) * 1000000000;
            $pop = rand(1, 80) * 1000000;
            $inflation = rand(100, 1000) / 100.0;
            
            $cCode = strtoupper($country->code);
            if ($cCode === 'USA') {
                $gdp = 27000000000000;
                $pop = 335000000;
                $inflation = 3.2;
            } elseif ($cCode === 'CHN') {
                $gdp = 18000000000000;
                $pop = 1410000000;
                $inflation = 2.1;
            } elseif ($cCode === 'JPN') {
                $gdp = 4200000000000;
                $pop = 125000000;
                $inflation = 2.5;
            } elseif ($cCode === 'DEU') {
                $gdp = 4400000000000;
                $pop = 84000000;
                $inflation = 3.5;
            } elseif ($cCode === 'IND') {
                $gdp = 3700000000000;
                $pop = 1420000000;
                $inflation = 5.1;
            } elseif ($cCode === 'IDN') {
                $gdp = 1370000000000;
                $pop = 277000000;
                $inflation = 4.2;
            }

            if (in_array($cCode, ['TUR', 'ARG', 'VEN', 'ZWE', 'IRN', 'SDN'])) {
                $inflation = rand(2500, 8000) / 100.0;
            }

            $exports = $gdp * (rand(15, 35) / 100.0);
            $imports = $gdp * (rand(18, 45) / 100.0);

            CountryEconomicsHistory::create([
                'country_id' => $country->id,
                'gdp' => $gdp,
                'inflation' => $inflation,
                'population' => $pop,
                'exports' => $exports,
                'imports' => $imports,
                'data_year' => 2025,
                'fetched_at' => $now,
            ]);
        }

        // 3. Weather
        foreach ($countries as $country) {
            if (WeatherHistory::where('country_id', $country->id)->exists()) {
                continue;
            }

            $lat = $country->latitude ?? rand(-40, 60);
            $lon = $country->longitude ?? rand(-120, 140);

            $absLat = abs($lat);
            if ($absLat < 23.5) {
                $temp = rand(240, 340) / 10.0;
                $rain = rand(0, 180) / 10.0;
            } else {
                $temp = (30 - $absLat) + (rand(-10, 10));
                $rain = rand(0, 120) / 10.0;
            }

            $wind = rand(50, 450) / 10.0;
            
            if (rand(1, 20) === 1) {
                $wind = rand(55, 85);
                $rain = rand(15, 30);
            }

            $windScore = min($wind / 60 * 100, 100);
            $rainScore = min($rain / 20 * 100, 100);
            $stormRisk = round(($windScore * 0.6) + ($rainScore * 0.4), 2);

            $condition = 'Clear';
            if ($wind > 50) {
                $condition = 'Storm';
            } elseif ($rain > 10) {
                $condition = 'Heavy Rain';
            } elseif ($rain > 0) {
                $condition = 'Rain';
            }

            WeatherHistory::create([
                'country_id' => $country->id,
                'temperature' => $temp,
                'rainfall' => $rain,
                'wind_speed' => $wind,
                'storm_risk' => $stormRisk,
                'weather_condition' => $condition,
                'fetched_at' => $now,
            ]);
        }

        // 4. News Articles & Sentiment
        $sentimentService = resolve(SentimentAnalyzerService::class);
        $mockArticles = [
            [
                'title' => 'Shanghai Port congestion worsens as Peak Season hits shipping corridors',
                'source' => 'Maritime Executive',
                'category' => 'port congestion',
                'description' => 'Container vessel dwell times at the Port of Shanghai have increased by 25% over the past week due to surging peak season volumes and labor constraints, triggering worries of global supply chain disruptions.',
            ],
            [
                'title' => 'Global shipping rates stabilize after months of volatility and supply chain shocks',
                'source' => 'Reuters Logistics',
                'category' => 'shipping',
                'description' => 'Container spot freight rates on major transpacific and Asia-Europe routes have begun to level off, providing much-needed stability for global retailers planning their inventory imports.',
            ],
            [
                'title' => 'Tensions in the Suez Canal force carriers to divert routes around Africa',
                'source' => 'Bloomberg Supply Chain',
                'category' => 'trade war',
                'description' => 'Major ocean carriers including Maersk and MSC continue to redirect container vessels around the Cape of Good Hope, adding 10-14 days to transit times and increasing fuel costs.',
            ],
            [
                'title' => 'Smart Port upgrades in Rotterdam reduce container handling delays by 15%',
                'source' => 'Tech Logistics Today',
                'category' => 'logistics',
                'description' => 'The implementation of automated guided vehicles and AI-powered yard planning at the Port of Rotterdam has significantly streamlined operations, reducing overall port congestion.',
            ],
            [
                'title' => 'US-China trade tensions flare as new tariffs loom on electrical goods',
                'source' => 'Financial Times',
                'category' => 'trade war',
                'description' => 'Importers are rushing to front-load shipments before the new round of tariffs takes effect next month, causing a temporary spike in freight demand and ocean transport costs.',
            ],
            [
                'title' => 'Inflation surges in South America, threatening purchasing power and local currencies',
                'source' => 'The Economist',
                'category' => 'inflation',
                'description' => 'Skyrocketing consumer prices and economic instability are causing steep currency depreciation in several nations, creating major risks for international trade and imports.',
            ],
            [
                'title' => 'Severe storm in the South China Sea causes shipping route diversions',
                'source' => 'Weather & Shipping news',
                'category' => 'shipping',
                'description' => 'A powerful tropical storm has forced ships to adjust their routes near Taiwan and the Philippines, leading to predicted ETA delays of 3 to 5 days for ports in Southeast Asia.',
            ],
            [
                'title' => 'Global fuel price decline lowers ocean freight surcharge costs for shippers',
                'source' => 'Journal of Commerce',
                'category' => 'logistics',
                'description' => 'A downward trend in crude oil prices has translated to lower bunker adjustment factors (BAF), bringing welcome relief to shipping margins and international supply chains.',
            ]
        ];

        foreach ($mockArticles as $art) {
            $exists = NewsArticle::where('title', $art['title'])->first();
            if ($exists) {
                if (!$exists->sentiment) {
                    $text = $exists->title . ' ' . ($exists->content_snippet ?? '');
                    $res = $sentimentService->analyzeText($text);
                    NewsSentiment::create([
                        'news_article_id' => $exists->id,
                        'positive_count' => $res['positive_count'],
                        'negative_count' => $res['negative_count'],
                        'sentiment_label' => $res['label'],
                        'sentiment_score' => $res['score'],
                    ]);
                }
                continue;
            }

            $article = NewsArticle::create([
                'title' => $art['title'],
                'source' => $art['source'],
                'url' => 'https://example.com/news/' . Str::slug($art['title']),
                'category' => $art['category'],
                'content_snippet' => $art['description'],
                'published_at' => $now,
            ]);

            $text = $article->title . ' ' . ($article->content_snippet ?? '');
            $res = $sentimentService->analyzeText($text);

            NewsSentiment::create([
                'news_article_id' => $article->id,
                'positive_count' => $res['positive_count'],
                'negative_count' => $res['negative_count'],
                'sentiment_label' => $res['label'],
                'sentiment_score' => $res['score'],
            ]);
        }

        // 5. Calculate Risk Scores
        $riskScoringService = resolve(RiskScoringService::class);
        $riskScoringService->calculateAllCountries();
    }
}
