<?php

namespace App\Services;

use App\Models\Port;

class ShipmentService
{

    /**
     * Hitung jarak antar pelabuhan
     */
    public function calculateDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float
    {

        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLon / 2) *
            sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Estimasi hari pelayaran
     */
    public function estimateDays(float $distance): int
    {

        $speedPerDay = 650;

        return max(1, ceil($distance / $speedPerDay));
    }

    /**
     * Hitung biaya
     */
    public function estimateCost(
        float $distance,
        int $container
    ): float
    {

        $costPerKm = 2.25;

        return round(
            $distance *
            $costPerKm *
            $container,
            2
        );
    }

    /**
     * Ambil data lengkap shipment
     */
    public function calculate(
        Port $origin,
        Port $destination,
        int $container
    ): array
    {

        $distance = $this->calculateDistance(

            $origin->latitude,
            $origin->longitude,

            $destination->latitude,
            $destination->longitude

        );

        return [

            'distance_km' => $distance,

            'estimated_days' => $this->estimateDays($distance),

            'shipping_cost' => $this->estimateCost(
                $distance,
                $container
            )

        ];

    }

}