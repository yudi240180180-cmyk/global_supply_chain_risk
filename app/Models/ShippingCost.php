<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingCost extends Model
{
    protected $fillable = [
        'shipment_id', 'ocean_freight', 'insurance',
        'import_tax', 'currency_adjustment', 'handling_fee',
        'port_charges', 'total_cost', 'currency_code', 'cargo_value',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
