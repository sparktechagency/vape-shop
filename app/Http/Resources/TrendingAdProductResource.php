<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrendingAdProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // dd($this);
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'product_name' => $this->product->product_name,
            'product_image' => $this->product->product_image,
            'amount' => $this->payments->first()?->amount,
            'status' => $this->status,
            'requested_at' => \Carbon\Carbon::parse($this->requested_at)->format('d-m-Y'),
            'requested_by' => $this->user->full_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
