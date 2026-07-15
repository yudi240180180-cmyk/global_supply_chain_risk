<?php

namespace App\Services;

use App\Models\Country;
use App\Models\RiskScore;
use App\Models\RiskWeight;
use App\Models\NewsSentiment;
use Illuminate\Support\Facades\Log;
use Throwable;

class RiskScoringService
{
    protected array $weights;

    public function __construct()
    {
        $this->weights = RiskWeight::pluck('weight_percentage', 'component_name')->toArray();
    }

    public function calculateAllCountries(): int
{
    $countries = Country::all();
    $globalNewsScore = $this->calculateGlobalNewsRisk();
    $batchTimestamp = now(); // dibuat SEKALI, dipakai untuk semua negara di batch ini
    $totalCalculated = 0;

    foreach ($countries as $country) {
        try {
            $weatherScore = $this->calculateWeatherRisk($country);
            $economicScore = $this->calculateEconomicRisk($country);
            $currencyScore = $this->calculateCurrencyRisk($country);

            $result = $this->combineScores($weatherScore, $economicScore, $currencyScore, $globalNewsScore);

            RiskScore::create([
                'country_id' => $country->id,
                'weather_score' => $weatherScore,
                'inflation_score' => $economicScore,
                'currency_score' => $currencyScore,
                'news_score' => $globalNewsScore,
                'total_score' => $result['total'],
                'risk_level' => $result['level'],
                'calculated_at' => $batchTimestamp, // pakai timestamp yang sama untuk seluruh batch
            ]);

            $totalCalculated++;
        } catch (Throwable $e) {
            Log::error("Risk scoring gagal untuk {$country->name}: " . $e->getMessage());
            continue;
        }
    }

    return $totalCalculated;
}

    /**
     * Weather Risk: ambil storm_risk terbaru negara ini (sudah skala 0-100).
     */
    protected function calculateWeatherRisk(Country $country): float
    {
        $latest = $country->weatherHistory()->latest('fetched_at')->first();
        return $latest?->storm_risk ?? 0;
    }

    /**
     * Economic Risk: kombinasi inflasi (60%) + rasio defisit dagang (40%).
     * Beda dari spec yang cuma pakai inflasi mentah.
     */
    protected function calculateEconomicRisk(Country $country): float
    {
        $latest = $country->economics()->latest('fetched_at')->first();

        if (! $latest) {
            return 0;
        }

        // Normalisasi inflasi: asumsi inflasi 25%+ = risiko maksimal
        $inflationScore = min((max($latest->inflation ?? 0, 0) / 25) * 100, 100);

        // Rasio defisit dagang terhadap GDP: makin besar defisit, makin berisiko
        $tradeGapScore = 0;
        if ($latest->gdp && $latest->gdp > 0 && $latest->imports !== null && $latest->exports !== null) {
            $gap = abs($latest->imports - $latest->exports);
            $gapRatio = ($gap / $latest->gdp) * 100;
            $tradeGapScore = min($gapRatio * 5, 100); // asumsi gap 20% dari GDP = risiko maksimal
        }

        return round(($inflationScore * 0.6) + ($tradeGapScore * 0.4), 2);
    }

    /**
     * Currency Risk: volatilitas dari waktu ke waktu.
     * Kalau data history masih 1 (belum ada pembanding), kasih skor netral 30.
     */
    protected function calculateCurrencyRisk(Country $country): float
    {
        if (! $country->currency_code) {
            return 0;
        }

        $history = \App\Models\ExchangeRateHistory::where('currency_code', $country->currency_code)
            ->orderByDesc('fetched_at')
            ->limit(2)
            ->get();

        if ($history->count() < 2) {
            return 30; // netral, belum ada cukup data untuk hitung volatilitas
        }

        $latest = $history[0]->rate_to_usd;
        $previous = $history[1]->rate_to_usd;

        if ($previous == 0) {
            return 30;
        }

        $percentChange = abs(($latest - $previous) / $previous) * 100;

        // Asumsi perubahan 5%+ dalam 1 periode = risiko maksimal
        return round(min($percentChange * 20, 100), 2);
    }

    /**
     * News Risk global: persentase berita negatif dari total berita yang sudah dianalisis.
     */
    protected function calculateGlobalNewsRisk(): float
    {
        $total = NewsSentiment::count();

        if ($total === 0) {
            return 0;
        }

        $negative = NewsSentiment::where('sentiment_label', 'Negative')->count();

        return round(($negative / $total) * 100, 2);
    }

    /**
     * Gabungkan semua skor pakai bobot, lalu tambahkan Risk Amplification
     * kalau 2+ komponen sekaligus tinggi (>70).
     */
    protected function combineScores(float $weather, float $economic, float $currency, float $news): array
    {
        $weightedTotal =
            ($weather * ($this->weights['weather'] ?? 25) / 100) +
            ($economic * ($this->weights['economic'] ?? 35) / 100) +
            ($currency * ($this->weights['currency'] ?? 15) / 100) +
            ($news * ($this->weights['news'] ?? 25) / 100);

        // Hitung berapa komponen yang "tinggi" (>70)
        $highRiskCount = collect([$weather, $economic, $currency, $news])
            ->filter(fn ($score) => $score > 70)
            ->count();

        // Amplification: kalau 2+ komponen tinggi bersamaan, tambah 10%
        if ($highRiskCount >= 2) {
            $weightedTotal = min($weightedTotal * 1.1, 100);
        }

        $total = round($weightedTotal, 2);

        if ($total < 35) {
            $level = 'Low';
        } elseif ($total < 65) {
            $level = 'Medium';
        } else {
            $level = 'High';
        }

        return ['total' => $total, 'level' => $level];
    }
}