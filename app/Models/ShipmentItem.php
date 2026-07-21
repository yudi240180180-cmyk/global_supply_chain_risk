<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentItem extends Model
{
    protected $fillable = [
        'shipment_id', 'item_name', 'quantity',
        'unit_weight', 'total_weight',
        'unit_price', 'total_price', 'hs_code',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
