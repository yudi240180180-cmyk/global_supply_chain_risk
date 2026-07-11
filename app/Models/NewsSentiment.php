<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsSentiment extends Model
{
    protected $fillable = [
        'news_article_id',
        'positive_count',
        'negative_count',
        'sentiment_label',
        'sentiment_score',
    ];

    public function newsArticle()
    {
        return $this->belongsTo(NewsArticle::class);
    }
}