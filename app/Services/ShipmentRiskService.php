<?php

namespace App\Services;

use App\Models\Country;
use App\Models\ExchangeRateHistory;
use App\Models\NewsSentiment;
use App\Models\Port;
use App\Models\Shipment;
use App\Models\ShipmentRecommendation;
use Illuminate\Support\Facades\Log;

class ShipmentRiskService
{
    /**
     * Hitung seluruh risk score untuk shipment dan simpan recommendation.
     */
    public function calculate(Shipment $shipment): array
    {
        try {
            $originPort = $shipment->originPort()->with('country')->first();
            $destPort   = $shipment->destinationPort()->with('country')->first();

            $weatherRisk    = $this->calcWeatherRisk($originPort?->country);
            $currencyRisk   = $this->calcCurrencyRisk($originPort?->country);
            $economicRisk   = $this->calcEconomicRisk($originPort?->country);
            $newsRisk       = $this->calcNewsRisk();
            $congestionRisk = $this->calcPortCongestionRisk($originPort, $destPort);

            $overall = $this->combineRisk(
                $weatherRisk, $currencyRisk,
                $economicRisk, $newsRisk, $congestionRisk
            );

            $riskLevel = match (true) {
                $overall >= 65 => 'High',
                $overall >= 35 => 'Medium',
                default        => 'Low',
            };

            $recommendation = $this->generateRecommendation(
                $shipment, $riskLevel, $overall,
                $weatherRisk, $currencyRisk, $economicRisk, $newsRisk, $congestionRisk
            );

            return [
                'overall_risk_score'  => round($overall, 2),
                'risk_level'          => $riskLevel,
                'weather_risk'        => round($weatherRisk, 2),
                'currency_risk'       => round($currencyRisk, 2),
                'economic_risk'       => round($economicRisk, 2),
                'news_risk'           => round($newsRisk, 2),
                'port_congestion_risk'=> round($congestionRisk, 2),
                'recommendation'      => $recommendation['message'],
                'recommendation_type' => $recommendation['type'],
                'delay_hours'         => $recommendation['delay_hours'],
                'risk_factors'        => $recommendation['factors'],
            ];
        } catch (\Throwable $e) {
            Log::error('ShipmentRiskService error: ' . $e->getMessage());
            return [
                'overall_risk_score'  => 30,
                'risk_level'          => 'Low',
                'weather_risk'        => 20,
                'currency_risk'       => 25,
                'economic_risk'       => 25,
                'news_risk'           => 20,
                'port_congestion_risk'=> 20,
                'recommendation'      => 'Insufficient data — proceed with standard monitoring.',
                'recommendation_type' => 'monitor',
                'delay_hours'         => null,
                'risk_factors'        => [],
            ];
        }
    }

    // ── Risk Component Calculators ────────────────────────────

    protected function calcWeatherRisk(?Country $country): float
    {
        if (! $country) return 25.0;

        $latest = $country->weatherHistory()->latest('fetched_at')->first();
        return $latest?->storm_risk ?? 25.0;
    }

    protected function calcCurrencyRisk(?Country $country): float
    {
        if (! $country?->currency_code) return 30.0;

        $history = ExchangeRateHistory::where('currency_code', $country->currency_code)
            ->orderByDesc('fetched_at')
            ->limit(3)
            ->get();

        if ($history->count() < 2) return 30.0;

        $latest   = $history[0]->rate_to_usd;
        $previous = $history[1]->rate_to_usd;

        if ($previous == 0) return 30.0;

        $pct = abs(($latest - $previous) / $previous) * 100;
        return round(min($pct * 20, 100), 2);
    }

    protected function calcEconomicRisk(?Country $country): float
    {
        if (! $country) return 25.0;

        $latest = $country->economics()->latest('fetched_at')->first();
        if (! $latest) return 25.0;

        $inflationScore = min((max($latest->inflation ?? 0, 0) / 25) * 100, 100);

        $tradeGapScore = 0;
        if ($latest->gdp && $latest->gdp > 0 && $latest->imports !== null && $latest->exports !== null) {
            $gap = abs($latest->imports - $latest->exports);
            $tradeGapScore = min(($gap / $latest->gdp) * 100 * 5, 100);
        }

        return round(($inflationScore * 0.6) + ($tradeGapScore * 0.4), 2);
    }

    protected function calcNewsRisk(): float
    {
        $total = NewsSentiment::count();
        if ($total === 0) return 20.0;

        $negative = NewsSentiment::where('sentiment_label', 'Negative')->count();
        return round(($negative / $total) * 100, 2);
    }

