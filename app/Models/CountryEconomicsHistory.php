<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryEconomicsHistory extends Model
{
    protected $table = 'country_economics_history';

    protected $fillable = [
        'country_id',
        'gdp',
        'inflation',
        'population',
        'exports',
        'imports',
        'data_year',
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