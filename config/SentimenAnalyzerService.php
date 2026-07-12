<?php

namespace App\Services;

use App\Models\NewsArticle;
use App\Models\NewsSentiment;
use App\Models\PositiveWord;
use App\Models\NegativeWord;
use Illuminate\Support\Str;

class SentimentAnalyzerService
{
    protected array $positiveWords;
    protected array $negativeWords;

    public function __construct()
    {
        // Load kamus kata sekali saja ke memory (bukan query berulang tiap analisis)
        $this->positiveWords = PositiveWord::pluck('word')->map(fn ($w) => strtolower($w))->toArray();
        $this->negativeWords = NegativeWord::pluck('word')->map(fn ($w) => strtolower($w))->toArray();
    }

    /**
     * Analisis semua berita yang BELUM punya sentiment, simpan hasilnya.
     */
    public function analyzeAllPending(): int
    {
        $articles = NewsArticle::whereDoesntHave('sentiment')->get();
        $totalAnalyzed = 0;

        foreach ($articles as $article) {
            $text = $article->title . ' ' . ($article->content_snippet ?? '');
            $result = $this->analyzeText($text);

            NewsSentiment::create([
                'news_article_id' => $article->id,
                'positive_count' => $result['positive_count'],
                'negative_count' => $result['negative_count'],
                'sentiment_label' => $result['label'],
                'sentiment_score' => $result['score'],
            ]);

            $totalAnalyzed++;
        }

        return $totalAnalyzed;
    }

    /**
     * Logika inti: hitung kata positif vs negatif dalam teks.
     * INI BAGIAN YANG BISA/HARUS KAMU MODIFIKASI biar beda dari mahasiswa lain.
     */
    public function analyzeText(string $text): array
    {
        // Bersihkan teks: lowercase, hilangkan tanda baca, pecah jadi kata per kata
        $cleaned = Str::lower($text);
        $cleaned = preg_replace('/[^\w\s]/', ' ', $cleaned);
        $words = preg_split('/\s+/', trim($cleaned));

        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($words as $word) {
            if (in_array($word, $this->positiveWords)) {
                $positiveCount++;
            }
            if (in_array($word, $this->negativeWords)) {
                $negativeCount++;
            }
        }

        // Skor -1 (sangat negatif) sampai +1 (sangat positif)
        $total = $positiveCount + $negativeCount;
        $score = $total > 0
            ? round(($positiveCount - $negativeCount) / $total, 2)
            : 0;

        // Tentukan label — threshold ini contoh, bebas kamu ubah sendiri
        if ($score > 0.2) {
            $label = 'Positive';
        } elseif ($score < -0.2) {
            $label = 'Negative';
        } else {
            $label = 'Neutral';
        }

        return [
            'positive_count' => $positiveCount,
            'negative_count' => $negativeCount,
            'score' => $score,
            'label' => $label,
        ];
    }
}