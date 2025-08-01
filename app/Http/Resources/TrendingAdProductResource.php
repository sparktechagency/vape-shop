<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrendingAdProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // dd($this);
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'product_name' => $this->product->product_name,
            'product_image' => $this->product->product_image,
            'amount' => $this->amount,
            'preferred_duration' => $this->preferred_duration,
            'slot' => $this->slot,
            'status' => $this->status,
            'requested_at' => \Carbon\Carbon::parse($this->requested_at)->format('d-m-Y'),
            'requested_by' => $this->user->full_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'full_name' => $this->user->full_name,
                    'avatar' => $this->user->avatar,
                    'role' => $this->user->role,
                ];
            }),
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,

                ];
            }),
            'region' => $this->whenLoaded('region', function () {
                return [
                    'id' => $this->region->id,
                    'name' => $this->region->name,
                    'code' => $this->region->code,
                    'country' => $this->region->country ? [
                        'id' => $this->region->country->id,
                        'name' => $this->region->country->name,
                    ] : null,
                ];
            }),

        ];
    }
}
