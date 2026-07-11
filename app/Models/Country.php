<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
        'region',
        'subregion',
        'currency_code',
        'currency_name',
        'capital',
        'latitude',
        'longitude',
        'languages',
        'flag_url',
    ];

    protected $casts = [
        'languages' => 'array',
    ];

    public function economics()
    {
        return $this->hasMany(CountryEconomicsHistory::class);
    }

    public function weatherHistory()
    {
        return $this->hasMany(WeatherHistory::class);
    }

    public function riskScores()
    {
        return $this->hasMany(RiskScore::class);
    }
}