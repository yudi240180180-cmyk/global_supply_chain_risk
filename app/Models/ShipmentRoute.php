<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentRoute extends Model
{
    protected $fillable = [
        'shipment_id', 'sequence', 'port_id',
        'eta', 'etd', 'distance_from_prev_km',
        'risk_score', 'route_type',
    ];

    protected $casts = [
        'eta' => 'datetime',
        'etd' => 'datetime',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function port()
    {
        return $this->belongsTo(Port::class);
    }
}
