<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


use ReceiptValidator\GooglePlay\Validator as PlayValidator;
use ReceiptValidator\iTunes\AbstractResponse;
use ReceiptValidator\iTunes\Validator as iTunesValidator;

class Subscription_cron extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        echo "Subscription Cron here";exit();
    }

    /**
     * method to get the pending subscriptions for renew 
     */
    public function renew_coin_package_subscription()
    {
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $to_renew_users = $this->Cron_model->renew_coin_package_subscription();
        foreach($to_renew_users as $key=>$one_user)
        {
            $data = array(
                "deviceType" => $one_user['type'],
                "purchaseToken" =>$one_user['receipt_id'],
                "productId" =>$one_user['product_id'],
                "packageName" =>$this->app_config['allow_subscription']['custom_data']['package_name'],
                "user_id"=>$one_user['user_id'],
                "subscription_id"=>$one_user['subscription_id'],
                "amount"=>$one_user['amount'],
                "coins"=>$one_user['coins'],
                "email"=>$one_user['email'],
                "phone"=>$one_user['phone_no'],
            );

            $res = $this->validate_receipt($data);
        }
        $this->benchmark->mark('code_end');
        echo "Execution  Time : ".$this->benchmark->elapsed_time('code_start','code_end');
        exit();
    }
    

    /**
     * function for receipt validation for both ios and android
     */
    public function validate_receipt($post_data) {
        
        if (empty($post_data['purchaseToken'])) {
            redirect(BASE_APP_PATH, 'location', 301);
        }

        $data = array();
        $data['number']= $post_data['purchaseToken'];
        $user_balance_cache_key = 'user_balance_' . $post_data['user_id'];

        if($post_data['deviceType']==1){

            //ANDROID RECEIPT VALIDATION
            $validate = $this->validate_tx_andriod($post_data);
        }
        else if($post_data['deviceType']==2){
            //IOS  RECEIPT VALIDATION
            $validate = $this->validate_tx_ios($post_data);
        }
        if (!empty($validate) && !empty($validate['pg_order_id'])) {
                if (!empty($validate["status"] == 1 && $validate['txn_expiry_date']) && $validate['txn_expiry_date'] > format_date('today')){

                    $this->load->model('Cron_model');
                    //generate order 
                    $amount = $post_data['coins'];
                    $user_id = $post_data['user_id'];
                    $cash_type = 2;
                    $plateform = 0;
                    $source = 437;
                    $source_id = $post_data['subscription_id'];
                    $season_type = 1;
                    $status = 1;
                            $order_id = $this->Cron_model->generate_order($amount, $user_id, $cash_type, $plateform, $source, $source_id,$season_type,$status=0);
                    //generate transaction 
                    $custom_data = array(
                            "type"             => $post_data['deviceType'],
                            "coins"             => $post_data['coins'],
                            "amount"            => $post_data['amount'],
                            "subscribed_date"   => $validate['txn_date'],
                            "subscription_id"   => $post_data['subscription_id'],
                    );
                    // $update_order = $this->Cron_model->update_order($order_id,json_encode($custom_data));
                    $transaction = array(
                                        'order_id' => $order_id,
                                        'payment_gateway_id' => 11, // pg id for in app subscription module 
                                        'email' => $post_data['email'],
                                        'phone' => $post_data['phone'],
                                        'description' =>  SITE_TITLE . ' Inapp subscription',
                                        'surl' => WEBSITE_DOMAIN.'payment-method?status=success',
                                        'furl' => WEBSITE_DOMAIN.'payment-method?status=failure',
                                        'transaction_status' => 1,
                                        'withdraw_type' => 0,
                                        'txn_amount' => (isset($validate['amount'])) ? $validate['amount'] : 0,
                                        'responce_code' => (isset($post_data['purchaseToken'])) ? $post_data['purchaseToken'] : 0,
                                        'txn_date' => (isset($validate['txn_date'])) ? $validate['txn_date'] : 0,
                                        'pg_order_id' => (isset($validate['pg_order_id'])) ? $validate['pg_order_id'] : 0,
                                        'bank_name' => (isset($validate['bank_name'])) ? $validate['bank_name'] : 0,
                                        'custom_data' => $custom_data,
                                    );
                    $txn_id = $this->Cron_model->create_transaction($transaction);
                    $post_data['new_exp_date'] =  $validate['txn_expiry_date'] ? $validate['txn_expiry_date']:null;
                    $post_data['start_date'] =  $validate['txn_date'] ? $validate['txn_date']:format_date('today');
                    //update user subscription
                    $update_user_subscription = $this->Cron_model->update_subscription($order_id,$post_data);
                        /* Send Notification */
                        $notify_data = array();
                        $notify_data['notification_type'] = 439; //439- renew subscription plan
                        $notify_data['notification_destination'] = 1; //web
                        $notify_data["source_id"] = 0;
                        $notify_data["user_id"] = $user_id;
                        $notify_data["to"] = $post_data['email'];
                        $notify_data["user_name"] = '';
                        $notify_data["added_date"] = format_date();
                        $notify_data["modified_date"] = format_date();
                        $notify_data["subject"] = "Renew subscription";
                        $notify_data["content"] = json_encode(array());
                        $this->load->model('notification/Notify_nosql_model');
                        $this->Notify_nosql_model->send_notification($notify_data); 
                } 
        } 
       
    }

    /**
     * VALIDATE ANDROID TYPE SUBSCRIPTION WHILE RENEW PLAN
     */
    private function validate_tx_andriod($data) {
        $receiptBase64Data = $data['purchaseToken']; // purchase token
        $configLocation = ROOT_PATH . $this->app_config['allow_subscription']['custom_data']['json_name']; //service account credentials
        
        try {
            $googleClient = new \Google_Client();
            $googleClient->setScopes([\Google_Service_AndroidPublisher::ANDROIDPUBLISHER]);
            $googleClient->setApplicationName($this->app_config['allow_subscription']['custom_data']['app_name']);
            $googleClient->setAuthConfig($configLocation);
            $googleAndroidPublisher = new \Google_Service_AndroidPublisher($googleClient);
            $subscription = $googleAndroidPublisher->purchases_subscriptions->get($data['packageName'], $data['productId'], $receiptBase64Data);
            if (is_null($subscription)) {
                $res['message'] = $this->lang->line('validation_error');
                $res['txn_expiry_date'] = NULL;
            } elseif (isset($subscription['error']['code'])) {
                $res['message'] = $this->lang->line('validation_error');
                $res['txn_expiry_date'] = NULL;
            } elseif (!isset($subscription['expiryTimeMillis'])) {
                $res['message'] = $this->lang->line('validation_error');
                $res['txn_expiry_date'] = NULL;
            } else {
                $res['txn_expiry_date'] = date("Y-m-d H:i:s", ($subscription->expiryTimeMillis / 1000));
            }
  
            $res['txn_id'] = $subscription->orderId;
            $original_transaction_id = strstr($subscription->orderId, '..', true);
            if ($original_transaction_id === FALSE) {
                $res['mid'] = $subscription->orderId;
            } else {
                $res['mid'] = $original_transaction_id;
            }
            $res['bank_name'] = $data['packageName'];
            $res['currency'] = $subscription->priceCurrencyCode;
            $res['original_transaction_date'] = date("Y-m-d H:i:s", ($subscription->startTimeMillis / 1000));
            $res['txn_date'] = date("Y-m-d H:i:s", ($subscription->startTimeMillis / 1000));
            $res['pg_order_id'] = $subscription->orderId;
            $res['purchaseState'] = $subscription->purchaseState;
            $res['product_id'] = $data['productId'];
            $res['amount'] = ($subscription->priceAmountMicros)/1000000;
            $res['status'] = 1;
        } catch (Exception $e) {
            $res['message'] = $e->getMessage();
        }
        return $res;
    }

    /**
   * VALIDATE IOS RENEW SUBSCRIPTION PLAN 
   */
  private function validate_tx_ios($data) {
    $receiptBase64Data = $data['number'];
    $res = array('bank_txn_id' => $receiptBase64Data);
    if (ENVIRONMENT == 'production') {
        $validator = new iTunesValidator(iTunesValidator::ENDPOINT_PRODUCTION);
    } else {
        $validator = new iTunesValidator(iTunesValidator::ENDPOINT_SANDBOX);
    }
    $validator->setSharedSecret(SHARED_SECRET);
    try {
        $response = $validator->setReceiptData($receiptBase64Data)->validate(); // use setSharedSecret() if for recurring subscriptions
        
        if ($response->isValid()) {

            $purchase = $response->getLatestReceiptInfo()[0];
            if (!is_null($purchase)) {
                if ($purchase->getExpiresDate() != null) {
                    $res['txn_expiry_date'] = $purchase->getExpiresDate()->toDateTimeString();
                } else {
                    $res['txn_expiry_date'] = NULL;
                }

                $res['mid'] = $purchase->getOriginalTransactionId();
                //$res['product_id'] = $purchase->getProductId();
                $res['txn_id'] = $purchase->getTransactionId();
                if ($purchase->getPurchaseDate() != null) {
                    $res['txn_date'] = $purchase->getPurchaseDate()->toDateTimeString();
                }
                if ($purchase->getOriginalPurchaseDate() != null) {
                    $res['original_transaction_date'] = $purchase->getOriginalPurchaseDate()->toDateTimeString();
                }
            } else {
                $res['txn_expiry_date'] = NULL;
            }
            $result = $response->getReceipt();
            // echo "<pre>";print_r($data);exit;
            // 'product_name' => ($res['in_app'][0]['product_id']) ? $res['in_app'][0]['product_id'] : '',
            // $updateTransactionData = array(
            $res['bank_name'] = (isset($result['bundle_id'])) ? $result['bundle_id'] : '';
            $res['bank_txn_id'] =  ($result['in_app'][0]['transaction_id']) ? $result['in_app'][0]['transaction_id'] : '';
            $res['txn_date'] = ($result['in_app'][0]['purchase_date']) ? $result['in_app'][0]['purchase_date'] : '';
            $res['txn_amount'] = $_POST['txn_amount'];
            $res['txn_id'] = $_POST['order_id'];
            $res['mid'] = $_POST['purchaseToken'];
            // );
            $output['response']= $updateTransactionData;
        } else {
            $output['message'] = $response->getResultCode();
        }
    } catch (Exception $e) {
        $output['message'] = $e->getMessage();
    }
    return $output;
  }
   
}
