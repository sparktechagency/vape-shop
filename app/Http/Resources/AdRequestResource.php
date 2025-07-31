<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $remainingDays = null;
        if ($this->status === 'approved' && $this->is_active === true && $this->end_date && $this->end_date->isFuture()) {
            $remainingDays = Carbon::now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false);
            $remainingDays = max(0, $remainingDays); // Ensure non-negative
        }
        return [
            'id' => $this->id,
            'product_id' => $this->when($this->product_id !== null, $this->product_id),
            'product_name' => $this->when($this->product_id, $this->product->product_name ?? null),
            'product_image' => $this->when($this->product_id, $this->product->product_image ?? null),
            'user_id' => $this->user_id ?? null,
            'requested_by' => $this->user->full_name ?? null,
            'requester_avatar' => $this->user->avatar ?? null,
            'requested_at' => $this->requested_at,
            'amount' => $this->amount ?? null,
            'approved_by' => $this->approvedBy->full_name ?? null,
            'rejected_by' => $this->rejectedBy->full_name ?? null,
            'rejection_reason' => $this->rejection_reason,
            'approved_at' => $this->approved_at,
            'rejected_at' => $this->rejected_at,
            'display_order' => $this->display_order,
            'status' => $this->status,
            'preferred_duration' => $this->preferred_duration,
            'is_active' => $this->is_active,
            'start_date' => $this->when($this->start_date !== null, $this->start_date),
            'end_date' => $this->when($this->end_date !== null, $this->end_date),
            'remaining_days' => $this->when($remainingDays !== null, $remainingDays),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
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
            'featured_article' => $this->whenLoaded('FeaturedArticle', function () {
                return [
                    'id' => $this->FeaturedArticle->id,
                    'title' => $this->FeaturedArticle->title,
                    'article_image' => $this->FeaturedArticle->article_image,
                    'content' => $this->FeaturedArticle->content,
                ];
            }),
        ];
    }
}
