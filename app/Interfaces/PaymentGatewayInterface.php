<?php

namespace App\Interfaces;

interface PaymentGatewayInterface
{
   public function charge(array $paymentData): array;
   public function refund(string $transactionId, float $amount): array;
}
