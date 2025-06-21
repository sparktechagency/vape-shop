<?php

require __DIR__.'/../vendor/autoload.php';

use Omnipay\Omnipay;

echo "<h1>Omnipay Debug Script</h1>";

// গেটওয়ে তৈরি করা
$gateway = Omnipay::create('AuthorizeNet_AIM');

$apiLoginId = '4HG3yt7D';
$transactionKey = '5Qwu6e3gC25Ltk6e';

$gateway->setApiLoginId($apiLoginId);
$gateway->setTransactionKey($transactionKey);
$gateway->setTestMode(true);

// কার্ডের তথ্য
$cardData = [
    'number' => '4007000000027',
    'expiryMonth' => '12',
    'expiryYear' => '2028',
    'cvv' => '123',
];

// রিকোয়েস্ট অবজেক্ট তৈরি করা (কিন্তু send() করা হচ্ছে না)
$request = $gateway->purchase([
    'amount' => '12.00',
    'currency' => 'USD',
    'card' => $cardData,
]);


// --- ডিবাগিং শুরু ---

echo "<h2>Debugging Information:</h2>";

// ১. গেটওয়ের এন্ডপয়েন্ট ইউআরএল পরীক্ষা করা
// দেখা যাক এটি স্যান্ডবক্সের URL ব্যবহার করছে নাকি লাইভ URL
echo "<h3>Endpoint URL:</h3>";
echo "<pre>";
var_dump($gateway->getEndpoint());
echo "</pre>";

// ২. রিকোয়েস্টের জন্য তৈরি করা ডেটা পরীক্ষা করা
// দেখা যাক ডেটার ভেতরে ক্রেডেনশিয়ালগুলো ঠিকমতো আছে কিনা
echo "<h3>Data to be Sent:</h3>";
echo "<pre>";
var_dump($request->getData());
echo "</pre>";

// --- ডিবাগিং শেষ ---

echo "<hr><p><strong>Debug script finished.</strong></p>";
