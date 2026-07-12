<?php

namespace App\Services;

use App\Models\NewsArticle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsService
{
    protected string $baseUrl;
    protected string $apiKey;

    protected array $keywords = [
        'logistics',
        'shipping',
        'trade war',
        'port congestion',
        'inflation',
        'supply chain',
    ];

    public function __construct()
    {
        $this->baseUrl = config('services.gnews.url');
        $this->apiKey = config('services.gnews.key');
    }

    /**
     * Fetch berita untuk tiap keyword, simpan ke database.
     * Free tier GNews cuma 100 request/hari, jadi kita batasi jumlah keyword.
     */
    public function syncNews(): int
    {
        $totalSynced = 0;

        foreach ($this->keywords as $keyword) {
            $articles = $this->fetchNewsByKeyword($keyword);

            foreach ($articles as $article) {
                $saved = $this->saveArticle($article, $keyword);
                if ($saved) {
                    $totalSynced++;
                }
            }

            usleep(500000); // jeda 0.5 detik antar keyword
        }

        return $totalSynced;
    }

    protected function fetchNewsByKeyword(string $keyword): array
    {
        $response = Http::timeout(15)
            ->retry(2, 300)
            ->get("{$this->baseUrl}/search", [
                'q' => $keyword,
                'lang' => 'en',
                'max' => 10, // ambil 10 berita per keyword
                'token' => $this->apiKey,
            ]);

        if (! $response->successful()) {
            Log::error("GNews API gagal untuk keyword '{$keyword}': " . $response->body());
            return [];
        }

        return $response->json('articles') ?? [];
    }

    protected function saveArticle(array $article, string $category): ?NewsArticle
    {
        $url = $article['url'] ?? null;

        if (empty($url)) {
            return null;
        }

        // updateOrCreate berdasarkan url supaya tidak ada berita duplikat
        return NewsArticle::updateOrCreate(
            ['url' => $url],
            [
                'title' => $article['title'] ?? 'Untitled',
                'source' => $article['source']['name'] ?? null,
                'category' => $category,
                'content_snippet' => $article['description'] ?? null,
                'published_at' => $article['publishedAt'] ?? null,
            ]
        );
    }
}