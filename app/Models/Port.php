<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    protected $fillable = [
        'name',
        'locode',
        'country_id',
        'latitude',
        'longitude',
        'port_type',
        'status',
        'function',
        'outflows',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'outflows' => 'float',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function suppliers()
{
    return $this->hasMany(Supplier::class);
}
}