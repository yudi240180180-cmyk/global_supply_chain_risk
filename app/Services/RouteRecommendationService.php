<?php

namespace App\Services;

use App\Models\Port;
use App\Services\ShipmentService;

class RouteRecommendationService
{
    // Major transit hub ports (lat/lng hardcoded for reliability)
    protected array $hubs = [
        'Singapore' => ['lat' => 1.2897,  'lng' => 103.8501, 'name' => 'Port of Singapore', 'code' => 'SGP'],
        'HongKong'  => ['lat' => 22.3193, 'lng' => 114.1694, 'name' => 'Hong Kong Port',    'code' => 'HKG'],
        'Colombo'   => ['lat' => 6.9271,  'lng' => 79.8612,  'name' => 'Port of Colombo',   'code' => 'CMB'],
        'PortKlang' => ['lat' => 3.0000,  'lng' => 101.3667, 'name' => 'Port Klang',        'code' => 'PKL'],
        'Busan'     => ['lat' => 35.1800, 'lng' => 129.0750, 'name' => 'Port of Busan',     'code' => 'PUS'],
        'Dubai'     => ['lat' => 25.2048, 'lng' => 55.2708,  'name' => 'Jebel Ali Port',    'code' => 'DXB'],
        'Rotterdam' => ['lat' => 51.9225, 'lng' => 4.4792,   'name' => 'Port of Rotterdam', 'code' => 'RTM'],
        'Kaohsiung' => ['lat' => 22.6273, 'lng' => 120.3014, 'name' => 'Port of Kaohsiung', 'code' => 'KHH'],
    ];

    protected ShipmentService $shipmentSvc;

    public function __construct(ShipmentService $shipmentSvc)
    {
        $this->shipmentSvc = $shipmentSvc;
    }

    /**
     * Rekomendasikan 3 rute dari origin ke destination.
     * Return array rute dengan waypoints, distance, ETA, risk.
     */
    public function recommend(Port $origin, Port $destination): array
    {
        $routes   = [];
        $waypoints = $this->selectWaypoints($origin, $destination);

        // Route 1: Recommended (via best hub)
        if (count($waypoints) > 0) {
            $routes[] = $this->buildRoute('Recommended Route', $origin, $destination, [$waypoints[0]], 'recommended');
        }

        // Route 2: Alternative (via second hub if exists)
        if (count($waypoints) > 1) {
            $routes[] = $this->buildRoute('Alternative Route A', $origin, $destination, [$waypoints[1]], 'alternative_1');
        }

        // Route 3: Direct (no hub)
        $routes[] = $this->buildRoute('Direct Route', $origin, $destination, [], 'direct');

        // Sort by risk score ascending
        usort($routes, fn($a, $b) => $a['risk_score'] <=> $b['risk_score']);

        return $routes;
    }

    protected function selectWaypoints(Port $origin, Port $dest): array
    {
        // Tentukan hub berdasarkan posisi geografis origin dan destination
        $midLat = ($origin->latitude  + $dest->latitude)  / 2;
        $midLng = ($origin->longitude + $dest->longitude) / 2;

        // Cari 2 hub terdekat dari titik tengah rute
        $scored = [];
        foreach ($this->hubs as $key => $hub) {
            $dist = $this->haversine($midLat, $midLng, $hub['lat'], $hub['lng']);
            // Pastikan hub tidak terlalu dekat dengan origin/dest
            $fromOrigin = $this->haversine($origin->latitude, $origin->longitude, $hub['lat'], $hub['lng']);
            $fromDest   = $this->haversine($dest->latitude,   $dest->longitude,   $hub['lat'], $hub['lng']);

            if ($fromOrigin < 200 || $fromDest < 200) continue; // skip kalau terlalu dekat

            $scored[$key] = $dist;
        }

        asort($scored);
        return array_keys(array_slice($scored, 0, 2, true));
    }

    protected function buildRoute(
        string $label,
        Port $origin,
        Port $destination,
        array $hubKeys,
        string $type
    ): array {
        $stops = [];

        // Origin
        $stops[] = [
            'name'     => $origin->name,
            'country'  => $origin->country?->name ?? '',
            'flag'     => $origin->country?->flag ?? '🌍',
            'lat'      => $origin->latitude,
            'lng'      => $origin->longitude,
            'is_hub'   => false,
            'sequence' => 1,
        ];

        $seq = 2;
        foreach ($hubKeys as $hubKey) {
            if (isset($this->hubs[$hubKey])) {
                $hub     = $this->hubs[$hubKey];
                $stops[] = [
                    'name'     => $hub['name'],
                    'country'  => '',
                    'flag'     => '🔄',
                    'lat'      => $hub['lat'],
                    'lng'      => $hub['lng'],
                    'is_hub'   => true,
                    'sequence' => $seq++,
                ];
            }
        }

        // Destination
        $stops[] = [
            'name'     => $destination->name,
            'country'  => $destination->country?->name ?? '',
            'flag'     => $destination->country?->flag ?? '🌍',
            'lat'      => $destination->latitude,
            'lng'      => $destination->longitude,
            'is_hub'   => false,
            'sequence' => $seq,
        ];

        // Hitung total distance
        $totalDistance = 0;
        for ($i = 0; $i < count($stops) - 1; $i++) {
            $totalDistance += $this->haversine(
                $stops[$i]['lat'],  $stops[$i]['lng'],
                $stops[$i+1]['lat'], $stops[$i+1]['lng']
            );
        }

        $estimatedDays = max(1, ceil($totalDistance / 650));
        $riskScore     = $this->estimateRouteRisk($type, count($hubKeys), $totalDistance);

        // Risk dari cuaca & port kongesti
        $riskLevel = match (true) {
            $riskScore >= 65 => 'High',
            $riskScore >= 35 => 'Medium',
            default          => 'Low',
        };

        return [
            'label'          => $label,
            'type'           => $type,
            'stops'          => $stops,
            'total_distance' => round($totalDistance),
            'estimated_days' => $estimatedDays,
            'risk_score'     => round($riskScore, 1),
            'risk_level'     => $riskLevel,
            'hub_count'      => count($hubKeys),
            'est_cost_usd'   => round($totalDistance * 2.25 * max(1, 1), 0),
        ];
    }

    protected function estimateRouteRisk(string $type, int $hubs, float $distance): float
    {
        // Direct route = less time at sea but fewer options
        // Via hubs = more reliable but longer
        $base = match ($type) {
            'direct'        => 40.0,
            'recommended'   => 30.0,
            'alternative_1' => 35.0,
            default         => 38.0,
        };

        // Jarak lebih jauh = risiko lebih tinggi sedikit
        $distancePenalty = min($distance / 1000, 15);

        return round($base + $distancePenalty, 1);
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
