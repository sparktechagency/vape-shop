<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use App\Interfaces\PaymentRepositoryInterface;
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
        // dd($payableItem);
        // $chargeData = array_merge($cardDetails, ['amount' => $payableItem->amount]);

        // $response = $this->paymentGateway->charge($chargeData);

        $response = $this->paymentGateway->charge($seller, $payableItem->subtotal, $cardDetails);

        if ($response['status'] === 'success') {
            $this->paymentRepository->create([
                'payable_id'     => $payableItem->id,
                'payable_type'   => get_class($payableItem),
                'payment_method' => $response['payment_method'] ?? 'authorizenet',
                'transaction_id' => $response['transaction_id'],
                'amount'         => $payableItem->subtotal,
                'status'         => 'completed',
            ]);
            // $payableItem->update(['status' => 'paid']);
        }
        return $response;
    }
}
