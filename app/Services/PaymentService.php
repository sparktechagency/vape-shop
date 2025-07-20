<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use App\Interfaces\PaymentRepositoryInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PaymentService
{
    protected $paymentGateway;
    protected $paymentRepository;

    public function __construct(PaymentGatewayInterface $paymentGateway, PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentGateway = $paymentGateway;
        $this->paymentRepository = $paymentRepository;
    }

    public function processPaymentForPayable(Model $payableItem, array $cardDetails, User $seller): array
    {
        $amount = $payableItem->subtotal ?? $payableItem->amount ?? $payableItem->price;
        // dd($amount);
        // $chargeData = array_merge($cardDetails, ['amount' => $payableItem->amount]);

        // $response = $this->paymentGateway->charge($chargeData);

        $response = $this->paymentGateway->charge($seller, $amount, $cardDetails);

        if ($response['status'] === 'success') {
            $this->paymentRepository->create([
                'payable_id'     => $payableItem->id,
                'payable_type'   => get_class($payableItem),
                'payment_method' => $response['payment_method'] ?? 'authorizenet',
                'transaction_id' => $response['transaction_id'],
                'amount'         => $amount,
                'status'         => 'completed',
            ]);
            // $payableItem->update(['status' => 'paid']);
        }
        return $response;
    }


    public function processRefundForOrder(Order $order): array
    {
        $payment = $order->payments()->where('status', 'completed')->first();
        // dd($payment);
        if (!$payment) {
            return ['status' => 'failed', 'message' => 'No completed payment found to refund.'];
        }
        // dd($order   );
        $seller = $order->store;
        // dd($seller);
        $response = $this->paymentGateway->refund($seller, $payment->transaction_id, $payment->amount);
        // dd($response->success);

        if ($response['success'] === true) {
            $payment->update(['status' => 'refunded']);
        }

        return $response;
    }
}
