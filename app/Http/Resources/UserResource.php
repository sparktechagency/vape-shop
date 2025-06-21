<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'dob' => $this->dob,
            'email' => $this->email,
            'role' => $this->role,
            'avatar' => $this->avatar,
            'phone' => $this->phone,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'role_label' => $this->role_label,
            'full_name' => $this->full_name,
            'total_followers' => $this->total_followers,
            'total_following' => $this->total_following,
            'is_following' => $this->is_following,
            'avg_rating' => $this->avg_rating,
            'total_reviews' => $this->total_reviews,
            'is_favourite' => $this->is_favourite,
        ];
    }
}
