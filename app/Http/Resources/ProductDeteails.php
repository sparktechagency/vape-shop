<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDeteails extends JsonResource
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
            'category_id' => $this->category_id,
            'user_id' => $this->user_id,
            'product_name' => $this->product_name,
            'slug' => $this->slug,
            'product_image' => $this->product_image,
            'product_price' => $this->product_price,
            'brand_name' => $this->brand_name,
            'product_description' => $this->product_description,
            'product_faqs' => $this->product_faqs,
            'stock' => $this->product_stock,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'is_hearted' => $this->is_hearted,
            'total_heart' => $this->total_heart,
            'average_rating' => $this->average_rating,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'user' => new UserResource($this->whenLoaded('user')),
            'related_products' => RelatedProductResource::collection($this->relatedProducts),
        ];
    }
}
