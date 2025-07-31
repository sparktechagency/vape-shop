<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdFeaturedResourc extends JsonResource
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
            'featured_article_id' => $this->featured_article_id,
            'region_id' => $this->region_id,
            'preferred_duration' => $this->preferred_duration,
            'amount' => $this->amount,
            'slot' => $this->slot,
            'user_id' => $this->user_id,
            'requested_by' => $this->user ? $this->user->name : null,
            'requested_at' => \Carbon\Carbon::parse($this->requested_at)->format('d-m-Y'),
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
            'featured_article' => $this->whenLoaded('featuredArticle', function () {
                return [
                    'id' => $this->featuredArticle->id,
                    'title' => $this->featuredArticle->title,
                    'image' => $this->featuredArticle->article_image,
                ];
            }),
        ];
    }
}
