<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'item_name', 'quantity',
        'unit', 'unit_price', 'total_price', 'description',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
