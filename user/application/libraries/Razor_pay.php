<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'libraries/razorpaylib/autoload.php';
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class Razor_pay {

    public $api_key = ""; //RAZORPAY_KEY;
    public $secret_key = "";
    public $currency = "";
    public $payment_capture = 1;

	function __construct($config)
    {
        if ( ! empty($config))
        {
            $this->api_key = $config['r_key'];
            $this->secret_key = $config['r_secret'];
            $this->currency = $config['r_currency'];
        }
    }

    public function razorpay_txn_order($data_arr){
        $orderData = array();
        $orderData['receipt'] = $data_arr['txn_id'];
        $orderData['amount'] = $data_arr['amount'] * 100;//paisa
        $orderData['currency'] = $this->currency;
        $orderData['payment_capture'] = $this->payment_capture;

        $api = new Api($this->api_key, $this->secret_key);
        $result = $api->order->create($orderData);
        return $result;
    }

    public function validate_signature($data_arr){
        $api = new Api($this->api_key, $this->secret_key);
        $success = true;
        try
        {
            $attributes = array(
                'razorpay_order_id' => $data_arr['razorpay_order_id'],
                'razorpay_payment_id' => $data_arr['razorpay_payment_id'],
                'razorpay_signature' => $data_arr['razorpay_signature']
            );

            $result = $api->utility->verifyPaymentSignature($attributes);
            if(!$result){
                $success = false;
            }
        }
        catch(SignatureVerificationError $e)
        {
            $success = false;
            //$error = 'Razorpay Error : ' . $e->getMessage();
        }

        return $success;
    }
}
?>