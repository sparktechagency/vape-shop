<?php

namespace App\Services\Gateways;

use App\Enums\UserRole\Role;
use App\Interfaces\PaymentGatewayInterface;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use net\authorize\api\constants\ANetEnvironment;
use App\Models\User;
use Exception;

class AuthorizeNetService implements PaymentGatewayInterface
{
    // protected $merchantAuthentication;
    protected $isSandbox;

    public function __construct()
    {
        // $this->merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        // $this->merchantAuthentication->setName(config('authorizenet.login_id'));
        // $this->merchantAuthentication->setTransactionKey(config('authorizenet.transaction_key'));
        $this->isSandbox = config('authorizenet.sandbox', true);
    }

    public function charge(User $seller, float $amount, array $paymentData): array
    {

        $credentials = $seller->PaymentGatewayCredential;


        if (!$credentials || !$credentials->login_id || !$credentials->transaction_key) {
            $sellerName = $seller->full_name;
            $role = (int)$seller->role; // Assuming role is stored in the User model

            $roleName = match ($role) {
                Role::STORE => Role::STORE,
                Role::BRAND => Role::BRAND,
                Role::WHOLESALER => Role::WHOLESALER,
                Role::ADMIN => Role::ADMIN->label(),
                default => 'Seller',
            };


            $message = "Payment failed because this {$roleName} ('{$sellerName}') has not completed their payment setup. Please contact directly for assistance.";

            return ['status' => 'failed', 'message' => $message];
            // return ['status' => 'failed', 'message' => 'Seller has not configured payment credentials.'];
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();

        $merchantAuthentication->setName($credentials->login_id);
        $merchantAuthentication->setTransactionKey($credentials->transaction_key);


        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($paymentData['card_number']);
        $creditCard->setExpirationDate($paymentData['expiration_year'] . '-' . $paymentData['expiration_month']);
        $creditCard->setCardCode($paymentData['cvc']);

        $payment = new AnetAPI\PaymentType();
        $payment->setCreditCard($creditCard);

        $transactionRequest = new AnetAPI\TransactionRequestType();
        $transactionRequest->setTransactionType("authCaptureTransaction");
        $transactionRequest->setAmount($amount);
        $transactionRequest->setPayment($payment);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
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
    public function refund(User $seller, $transactionId, $amount, $last4 = null): array
    {
        // try {

            $credentials = $seller->paymentGatewayCredential;
            // dd($credentials->login_id, $credentials->transaction_key);

            if (!$credentials || !$credentials->login_id || !$credentials->transaction_key) {
                error_log("Authorize.Net credentials not found for user ID: " . $seller->id);
                return ['success' => false, 'message' => 'Payment gateway credentials are not configured for this user.', 'data' => null];
            }

            $apiLoginId = $credentials->login_id;
            $transactionKey = $credentials->transaction_key;

        // } catch (Exception $e) {
        //     error_log("Database Error fetching user credentials: " . $e->getMessage());
        //     return ['success' => false, 'message' => 'Could not retrieve user credentials.', 'data' => null];
        // }

        $environment = env('AUTHORIZE_NET_ENVIRONMENT', 'sandbox');
        $anetEnvironment = ($environment == 'production') ? ANetEnvironment::PRODUCTION : ANetEnvironment::SANDBOX;

        try {
            $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
            $merchantAuthentication->setName($apiLoginId);
            $merchantAuthentication->setTransactionKey($transactionKey);

            $creditCard = new AnetAPI\CreditCardType();
            $creditCard->setCardNumber('1111');
            $creditCard->setExpirationDate("XXXX");

            $payment = new AnetAPI\PaymentType();
            $payment->setCreditCard($creditCard);

            $transactionRequest = new AnetAPI\TransactionRequestType();
            $transactionRequest->setTransactionType("refundTransaction");
            $transactionRequest->setAmount($amount);
            $transactionRequest->setPayment($payment);
            $transactionRequest->setRefTransId($transactionId);

            $request = new AnetAPI\CreateTransactionRequest();
            $request->setMerchantAuthentication($merchantAuthentication);
            $request->setTransactionRequest($transactionRequest);

            $controller = new AnetController\CreateTransactionController($request);
            $response = $controller->executeWithApiResponse($anetEnvironment);

            if ($response != null && $response->getMessages()->getResultCode() == "Ok") {
                $tresponse = $response->getTransactionResponse();
                if ($tresponse != null && $tresponse->getMessages() != null) {
                    return [
                        'success' => true,
                        'message' => 'Refund processed successfully.',
                        'data' => ['refund_transaction_id' => $tresponse->getTransId()]
                    ];
                }
            }

            $errorMessage = 'Refund failed.';
            if ($response != null) {
                $tresponse = $response->getTransactionResponse();
                if ($tresponse != null && $tresponse->getErrors() != null) {
                    $errorMessage = $tresponse->getErrors()[0]->getErrorText();
                } else {
                    $errorMessage = $response->getMessages()->getMessage()[0]->getText();
                }
            }

            error_log("Authorize.Net Refund Error for User ID $seller->id: $errorMessage");
            return ['success' => false, 'message' => $errorMessage, 'data' => null];
        } catch (Exception $e) {
            error_log("Authorize.Net SDK Exception for User ID $seller->id: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An unexpected error occurred during the refund process.',
                'data' => null
            ];
        }
    }

}
