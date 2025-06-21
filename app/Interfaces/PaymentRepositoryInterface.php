<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface PaymentRepositoryInterface
{
    public function create(array $data): Model;
}
