<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RiskWeight;

class RiskWeightSeeder extends Seeder
{
    public function run(): void
    {
        $weights = [
            ['component_name' => 'weather', 'weight_percentage' => 25],
            ['component_name' => 'economic', 'weight_percentage' => 35],
            ['component_name' => 'currency', 'weight_percentage' => 15],
            ['component_name' => 'news', 'weight_percentage' => 25],
        ];

        foreach ($weights as $w) {
            RiskWeight::firstOrCreate(
                ['component_name' => $w['component_name']],
                ['weight_percentage' => $w['weight_percentage']]
            );
        }
    }
}