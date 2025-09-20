<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NearbyStoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $addressable = $this->resource->addressable;

        if (!$addressable) {
            return [];
        }
        return [
            'id' => $addressable->id,
            'full_name' => $addressable->full_name,
            'email' => $addressable->email,
            'role' => $addressable->role,
            'role_label' => $addressable->role_label,
            'avatar' => $addressable->avatar,
            'phone' => $addressable->phone,
            'is_favourite' => $addressable->is_favourite ?? false,
            'avg_rating' => $addressable->avg_rating ?? 0,
            'distance' => $this->when(isset($this->distance), function () {
                if ($this->distance < 1000) {
                    return round($this->distance, 2) . ' m';
                }
                return round($this->distance / 1000, 2) . ' km';
            }),


            'address' => [
                'id' => $this->id,
                'address' => $this->address,
                'zip_code' => $this->zip_code,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'location' => $this->location,
            ],
        ];
    }
}
