<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class PaymentGatewayCredential extends Model
{
    protected $guarded = ['id'];

    public function setLoginIdAttribute($value)
    {
        $this->attributes['login_id'] = Crypt::encryptString($value);
    }
    public function getLoginIdAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (DecryptException $e) {
            return null;
        }
    }
    public function setTransactionKeyAttribute($value)
    {
        $this->attributes['transaction_key'] = Crypt::encryptString($value);
    }
    public function getTransactionKeyAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (DecryptException $e) {
            return null;
        }
    }
}
