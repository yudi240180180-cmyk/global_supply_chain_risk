<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PositiveWord;

class PositiveWordSeeder extends Seeder
{
    public function run(): void
    {
        $words = [
            // Economic positives
            'growth', 'increase', 'profit', 'stable', 'improve', 'surge',
            'boost', 'recovery', 'rally', 'gain', 'expand', 'strong',
            'positive', 'rise', 'success', 'agreement', 'deal', 'partnership',
            'breakthrough', 'upgrade', 'resilient', 'efficient', 'record',
            'opportunity', 'thrive', 'optimistic', 'robust', 'accelerate',
            'benefit', 'advance', 'innovation', 'progress', 'stability',
            'confidence', 'expansion', 'flourish', 'prosper', 'achieve',
            'alliance', 'cooperation', 'reform', 'liberalize', 'sustainable',
            'surplus', 'open', 'investment', 'demand', 'strengthen',
            'healthy', 'upbeat', 'resolve', 'settlement', 'peace',
            // Supply chain positives
            'streamline', 'optimize', 'deliver', 'efficient', 'on-time',
            'capacity', 'increase', 'reopen', 'clear', 'restore',
            'resume', 'rebound', 'modernize', 'diversify', 'secure',
            'reliable', 'trusted', 'certified', 'approved', 'awarded',
        ];

        foreach ($words as $word) {
            PositiveWord::firstOrCreate(['word' => $word]);
        }

        $this->command->info('Positive words seeded: ' . count($words) . ' words.');
    }
}
