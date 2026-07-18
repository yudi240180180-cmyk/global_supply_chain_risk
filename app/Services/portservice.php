<?php

namespace App\Services;

use App\Models\Port;

class PortService
{
    public function getAll(array $filters = [])
    {
        $query = Port::with('country');

        if (!empty($filters['country'])) {
            $query->whereHas('country', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['country'] . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('locode', 'like', "%{$search}%");
            });
        }
        if (!empty($filters['all'])) {
    return $query
        ->with('country')
        ->orderBy('name')
        ->get();
}

       return $query
    ->with('country')
    ->orderBy('name')
    ->paginate(20);
    }

    public function getById(int $id)
    {
        return Port::with('country')->findOrFail($id);
    }

    public function nearest(float $latitude, float $longitude)
    {
        return Port::selectRaw("
                *,
                (
                    6371 *
                    acos(
                        cos(radians(?))
                        * cos(radians(latitude))
                        * cos(radians(longitude) - radians(?))
                        +
                        sin(radians(?))
                        * sin(radians(latitude))
                    )
                ) AS distance
            ", [
                $latitude,
                $longitude,
                $latitude
            ])
            ->orderBy('distance')
            ->limit(20)
            ->get();
    }
}