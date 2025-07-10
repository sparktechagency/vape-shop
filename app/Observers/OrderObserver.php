<?php

namespace App\Observers;

use App\Models\Checkout;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
   public function updated(Order $order): void
    {

        if ($order->isDirty('status')) {
            $this->updateCheckoutStatus($order->checkout);
        }
    }


     protected function updateCheckoutStatus(Checkout $checkout)
    {

        $orderStatuses = $checkout->orders()->pluck('status');

        // dd($orderStatuses);
        $statusCounts = $orderStatuses->countBy();

        $totalOrders = $orderStatuses->count();

        $pendingCount = $statusCounts->get('pending', 0);
        $acceptedCount = $statusCounts->get('accepted', 0);
        $rejectedCount = $statusCounts->get('rejected', 0);
        $deliveredCount = $statusCounts->get('delivered', 0);

        $newStatus = 'pending';

        if ($rejectedCount === $totalOrders) {
            $newStatus = 'cancelled';
        } elseif ($pendingCount === 0) {
            if ($acceptedCount > 0 || $deliveredCount > 0) {
                $newStatus = 'completed';
            } else {
                $newStatus = 'cancelled';
            }
        } elseif ($acceptedCount > 0 || $deliveredCount > 0) {

            $newStatus = 'partially_accepted';
        } else {

            $newStatus = 'pending';
        }


        if ($checkout->status !== $newStatus) {
            $checkout->status = $newStatus;
            $checkout->save();
            Log::info("Checkout status updated to: {$newStatus} for Checkout ID: {$checkout->id}");
        }
    }
}
