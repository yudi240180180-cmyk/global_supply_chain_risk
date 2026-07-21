<?php

namespace App\Services;

use App\Models\Port;

class ShippingCostService
{
    // Base rate per km per container (USD)
    protected float $baseCostPerKmPerContainer = 2.25;

    // Insurance rate (% of cargo value)
    protected float $insuranceRate = 0.005; // 0.5%

    // Import tax rate by commodity (heuristic)
    protected array $importTaxRates = [
        'electronics'   => 0.075,  // 7.5%
        'textile'       => 0.125,  // 12.5%
        'automotive'    => 0.150,  // 15%
        'food'          => 0.100,  // 10%
        'chemicals'     => 0.080,  // 8%
        'machinery'     => 0.065,  // 6.5%
        'general'       => 0.085,  // 8.5%
    ];

    /**
     * Hitung estimasi total shipping cost
     */
    public function calculate(
        Port   $origin,
        Port   $destination,
        int    $containerCount,
        string $containerType = '20FT',
        float  $cargoValue    = 0,
        string $commodity     = 'general'
    ): array {
        $distance = $this->haversine(
            $origin->latitude, $origin->longitude,
            $destination->latitude, $destination->longitude
        );

        // Container size multiplier
        $containerMultiplier = match ($containerType) {
            '40FT'  => 1.8,
            '40HC'  => 2.0,
            default => 1.0, // 20FT
        };

        // 1. Ocean Freight
        $oceanFreight = round(
            $distance * $this->baseCostPerKmPerContainer * $containerCount * $containerMultiplier,
            2
        );

        // 2. Insurance (0.5% of cargo value, minimum $200)
        $insurance = $cargoValue > 0
            ? max(round($cargoValue * $this->insuranceRate, 2), 200)
            : round($oceanFreight * 0.03, 2); // fallback: 3% of freight

        // 3. Import Tax (heuristic based on commodity + destination)
        $taxRate    = $this->importTaxRates[$commodity] ?? $this->importTaxRates['general'];
        $importTax  = $cargoValue > 0
            ? round($cargoValue * $taxRate, 2)
            : round($oceanFreight * $taxRate, 2);

        // 4. Currency Adjustment (heuristic: 2-5% buffer)
        $currencyAdjustment = round(($oceanFreight + $insurance) * 0.03, 2);

        // 5. Handling Fee (per container)
        $handlingFee = $containerCount * 350; // $350/container

        // 6. Port Charges (origin + destination)
        $portCharges = $containerCount * 280;

        $total = $oceanFreight + $insurance + $importTax
               + $currencyAdjustment + $handlingFee + $portCharges;

        return [
            'distance_km'         => round($distance),
            'estimated_days'      => max(1, (int) ceil($distance / 650)),
            'ocean_freight'       => $oceanFreight,
            'insurance'           => $insurance,
            'import_tax'          => $importTax,
            'currency_adjustment' => $currencyAdjustment,
            'handling_fee'        => $handlingFee,
            'port_charges'        => $portCharges,
            'total_cost'          => round($total, 2),
            'currency_code'       => 'USD',
            'cargo_value'         => $cargoValue,
        ];
    }

    protected function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c    = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($R * $c, 2);
    }
}
