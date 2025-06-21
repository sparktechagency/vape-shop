<?php

namespace App\Repositories;

use App\Interfaces\PaymentRepositoryInterface;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function create(array $data): Model
    {
        return Payment::create($data);
    }
}
