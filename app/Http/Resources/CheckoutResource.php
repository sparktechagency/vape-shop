<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'checkout_id' => $this->checkout_group_id,
            'grand_total' => $this->grand_total,
            'overall_status' => $this->status,
            'checkout_date' => $this->created_at->format('d M, Y'),
            'sub_orders' => StoreOrderResource::collection($this->whenLoaded('orders')),
        ];
    }
}
