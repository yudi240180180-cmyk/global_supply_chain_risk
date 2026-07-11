<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskWeight extends Model
{
    protected $fillable = [
        'component_name',
        'weight_percentage',
        'updated_by',
    ];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}