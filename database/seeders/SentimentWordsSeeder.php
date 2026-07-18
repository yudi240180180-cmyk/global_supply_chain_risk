<?php

namespace Database\Seeders;

use App\Models\PositiveWord;
use App\Models\NegativeWord;
use Illuminate\Database\Seeder;

class SentimentWordsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Positive words ─────────────────────────────────────────────────
        $positiveWords = [
            'growth', 'increase', 'profit', 'stable', 'improve', 'gain',
            'recovery', 'expansion', 'boost', 'surge', 'rise', 'advance',
            'progress', 'benefit', 'success', 'strengthen', 'thrive',
            'opportunity', 'positive', 'optimistic', 'upbeat', 'flourish',
            'prosperous', 'robust', 'healthy', 'strong', 'upgrade',
            'investment', 'innovation', 'efficient', 'accelerate', 'achieve',
            'agreement', 'alliance', 'cooperation', 'deal', 'partnership',
            'peace', 'resolve', 'settlement', 'stability', 'sustainable',
            'surplus', 'trade', 'reform', 'opening', 'liberalize',
            'export', 'import', 'demand', 'supply', 'open', 'confidence',
        ];

        // ── Negative words ─────────────────────────────────────────────────
        $negativeWords = [
            'war', 'crisis', 'inflation', 'delay', 'disaster', 'conflict',
            'recession', 'decline', 'fall', 'drop', 'loss', 'collapse',
            'bankruptcy', 'shortage', 'disruption', 'sanction', 'embargo',
            'blockade', 'congestion', 'strike', 'protest', 'riot', 'unrest',
            'tension', 'dispute', 'tariff', 'threat', 'risk', 'volatility',
            'uncertainty', 'slowdown', 'contraction', 'deficit', 'debt',
            'default', 'devaluation', 'depreciation', 'downturn', 'slump',
            'unemployment', 'poverty', 'corruption', 'fraud', 'scandal',
            'accident', 'explosion', 'flood', 'earthquake', 'storm',
            'hurricane', 'typhoon', 'drought', 'famine', 'pandemic',
            'outbreak', 'contamination', 'pollution', 'damage', 'destroy',
            'attack', 'terrorism', 'piracy', 'hijack', 'theft', 'crime',
            'penalty', 'fine', 'ban', 'restrict', 'halt', 'cancel',
            'suspend', 'delay', 'bottleneck', 'congested', 'backlog',
            'decrease', 'reduce', 'cut', 'downgrade', 'warning', 'alert',
        ];

        PositiveWord::truncate();
        NegativeWord::truncate();

        foreach ($positiveWords as $word) {
            PositiveWord::create(['word' => $word]);
        }

        foreach ($negativeWords as $word) {
            NegativeWord::create(['word' => $word]);
        }

        $this->command->info('Sentiment words seeded: ' . count($positiveWords) . ' positive, ' . count($negativeWords) . ' negative.');
    }
}
