<?php

namespace App\Interfaces;
use App\Models\User;
interface PaymentGatewayInterface
{
   public function charge(User $seller, array $paymentData): array;
   public function refund(string $transactionId, float $amount): array;
}
