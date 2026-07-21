<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'port_id',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'supplier_type',
        'risk_level',
        'rating',
        'status',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function port()
    {
        return $this->belongsTo(Port::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}