<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentRecommendation extends Model
{
    protected $fillable = [
        'shipment_id', 'recommendation_type', 'title',
        'message', 'risk_factors', 'delay_hours', 'generated_at',
    ];

    protected $casts = [
        'risk_factors' => 'array',
        'generated_at' => 'datetime',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function getIconAttribute(): string
    {
        return match ($this->recommendation_type) {
            'proceed'  => '✅',
            'delay'    => '⏳',
            'reroute'  => '🔄',
            'cancel'   => '❌',
            'monitor'  => '👁️',
            default    => '📋',
        };
    }
}
