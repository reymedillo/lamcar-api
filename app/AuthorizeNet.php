<?php
namespace App;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use App\User;
use App\Payment;
class AuthorizeNet
{
    public function __construct() {
        $this->merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $this->merchantAuthentication->setName( config('payment.loginID') );
        $this->merchantAuthentication->setTransactionKey( config('payment.transKey') );
        $this->prefix = env('AUTHORIZE_NET_PREFIX', 'lc');
        $this->refId = $this->prefix.'-'.time();
    }

    public function authorizeOnly($customerProfileId,$customerPaymentProfileId,$amount,$paymentId)
    {
        $invoiceNum = $this->prefix.'-'.$paymentId;

        $order = new AnetAPI\OrderType();
        $order->setInvoiceNumber($invoiceNum);

        $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId($customerProfileId);
        $paymentProfile  = new AnetAPI\PaymentProfileType();
        $paymentProfile->setPaymentProfileId($customerPaymentProfileId);
        $profileToCharge->setPaymentProfile($paymentProfile);

        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authOnlyTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setOrder($order);
        $transactionRequestType->setProfile($profileToCharge);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setRefId($this->refId);
        $request->setTransactionRequest($transactionRequestType);

        $controller = new AnetController\CreateTransactionController($request);
        $response   = $controller->executeWithApiResponse( constant('\net\authorize\api\constants\ANetEnvironment::'.config('payment.environment')) );

        if (is_null($response)) {
            return config('define.valid.false');
        }

        $res = new AuthorizeNetResponse($response, $response->getTransactionResponse());
        \Log::info('Function authorizeOnly');
        \Log::info((array)$res);

        return $res;
    }

    public function saveProfileTo($creditCardNum,$creditCardExpiry,$creditCardCode,$userId,$email)
    {
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($creditCardNum);
        $creditCard->setExpirationDate($creditCardExpiry);
        $creditCard->setCardCode($creditCardCode);
        $paymentCreditCard = new AnetAPI\PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        $paymentProfile->setCustomerType('individual');
        $paymentProfile->setPayment($paymentCreditCard);
        $paymentProfiles[] = $paymentProfile;

        $customerProfile = new AnetAPI\CustomerProfileType();
        $customerProfile->setMerchantCustomerId($this->prefix.'-'.$userId);
        $customerProfile->setEmail($email);
        $customerProfile->setPaymentProfiles($paymentProfiles);

        $request = new AnetAPI\CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setRefId($this->refId);
        $request->setProfile($customerProfile);

        $controller = new AnetController\CreateCustomerProfileController($request);
        $response = $controller->executeWithApiResponse(constant('\net\authorize\api\constants\ANetEnvironment::'.config('payment.environment')) );

        if (is_null($response)) {
            return config('define.valid.false');
        }

        $res = new AuthorizeNetResponse($response);
        \Log::info('Function saveProfileTo');
        \Log::info((array)$res);

        if ( $res->isError() == config('define.valid.false') ) {
            $paymentProfileAN = $response->getCustomerPaymentProfileIdList();
            $res->customer_profile_id         = $response->getCustomerProfileId();
            $res->customer_payment_profile_id = $paymentProfileAN[0];
        }

        return $res;
    }

