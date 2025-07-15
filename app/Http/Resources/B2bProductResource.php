<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2bProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $b2bPricing = $this->b2bPricing;

        
        if (!$b2bPricing) {
            return [];
        }

        return [
            'product_id' => $this->id,
            'product_type' => get_class($this->resource),
            'product_name' => $this->product_name,
            'product_image' => $this->product_image,
            'slug' => $this->slug,
            'description' => $this->product_description,


            'b2b_details' => [
                'wholesale_price' => $b2bPricing->wholesale_price,
                'moq' => $b2bPricing->moq, // Minimum Order Quantity
            ],
        ];
    }
}
