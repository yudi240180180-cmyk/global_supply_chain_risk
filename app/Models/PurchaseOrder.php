<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number', 'user_id', 'supplier_id', 'shipment_id',
        'status', 'order_date', 'expected_date',
        'total_amount', 'currency_code', 'notes',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'expected_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'Draft'     => 'slate',
            'Approved'  => 'blue',
            'Shipped'   => 'violet',
            'Completed' => 'green',
            'Cancelled' => 'red',
            default     => 'slate',
        };
    }
}
