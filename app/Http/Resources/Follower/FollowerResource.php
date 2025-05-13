<?php

namespace App\Http\Resources\Follower;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FollowerResource extends JsonResource
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
           'full_name' => $this->full_name,
           'email' => $this->email,
           'role' => $this->role,
           'role_label' => $this->role_label,
           'pivot' => [
            'follower_id' => $this->pivot->follower_id,
            'following_id' => $this->pivot->following_id,
            'total_followers' => User::find($this->id)->followers()->count(),
            'total_following' => User::find($this->id)->following()->count(),
            'created_at' => $this->pivot->created_at,
            'updated_at' => $this->pivot->updated_at,
           ],
        ];
    }
}