    public function captureAuth($transId,$amount,$paymentId)
    {
        $invoiceNum = $this->prefix.'-'.$paymentId;

        $order = new AnetAPI\OrderType();
        $order->setInvoiceNumber($invoiceNum);

        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("priorAuthCaptureTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setOrder($order);
        $transactionRequestType->setRefTransId($transId);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setRefId($this->refId);
        $request->setTransactionRequest( $transactionRequestType);

        $controller = new AnetController\CreateTransactionController($request);
        $response   = $controller->executeWithApiResponse( constant('\net\authorize\api\constants\ANetEnvironment::'.config('payment.environment')) );

        if (is_null($response)) {
            return config('define.valid.false');
        }

        $res = new AuthorizeNetResponse($response, $response->getTransactionResponse());
        \Log::info('Function captureAuth');
        \Log::info((array)$res);

        return $res;

    }

    public function voidTransaction($transId,$paymentId)
    {
        $invoiceNum = $this->prefix.'-'.$paymentId;

        $order = new AnetAPI\OrderType();
        $order->setInvoiceNumber($invoiceNum);

        //create a transaction
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("voidTransaction");
        $transactionRequestType->setOrder($order); 
        $transactionRequestType->setRefTransId($transId);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setRefId($this->refId);
        $request->setTransactionRequest($transactionRequestType);
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse( constant('\net\authorize\api\constants\ANetEnvironment::'.config('payment.environment')) );
        if (is_null($response)) {
            return config('define.valid.false');
        }

        $res = new AuthorizeNetResponse($response, $response->getTransactionResponse());
        \Log::info('Function voidTransaction');
        \Log::info((array)$res);

        return $res;
    }

    public function updateProfile($cardNum,$cardExpiry,$cardCode,$userId,$email,$customerProfileId,$customerPaymentProfileId)
    {
        $request = new AnetAPI\GetCustomerProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setCustomerProfileId($customerProfileId);
        $controller = new AnetController\GetCustomerProfileController($request);
        $response = $controller->executeWithApiResponse( constant('\net\authorize\api\constants\ANetEnvironment::'.config('payment.environment')));

        if (is_null($response)) {
            return config('define.valid.false');
        }

        $res = new AuthorizeNetResponse($response);
        \Log::info((array)$res);

        if ($res->isError()){
            return $res;
        }

        $res = $this->updateCustomerProfile($customerProfileId,$userId,$email);
        if ($res->isError()){
            return $res;
        }

        $res = $this->updateCustomerPaymentProfile(
            $cardNum,
            $cardExpiry,
            $cardCode,
            $customerProfileId,
            $customerPaymentProfileId
        );

        $res->customer_profile_id = $customerProfileId;
        $res->customer_payment_profile_id = $customerPaymentProfileId;

        return $res;
    }

    public function updateCustomerProfile($customerProfileId,$userId,$email)
    {
        $updatecustomerprofile = new AnetAPI\CustomerProfileExType();
        $updatecustomerprofile->setCustomerProfileId($customerProfileId);
        $updatecustomerprofile->setMerchantCustomerId($this->prefix.'-'.$userId);
        $updatecustomerprofile->setEmail($email);

        $request = new AnetAPI\UpdateCustomerProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setRefId($this->refId);
        $request->setProfile($updatecustomerprofile);

        $controller = new AnetController\UpdateCustomerProfileController($request);
        $response = $controller->executeWithApiResponse(constant('\net\authorize\api\constants\ANetEnvironment::'.config('payment.environment')) );

        if (is_null($response)) {
            return config('define.valid.false');
        }

        $res = new AuthorizeNetResponse($response);
        \Log::info('Function updateCustomerProfile');
        \Log::info((array)$res);

        return $res;
    }

    public function updateCustomerPaymentProfile($cardNum,$cardExpiry,$cardCode,$customerProfileId,$customerPaymentProfileId)
    {
        $request = new AnetAPI\UpdateCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setCustomerProfileId($customerProfileId);
        $controller = new AnetController\GetCustomerProfileController($request);

        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($cardNum);
        $creditCard->setExpirationDate($cardExpiry);
        $creditCard->setCardCode($cardCode);
        $paymentCreditCard = new AnetAPI\PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        $paymentprofile = new AnetAPI\CustomerPaymentProfileExType();
        $paymentprofile->setCustomerPaymentProfileId($customerPaymentProfileId);
        $paymentprofile->setPayment($paymentCreditCard);

        $request->setPaymentProfile( $paymentprofile );
        $controller = new AnetController\UpdateCustomerPaymentProfileController($request);
        $response = $controller->executeWithApiResponse( constant('\net\authorize\api\constants\ANetEnvironment::'.config('payment.environment')));

        if (is_null($response)) {
            return config('define.valid.false');
        }

        $res = new AuthorizeNetResponse($response);
        \Log::info('Function updateCustomerPaymentProfile');
        \Log::info((array)$res);

        return $res;
    }

}
