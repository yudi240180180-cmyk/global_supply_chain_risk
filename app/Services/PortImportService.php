<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Port;

class PortImportService
{
    public function import(): array
    {
        $path = storage_path('app/ports/attributed_ports.geojson');

        if (!file_exists($path)) {
            throw new \Exception("GeoJSON file tidak ditemukan.");
        }

        $geojson = json_decode(file_get_contents($path), true);

        if (!isset($geojson['features'])) {
            throw new \Exception("Format GeoJSON tidak valid.");
        }

        $inserted = 0;
        $updated = 0;

        foreach ($geojson['features'] as $feature) {

            $properties = $feature['properties'];

            $coordinates = $feature['geometry']['coordinates'];

            $country = Country::where('name', $properties['Country'] ?? null)->first();

            $port = Port::updateOrCreate(

                [
                    'locode' => $properties['LOCODE'] ?? null
                ],

                [
                    'name' => $properties['Name'] ?? 'Unknown',

                    'country_id' => optional($country)->id,

                    'latitude' => $coordinates[1],

                    'longitude' => $coordinates[0],

                    'port_type' => 'International',

                    'status' => $properties['Status'] ?? null,

                    'function' => $properties['Function'] ?? null,

                    'outflows' => $properties['outflows'] ?? 0
                ]
            );

            if ($port->wasRecentlyCreated) {
                $inserted++;
            } else {
                $updated++;
            }
        }

        return [
            'inserted' => $inserted,
            'updated' => $updated,
            'total' => Port::count()
        ];
    }
}