<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NegativeWord;

class NegativeWordSeeder extends Seeder
{
    public function run(): void
    {
        $words = [
            // Geopolitical & conflict
            'war', 'conflict', 'tension', 'sanctions', 'embargo', 'blockade',
            'attack', 'terrorism', 'piracy', 'hijack', 'dispute', 'protest',
            'riot', 'unrest', 'instability', 'threat', 'crisis',
            // Economic negatives
            'inflation', 'recession', 'collapse', 'default', 'debt',
            'deficit', 'bankruptcy', 'downturn', 'slump', 'crash', 'plunge',
            'decline', 'fall', 'drop', 'weak', 'loss', 'layoffs',
            'unemployment', 'poverty', 'devaluation', 'depreciation',
            'contraction', 'slowdown', 'downgrade', 'volatile', 'uncertainty',
            'corruption', 'fraud', 'scandal', 'fine', 'penalty', 'ban',
            // Logistics & supply chain negatives
            'delay', 'disruption', 'shortage', 'bottleneck', 'congestion',
            'backlog', 'strike', 'halt', 'cancel', 'suspend', 'restrict',
            'damage', 'accident', 'explosion', 'fire', 'theft', 'crime',
            // Natural disasters
            'disaster', 'flood', 'earthquake', 'storm', 'hurricane',
            'typhoon', 'drought', 'famine', 'pandemic', 'outbreak',
            'contamination', 'pollution', 'destroy', 'collapse',
        ];

        foreach ($words as $word) {
            NegativeWord::firstOrCreate(['word' => $word]);
        }

        $this->command->info('Negative words seeded: ' . count($words) . ' words.');
    }
}
