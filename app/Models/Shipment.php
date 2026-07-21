<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'shipment_code',
        'user_id',
        'supplier_id',
        'origin_port_id',
        'destination_port_id',
        'cargo_name',
        'cargo_weight',
        'container_count',
        'container_type',
        'quantity',
        'shipping_cost',
        'estimated_days',
        'distance_km',
        'status',
        'tracking_status',
        'estimated_departure',
        'estimated_arrival',
        'actual_departure',
        'actual_arrival',
        'overall_risk_score',
        'risk_level',
        'weather_risk',
        'currency_risk',
        'economic_risk',
        'news_risk',
        'port_congestion_risk',
        'recommendation',
    ];

    protected $casts = [
        'estimated_departure' => 'datetime',
        'estimated_arrival'   => 'datetime',
        'actual_departure'    => 'datetime',
        'actual_arrival'      => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function originPort()
    {
        return $this->belongsTo(Port::class, 'origin_port_id');
    }

    public function destinationPort()
    {
        return $this->belongsTo(Port::class, 'destination_port_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function routes()
    {
        return $this->hasMany(ShipmentRoute::class)->orderBy('sequence');
    }

    public function statusLogs()
    {
        return $this->hasMany(ShipmentStatusLog::class)->orderByDesc('logged_at');
    }

    public function shippingCost()
    {
        return $this->hasOne(ShippingCost::class);
    }

    public function recommendations()
    {
        return $this->hasMany(ShipmentRecommendation::class)->latest('generated_at');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // ── Helpers ───────────────────────────────────────────────

    public function getTrackingProgressAttribute(): int
    {
        $steps = [
            'Planning'  => 0,
            'Ready'     => 15,
            'Loading'   => 30,
            'Departed'  => 45,
            'At Sea'    => 65,
            'Arrived'   => 85,
            'Completed' => 100,
            'Delayed'   => 50,
            'Cancelled' => 0,
        ];
        return $steps[$this->tracking_status] ?? 0;
    }

    public function getRiskColorAttribute(): string
    {
        return match ($this->risk_level) {
            'High'   => 'red',
            'Medium' => 'yellow',
            'Low'    => 'green',
            default  => 'slate',
        };
    }
}