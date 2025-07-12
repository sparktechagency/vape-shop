<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'order_id' => $this->id,
            'status' => $this->status,
            'sub_total' => $this->subtotal,
            'order_date' => $this->created_at->format('d M, Y'),
            'customer' => [
                'name' =>$this->whenLoaded('checkout', $this->checkout->customer_name) ?? $this->user->full_name,
                'email' => $this->whenLoaded('checkout', function () {
                    return $this->checkout->customer_email ?? $this->user->email;
                }),
                'dob' => $this->whenLoaded('checkout', function () {
                    $checkout = $this->checkout ?? null;
                    $user = $this->user ?? null;
                    return ($checkout && $checkout->customer_dob)
                        ? $checkout->customer_dob->format('d M, Y')
                        : (($user && $user->dob)
                            ? $user->dob->format('d M, Y')
                            : null);
                }),
                'address' =>$this->whenLoaded('checkout', $this->checkout->customer_address) ?? $this->whenLoaded('checkout', $this->checkout->customer_address),
            ],
            // 'address' => $this->whenLoaded('checkout', $this->checkout->customer_address),
            'order_items' => OrderItemResource::collection($this->whenLoaded('OrderItems')),
        ];
    }
}
