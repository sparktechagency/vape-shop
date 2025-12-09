<?php

namespace App\Http\Resources;

use App\Enums\UserRole\Role;
use Carbon\Carbon;
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
            'ein' => $this->ein,
            'total_followers' => $this->total_followers,
            'total_following' => $this->total_following,
            'is_following' => $this->is_following,
            'avg_rating' => $this->avg_rating,
            'total_reviews' => $this->total_reviews,
            'is_favourite' => $this->is_favourite,
            'is_banned' => $this->is_banned,
            'is_suspended' => $this->is_suspended,
            'tax_percentage' => $this->tax_percentage,
            'shipping_cost' => $this->shipping_cost,
            'address' => $this->whenLoaded('address', function () {
                return [
                    'address_id' => $this->address->id ?? null,
                    'address' => $this->address->address ?? null,
                    'zip' => $this->address->zip ?? null,
                    'region_id' => $this->address->region->id ?? null,
                    'region' => $this->address->region->name ?? null,
                    'country' => $this->address->region->country->name ?? null,
                ];
            }),

            $this->mergeWhen($this->relationLoaded('subscriptions') && $this->subscriptions->isNotEmpty(), function () {
                $latestSubscription = $this->subscriptions->last();

                $endsAt = Carbon::parse($latestSubscription->ends_at);
                $remainingDays = (int) max(0, ceil(now()->floatDiffInDays($endsAt, false)));

                return [
                    'invoice_status' => $latestSubscription->invoice_status,
                    'remaining_days' => $remainingDays,
                ];
            }),

            $this->mergeWhen($this->relationLoaded('favourites'), function () {

                [$favouriteStores, $favouriteBrands] = $this->favourites->partition(function ($favoriteUser) {
                    return $favoriteUser->role === Role::STORE->value;
                });

                return [
                    'favourite_stores' => FavouriteUserResource::collection($favouriteStores),
                    'favourite_brands' => FavouriteUserResource::collection($favouriteBrands),
                ];
            }),
        ];
    }
}
