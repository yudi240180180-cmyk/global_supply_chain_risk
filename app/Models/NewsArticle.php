<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsArticle extends Model
{
    protected $fillable = [
        'title',
        'source',
        'url',
        'category',
        'content_snippet',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function sentiment()
    {
        return $this->hasOne(NewsSentiment::class);
    }
}