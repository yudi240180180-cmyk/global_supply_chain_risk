<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NegativeWord;

class NegativeWordSeeder extends Seeder
{
    public function run(): void
    {
        $words = [
            'war', 'crisis', 'inflation', 'delay', 'disaster', 'decline',
            'recession', 'collapse', 'conflict', 'shortage', 'disruption',
            'sanctions', 'default', 'debt', 'unemployment', 'volatile',
            'plunge', 'crash', 'tension', 'risk', 'threat', 'concern',
            'slump', 'fall', 'drop', 'weak', 'loss', 'deficit', 'strike',
            'protest', 'instability', 'uncertainty', 'downturn', 'layoffs',
            'bankruptcy',
        ];

        foreach ($words as $word) {
            NegativeWord::firstOrCreate(['word' => $word]);
        }
    }
}