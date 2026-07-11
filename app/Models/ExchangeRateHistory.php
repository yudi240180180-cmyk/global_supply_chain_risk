<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRateHistory extends Model
{
    protected $fillable = [
        'currency_code',
        'rate_to_usd',
        'fetched_at',
    ];

    protected $casts = [
        'fetched_at' => 'datetime',
    ];
}