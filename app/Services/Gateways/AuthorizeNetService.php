<?php

namespace App\Services\Gateways;

use App\Interfaces\PaymentGatewayInterface;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use net\authorize\api\constants\ANetEnvironment;
use Exception;

class AuthorizeNetService implements PaymentGatewayInterface
{
    protected $merchantAuthentication;
    protected $isSandbox;

    public function __construct()
    {
        $this->merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $this->merchantAuthentication->setName(config('authorizenet.login_id'));
        $this->merchantAuthentication->setTransactionKey(config('authorizenet.transaction_key'));
        $this->isSandbox = config('authorizenet.sandbox', true);
    }

    public function charge(User $seller, array $paymentData): array
    {

        $credentials = $seller->paymentGatewayCredential;

        if (!$credentials || !$credentials->login_id || !$credentials->transaction_key) {
            return ['status' => 'failed', 'message' => 'Seller has not configured payment credentials.'];
        }

        $this->merchantAuthentication->setName($credentials->login_id);
        $this->merchantAuthentication->setTransactionKey($credentials->transaction_key);


        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($paymentData['card_number']);
        $creditCard->setExpirationDate($paymentData['expiration_year'] . '-' . $paymentData['expiration_month']);
        $creditCard->setCardCode($paymentData['cvc']);

        $payment = new AnetAPI\PaymentType();
        $payment->setCreditCard($creditCard);

        $transactionRequest = new AnetAPI\TransactionRequestType();
        $transactionRequest->setTransactionType("authCaptureTransaction");
        $transactionRequest->setAmount($paymentData['amount']);
        $transactionRequest->setPayment($payment);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setTransactionRequest($transactionRequest);

        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse($this->isSandbox ? ANetEnvironment::SANDBOX : ANetEnvironment::PRODUCTION);

        if ($response !== null && $response->getMessages()->getResultCode() === "Ok" && $response->getTransactionResponse()->getResponseCode() === "1") {
            return ['status' => 'success', 'transaction_id' => $response->getTransactionResponse()->getTransId(), 'message' => 'Payment successful. Your request is being processed.', 'payment_method' => 'authorizenet'];
        }

        $errorMsg = 'Transaction Failed';
        if ($response && $response->getTransactionResponse() && $response->getTransactionResponse()->getErrors()) {
            $errorMsg = $response->getTransactionResponse()->getErrors()[0]->getErrorText();
        }
        return ['status' => 'failed', 'transaction_id' => null, 'message' => $errorMsg];
    }

    // This method is for refunding a transaction
    public function refund(string $transactionId, float $amount): array
    {
        //refund logic here
        return [];
    }
}
