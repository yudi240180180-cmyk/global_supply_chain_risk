<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\Models\NewsSentiment;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * GET /api/news
     *
     * Query Params:
     * ?category=
     * ?q=
     * ?sentiment=
     * ?from=
     * ?to=
     * ?sort=asc|desc
     */
    public function index(Request $request)
    {
        $query = NewsArticle::with('sentiment');

        // Filter kategori
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->filled('q')) {
            $q = $request->q;

            $query->where(function ($builder) use ($q) {
                $builder->where('title', 'like', "%{$q}%")
                    ->orWhere('content_snippet', 'like', "%{$q}%")
                    ->orWhere('source', 'like', "%{$q}%");
            });
        }

        // Filter sentiment
        if ($request->filled('sentiment')) {
            $query->whereHas('sentiment', function ($builder) use ($request) {
                $builder->where('sentiment_label', $request->sentiment);
            });
        }

        // Filter tanggal
        if ($request->filled('from')) {
            $query->whereDate('published_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('published_at', '<=', $request->to);
        }

        // Sorting
        $sort = strtolower($request->get('sort', 'desc'));

        $query->orderBy(
            'published_at',
            $sort === 'asc' ? 'asc' : 'desc'
        );

        $articles = $query->paginate(20);

        return response()->json(
            $articles->through(function ($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'source' => $article->source,
                    'category' => $article->category,
                    'published_at' => optional($article->published_at)->toDateTimeString(),
                    'content_snippet' => $article->content_snippet,
                    'url' => $article->url,

                    'sentiment' => $article->sentiment ? [
                        'positive_count' => $article->sentiment->positive_count,
                        'negative_count' => $article->sentiment->negative_count,
                        'sentiment_label' => $article->sentiment->sentiment_label,
                        'sentiment_score' => $article->sentiment->sentiment_score,
                    ] : null,
                ];
            })
        );
    }

    /**
     * GET /api/news/{id}
     */
    public function show($id)
    {
        $article = NewsArticle::with('sentiment')->find($id);

        if (! $article) {
            return response()->json([
                'message' => 'News article not found'
            ], 404);
        }

        return response()->json([
            'id' => $article->id,
            'title' => $article->title,
            'source' => $article->source,
            'category' => $article->category,
            'published_at' => optional($article->published_at)->toDateTimeString(),
            'content_snippet' => $article->content_snippet,
            'url' => $article->url,

            'sentiment' => $article->sentiment ? [
                'positive_count' => $article->sentiment->positive_count,
                'negative_count' => $article->sentiment->negative_count,
                'sentiment_label' => $article->sentiment->sentiment_label,
                'sentiment_score' => $article->sentiment->sentiment_score,
            ] : null,
        ]);
    }

    /**
     * GET /api/news/summary
     */
    public function summary()
    {
        return response()->json([
            'total_articles' => NewsArticle::count(),
            'positive' => NewsSentiment::where('sentiment_label', 'Positive')->count(),
            'negative' => NewsSentiment::where('sentiment_label', 'Negative')->count(),
            'neutral' => NewsSentiment::where('sentiment_label', 'Neutral')->count(),
        ]);
    }
}