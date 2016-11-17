<?php
namespace App;

class AuthorizeNetResponse {

    public $resultCode = null;
    public $messages = null;
    public $transactionResponse = null;

    public function __construct($response, $trans = null){
        $this->resultCode = $response->getMessages()->getResultCode();
        foreach ($response->getMessages()->getMessage() as $message){
            $this->messages[$message->getCode()] = $message->getText();
        }
        if ( $trans != null ){
            $tres = (object)array();
            $tres->transactionId = $trans->getTransId();
            $tres->responseCode = $trans->getResponseCode();
            $allMessages = $trans->getMessages(); 
            if ( null != $allMessages){
                foreach ($allMessages as $message){
                    $tres->messages[$message->getCode()] = $message->getDescription();
                }
            }
            $allErrors = $trans->getErrors(); 
            if ( null != $allErrors){
                foreach ($allErrors as $error){
                    $tres->errors[$error->getErrorCode()] = $error->getErrorText();
                }
            }
            $this->transactionResponse = $tres;   
        }

    }   

    public function isError(){
        if (strtoupper($this->resultCode) != config('define.authorize.result_code.ok')){
            return config('define.valid.true');
        }
        if (!is_null($this->transactionResponse) && 
            $this->transactionResponse->responseCode != config('define.authorize.response_code.approved')){
            return config('define.valid.true');
        }
        return config('define.valid.false');
    }

    public function getErrors() {
        $authorizeLangs = \Lang::get('custom.authorize');
        foreach($this->messages as $code => $msg){
            if(isset($authorizeLangs['api'][$code])) {
                $errors['api'][] = $authorizeLangs['api'][$code];
            }else{
                $errors['api'][] = $msg;
            }
        }
        if(isset($this->transactionResponse) &&
           isset($this->transactionResponse->errors)){
            foreach($this->transactionResponse->errors as $code => $msg){
                if(isset($authorizeLangs['tran'][$code])) {
                    $errors['tran'][] = $authorizeLangs['tran'][$code];
                }else{
                    $errors['tran'][] = $msg;
                }
            }
        }
        return $errors;
    }

}
