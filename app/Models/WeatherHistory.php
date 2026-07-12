<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherHistory extends Model
{
    protected $table = 'weather_history';

    protected $fillable = [
        'country_id',
        'temperature',
        'rainfall',
        'wind_speed',
        'storm_risk',
        'weather_condition',
        'fetched_at',
    ];

    protected $casts = [
        'fetched_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}