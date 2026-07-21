<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_name',
        'avatar_icon',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Role helpers ──────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isImportManager(): bool
    {
        return $this->role === 'import_manager';
    }

    public function getPortalRouteAttribute(): string
    {
        return $this->isAdmin() ? '/' : '/manager';
    }

    // ── Relations ─────────────────────────────────────────────

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
