<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentStatusLog extends Model
{
    protected $fillable = [
        'shipment_id', 'status', 'notes',
        'risk_at_log', 'logged_by', 'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function loggedBy()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
