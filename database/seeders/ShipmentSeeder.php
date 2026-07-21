<?php

namespace Database\Seeders;

use App\Models\Shipment;
use App\Models\Supplier;
use App\Models\Port;
use App\Models\User;
use App\Models\ShipmentStatusLog;
use App\Models\ShipmentRecommendation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ShipmentSeeder extends Seeder
{
    public function run(): void
    {
        $managers = User::where('role', 'import_manager')->get();
        if ($managers->isEmpty()) {
            return;
        }

        $suppliers = Supplier::with('country')->get();
        $ports     = Port::with('country')->get();
        if ($suppliers->isEmpty() || $ports->isEmpty()) {
            return;
        }

        // Tanjung Priok as the main destination port
        $tanjungPriok = Port::where('name', 'like', '%Priok%')
            ->orWhere('name', 'like', '%Jakarta%')
            ->first() ?? $ports->first();

        $cargos = [
            ['name' => 'Microchip Processors', 'weight' => 2.4, 'qty' => 500, 'type' => '20FT'],
            ['name' => 'OLED Screen Panels', 'weight' => 15.2, 'qty' => 1200, 'type' => '40FT'],
            ['name' => 'Lithium Battery Packs', 'weight' => 20.0, 'qty' => 800, 'type' => '40HC'],
            ['name' => 'Printed Circuit Boards', 'weight' => 8.5, 'qty' => 350, 'type' => '20FT'],
            ['name' => 'Solder Alloys & Pastes', 'weight' => 12.0, 'qty' => 150, 'type' => '20FT'],
        ];

        $statuses = [
            'Planning',
            'Ready',
            'Loading',
            'Departed',
            'At Sea',
            'Arrived',
            'Completed',
            'Delayed',
        ];

        // Seed 15 shipments
        for ($i = 1; $i <= 15; $i++) {
            $manager  = $managers->random();
            // try to match supplier with manager's company if possible
            $supplier = $suppliers->first(fn($s) => $s->company_name === $manager->company_name) ?? $suppliers->random();

            $originPort = Port::find($supplier->port_id) ?? $ports->where('id', '!=', $tanjungPriok->id)->random() ?? $ports->random();
            $destPort   = $tanjungPriok;

            $cargo = $cargos[array_rand($cargos)];
            $status = $statuses[array_rand($statuses)];

            $estDeparture = Carbon::now()->addDays(rand(-30, 10));
            $estArrival   = (clone $estDeparture)->addDays(rand(5, 12));

            $overallRisk = rand(15, 88);
            $riskLevel = match (true) {
                $overallRisk >= 65 => 'High',
                $overallRisk >= 35 => 'Medium',
                default            => 'Low',
            };

            $code = 'SC-' . $estDeparture->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);

            // Distance & cost estimations
            $lat1 = $originPort->latitude;
            $lon1 = $originPort->longitude;
            $lat2 = $destPort->latitude;
            $lon2 = $destPort->longitude;
            $earthRadius = 6371;
            $dLat = deg2rad($lat2 - $lat1);
            $dLon = deg2rad($lon2 - $lon1);
            $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = round($earthRadius * $c, 2);

            $estDays = max(1, ceil($distance / 650));
            $cost = round($distance * 2.25 * rand(1, 3), 2);

            $recommendationMessage = match($riskLevel) {
                'High'   => "⚠️ HIGH RISK — Delay shipment 48 hours. Storm warnings and news updates indicates disruption.",
                'Medium' => "🟡 MEDIUM RISK — Proceed with caution. General sea swells and minor port backlog.",
                default  => "✅ LOW RISK — Safe to proceed.",
            };

            $shipment = Shipment::create([
                'shipment_code'       => $code,
                'user_id'             => $manager->id,
                'supplier_id'         => $supplier->id,
                'origin_port_id'      => $originPort->id,
                'destination_port_id' => $destPort->id,
                'cargo_name'          => $cargo['name'],
                'cargo_weight'        => $cargo['weight'] * rand(1, 3),
                'container_count'     => rand(1, 10),
                'container_type'      => $cargo['type'],
                'quantity'            => $cargo['qty'] * rand(1, 5),
                'estimated_departure' => $estDeparture,
                'estimated_arrival'   => $estArrival,
                'actual_departure'    => in_array($status, ['Departed', 'At Sea', 'Arrived', 'Completed']) ? $estDeparture->addHours(rand(1, 4)) : null,
                'actual_arrival'      => $status === 'Completed' ? $estArrival->addHours(rand(-4, 12)) : null,
                'distance_km'         => $distance,
                'estimated_days'      => $estDays,
                'shipping_cost'       => $cost,
                'status'              => in_array($status, ['Planning', 'Ready', 'Loading']) ? 'Planning' : ($status === 'Delayed' ? 'Delayed' : ($status === 'Completed' ? 'Arrived' : 'In Transit')),
                'tracking_status'     => $status,
                'overall_risk_score'  => $overallRisk,
                'risk_level'          => $riskLevel,
                'weather_risk'        => rand(10, 95),
                'currency_risk'       => rand(10, 70),
                'economic_risk'       => rand(10, 60),
                'news_risk'           => rand(10, 80),
                'port_congestion_risk'=> rand(10, 90),
                'recommendation'      => $recommendationMessage,
            ]);

            // Add starting log
            ShipmentStatusLog::create([
                'shipment_id' => $shipment->id,
                'status'      => 'Planning',
                'notes'       => 'Shipment initialized in planner database.',
                'risk_at_log' => $overallRisk * 0.9,
                'logged_by'   => $manager->id,
                'logged_at'   => $estDeparture->subDays(5),
            ]);

            if ($status !== 'Planning') {
                ShipmentStatusLog::create([
                    'shipment_id' => $shipment->id,
                    'status'      => $status,
                    'notes'       => "Transit updated to phase: {$status}.",
                    'risk_at_log' => $overallRisk,
                    'logged_by'   => $manager->id,
                    'logged_at'   => Carbon::now()->subDays(rand(1, 5)),
                ]);
            }

            // Create recommendation
            ShipmentRecommendation::create([
                'shipment_id'         => $shipment->id,
                'recommendation_type' => $riskLevel === 'High' ? 'delay' : ($riskLevel === 'Medium' ? 'monitor' : 'proceed'),
                'title'               => $riskLevel === 'High' ? 'Delay Transit' : 'Clearance Approved',
                'message'             => $recommendationMessage,
                'risk_factors'        => [
                    ['icon' => '🌩️', 'label' => 'Weather/storm index alert'],
                    ['icon' => '⚓', 'label' => 'Port congestion queue limit'],
                ],
                'delay_hours'         => $riskLevel === 'High' ? 48 : null,
                'generated_at'        => $estDeparture->subDays(1),
            ]);
        }
    }
}