    protected function calcPortCongestionRisk(?Port $origin, ?Port $dest): float
    {
        // Heuristic: pelabuhan besar Asia Timur punya congestion lebih tinggi
        $highCongestion = ['Shanghai', 'Shenzhen', 'Ningbo', 'Guangzhou', 'Tianjin', 'Busan', 'Hong Kong'];
        $medCongestion  = ['Singapore', 'Port Klang', 'Tanjung Pelepas', 'Colombo', 'Jakarta'];

        $score = 20.0; // baseline
        foreach ([$origin, $dest] as $port) {
            if (! $port) continue;
            foreach ($highCongestion as $name) {
                if (str_contains(strtolower($port->name), strtolower($name))) {
                    $score += 20;
                    break;
                }
            }
            foreach ($medCongestion as $name) {
                if (str_contains(strtolower($port->name), strtolower($name))) {
                    $score += 10;
                    break;
                }
            }
        }

        return min($score, 100);
    }

    protected function combineRisk(
        float $weather,
        float $currency,
        float $economic,
        float $news,
        float $congestion
    ): float {
        // Bobot: economic 30%, weather 25%, news 20%, currency 15%, congestion 10%
        $weighted =
            ($economic   * 0.30) +
            ($weather    * 0.25) +
            ($news       * 0.20) +
            ($currency   * 0.15) +
            ($congestion * 0.10);

        // Amplification: jika 2+ komponen > 70, tambah 10%
        $highCount = collect([$weather, $currency, $economic, $news, $congestion])
            ->filter(fn($s) => $s > 70)->count();

        if ($highCount >= 2) {
            $weighted = min($weighted * 1.10, 100);
        }

        return $weighted;
    }

    // ── Recommendation Generator ──────────────────────────────

    protected function generateRecommendation(
        Shipment $shipment,
        string $riskLevel,
        float $overall,
        float $weather,
        float $currency,
        float $economic,
        float $news,
        float $congestion
    ): array {
        $factors = [];

        if ($weather > 60)    $factors[] = ['icon' => '🌩️', 'label' => 'High weather/storm risk detected'];
        if ($currency > 60)   $factors[] = ['icon' => '💱', 'label' => 'Currency volatility is elevated'];
        if ($economic > 60)   $factors[] = ['icon' => '📉', 'label' => 'Economic instability in origin country'];
        if ($news > 60)       $factors[] = ['icon' => '📰', 'label' => 'High negative news sentiment'];
        if ($congestion > 60) $factors[] = ['icon' => '⚓', 'label' => 'Port congestion risk is high'];

        if ($riskLevel === 'High') {
            $delay = ($weather > 70 || $congestion > 70) ? 48 : 24;
            return [
                'type'        => 'delay',
                'message'     => "⚠️ HIGH RISK — Delay shipment {$delay} hours. Overall risk score is {$overall}. " .
                                 implode(', ', array_column($factors, 'label')) . '.',
                'delay_hours' => $delay,
                'factors'     => $factors,
            ];
        }

        if ($riskLevel === 'Medium') {
            return [
                'type'        => 'monitor',
                'message'     => "🟡 MEDIUM RISK — Proceed with caution and monitor closely. Score: {$overall}.",
                'delay_hours' => null,
                'factors'     => $factors,
            ];
        }

        return [
            'type'        => 'proceed',
            'message'     => "✅ LOW RISK — Safe to proceed. Risk score: {$overall}.",
            'delay_hours' => null,
            'factors'     => $factors,
        ];
    }

    /**
     * Simpan risk result ke DB dan buat recommendation record
     */
    public function saveToShipment(Shipment $shipment, array $result): void
    {
        $shipment->update([
            'overall_risk_score'   => $result['overall_risk_score'],
            'risk_level'           => $result['risk_level'],
            'weather_risk'         => $result['weather_risk'],
            'currency_risk'        => $result['currency_risk'],
            'economic_risk'        => $result['economic_risk'],
            'news_risk'            => $result['news_risk'],
            'port_congestion_risk' => $result['port_congestion_risk'],
            'recommendation'       => $result['recommendation'],
        ]);

        ShipmentRecommendation::create([
            'shipment_id'         => $shipment->id,
            'recommendation_type' => $result['recommendation_type'],
            'title'               => ucfirst($result['recommendation_type']) . ' Shipment',
            'message'             => $result['recommendation'],
            'risk_factors'        => $result['risk_factors'],
            'delay_hours'         => $result['delay_hours'],
            'generated_at'        => now(),
        ]);
    }
}
