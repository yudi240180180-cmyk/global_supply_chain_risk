<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
        'iso2',
        'iso3',
        'region',
        'subregion',
        'currency_code',
        'currency_name',
        'capital',
        'latitude',
        'longitude',
        'languages',
        'flag_url',
        'population',
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

    public function ports()
    {
        return $this->hasMany(Port::class);
    }

    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }

    public function latestRiskScore()
    {
        return $this->hasOne(RiskScore::class)->latestOfMany('calculated_at');
    }

    public function latestWeather()
    {
        return $this->hasOne(WeatherHistory::class)->latestOfMany('fetched_at');
    }

    public function latestEconomics()
    {
        return $this->hasOne(CountryEconomicsHistory::class)->latestOfMany('fetched_at');
    }
}