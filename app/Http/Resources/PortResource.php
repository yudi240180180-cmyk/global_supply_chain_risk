<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'locode' => $this->locode,

            'port_type' => $this->port_type,

            'status' => $this->status,

            'function' => $this->function,

            'outflows' => $this->outflows,

            'coordinates' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],

            'country' => $this->whenLoaded('country', function () {
                return [
                    'id' => $this->country->id,
                    'name' => $this->country->name,
                    'iso2' => $this->country->iso2 ?? null,
                    'iso3' => $this->country->iso3 ?? null,
                ];
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}