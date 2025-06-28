<?php

namespace App\Http\Resources;

use App\Http\Controllers\Product\TrendingProducts;
use App\Models\MostFollowerAd;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'payable_type' => class_basename($this->payable_type),
            'transaction_id' => $this->transaction_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'transaction_date' => $this->created_at->format('d M, Y h:i A'),
            'payment_details' => $this->getPaymentDetails(),
        ];
    }

    /**
     * Get the payment details based on the payable relationship.
     *
     * @return array|null
     */
    protected function getPaymentDetails()
    {

        if (!$this->relationLoaded('payable')) {
            return null;
        }

        if ($this->payable instanceof TrendingProducts) {
            return [
                'type' => 'Trending Product Ad',
                'product_name' => $this->payable->product->product_name ?? 'N/A',
                'user' => [
                    'name' => $this->payable->user->full_name ?? 'N/A',
                ]
            ];
        }

        if ($this->payable instanceof MostFollowerAd) {
            return [
                'type' => 'Follower Growth Ad',
                'user' => [
                    'name' => $this->payable->user->full_name ?? 'N/A',
                    'email' => $this->payable->user->email ?? 'N/A',
                ]
            ];
        }

        return ['type' => 'Unknown'];
    }
}
