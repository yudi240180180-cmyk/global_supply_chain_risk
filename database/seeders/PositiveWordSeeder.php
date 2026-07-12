<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PositiveWord;

class PositiveWordSeeder extends Seeder
{
    public function run(): void
    {
        $words = [
            'growth', 'increase', 'profit', 'stable', 'improve', 'surge',
            'boost', 'recovery', 'rally', 'gain', 'expand', 'strong',
            'positive', 'rise', 'success', 'agreement', 'deal', 'partnership',
            'breakthrough', 'upgrade', 'resilient', 'efficient', 'record',
            'opportunity', 'thrive', 'optimistic', 'robust', 'accelerate',
            'benefit', 'advance', 'innovation', 'progress', 'stability',
            'confidence', 'expansion',
        ];

        foreach ($words as $word) {
            PositiveWord::firstOrCreate(['word' => $word]);
        }
    }
}