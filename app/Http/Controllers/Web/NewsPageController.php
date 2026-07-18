<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\Models\NewsSentiment;
use Illuminate\Http\Request;

class NewsPageController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsArticle::with('sentiment');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('sentiment')) {
            $query->whereHas('sentiment', fn ($q) =>
                $q->where('sentiment_label', $request->sentiment)
            );
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($b) =>
                $b->where('title', 'like', "%{$q}%")
                  ->orWhere('content_snippet', 'like', "%{$q}%")
                  ->orWhere('source', 'like', "%{$q}%")
            );
        }

        $articles = $query->orderByDesc('published_at')->paginate(15)->withQueryString();

        // Sentiment summary
        $total    = NewsSentiment::count();
        $positive = NewsSentiment::where('sentiment_label', 'Positive')->count();
        $negative = NewsSentiment::where('sentiment_label', 'Negative')->count();
        $neutral  = $total - $positive - $negative;

        $categories = NewsArticle::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('news.index', compact(
            'articles', 'total', 'positive', 'negative', 'neutral', 'categories'
        ));
    }
}
