<?php

return [
    'login_id' => env('AUTHORIZE_LOGIN_ID'),
    'transaction_key' => env('AUTHORIZE_TRANSACTION_KEY'),
    'sandbox' => env('AUTHORIZE_SANDBOX', true),
];
