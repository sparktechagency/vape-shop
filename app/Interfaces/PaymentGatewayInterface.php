<?php

namespace App\Interfaces;
use App\Models\User;
interface PaymentGatewayInterface
{
   public function charge(User $seller, float $amount, array $paymentData): array;
   public function refund(string $transactionId, float $amount): array;
}
