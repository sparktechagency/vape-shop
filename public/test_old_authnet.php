<?php

require __DIR__ . '/../vendor/autoload.php';

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use net\authorize\api\constants\ANetEnvironment;

echo "<h1>Authorize.Net Modern SDK Test Script</h1>";

// ✅ Sandbox credentials
$loginId        = '4HG3yt7D';
$transactionKey = '5Qwu6e3gC25Ltk6e';
$isSandbox      = true; // true for sandbox, false for production

// Step 1: Authentication
$merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
$merchantAuthentication->setName($loginId);
$merchantAuthentication->setTransactionKey($transactionKey);

// Step 2: Card Info
$creditCard = new AnetAPI\CreditCardType();
$creditCard->setCardNumber("4007000000027"); // Sandbox test card
$creditCard->setExpirationDate("2028-12");
$creditCard->setCardCode("123");

$payment = new AnetAPI\PaymentType();
$payment->setCreditCard($creditCard);

// Step 3: Transaction Details
$transactionRequest = new AnetAPI\TransactionRequestType();
$transactionRequest->setTransactionType("authCaptureTransaction");
$transactionRequest->setAmount(10.00);
$transactionRequest->setPayment($payment);

// Step 4: Full API request
$request = new AnetAPI\CreateTransactionRequest();
$request->setMerchantAuthentication($merchantAuthentication);
$request->setTransactionRequest($transactionRequest);

// Step 5: Controller & API call
$controller = new AnetController\CreateTransactionController($request);
$response = $controller->executeWithApiResponse(
    $isSandbox ? ANetEnvironment::SANDBOX : ANetEnvironment::PRODUCTION
);

// Step 6: Response Handling
echo "<p><strong>Response Received:</strong></p>";

if ($response !== null && $response->getMessages()->getResultCode() === "Ok") {
    $tresponse = $response->getTransactionResponse();

    if ($tresponse !== null && $tresponse->getResponseCode() === "1") {
        echo '<div style="color: green; border: 1px solid green; padding: 10px;">';
        echo '<strong>Success!</strong><br>';
        echo 'Transaction ID: ' . $tresponse->getTransId() . '<br>';
        echo 'Auth Code: ' . $tresponse->getAuthCode();
        echo '</div>';
    } else {
        echo '<div style="color: red; border: 1px solid red; padding: 10px;">';
        echo '<strong>Transaction Failed!</strong><br>';
        echo 'Error: ' . $tresponse->getErrors()[0]->getErrorText();
        echo '</div>';
    }
} else {
    $errorMsg = $response && $response->getMessages()
        ? $response->getMessages()->getMessage()[0]->getText()
        : "Unknown error";

    echo '<div style="color: red; border: 1px solid red; padding: 10px;">';
    echo '<strong>Connection Failed!</strong><br>';
    echo 'Error: ' . $errorMsg;
    echo '</div>';
}

echo "<hr><p><strong>Test Finished.</strong></p>";
