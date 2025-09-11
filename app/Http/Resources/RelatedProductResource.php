<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RelatedProductResource extends JsonResource
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
            'user_id' => $this->user_id,
            'category_id' => $this->category_id,
            'product_name' => $this->product_name,
            'product_image' => $this->product_image,
            'product_price' => $this->product_price,
            'average_rating' => $this->average_rating,
        ];
    }
}
