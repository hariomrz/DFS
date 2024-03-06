<?php
 
class Btcpay extends Common_Api_Controller {

    public $pg_id = 19;
    public $order_prefix = '';
    public $BTCPAY_URL = BTC_URL;

    function __construct() {
        parent::__construct();

        if(isset($this->app_config['allow_btcpay']) && $this->app_config['allow_btcpay']['key_value'] == "0"){
            // throw new Exception("Sorry, crypto not enabled. please contact admin."); 
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = "Sorry, BTCPAY not enabled. please contact admin.";
            $this->api_response();
        }
        $this->finance_lang = $this->lang->line("finance");
        $this->crypto = $this->lang->line('crypto');
        $this->APP_ID = $this->app_config['allow_btcpay']['custom_data']['app_id'];
        $this->STORE_ID = $this->app_config['allow_btcpay']['custom_data']['store_id'];
        $this->EXP_MINUTES = (int)$this->app_config['allow_btcpay']['custom_data']['order_expiry_minutes'];
        $this->order_prefix = isset($this->app_config['order_prefix']) ? $this->app_config['order_prefix']['key_value'] : '';
    }

    /**
     * @method deposit cash
     * @uses function to add real and bonus cash to user
     * */
    public function deposit_post() {
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|callback_decimal_numeric|callback_validate_deposit_amount');
        $this->form_validation->set_rules('furl', 'furl', 'required');
        $this->form_validation->set_rules('surl', 'surl', 'required');
        $this->form_validation->set_rules('purl', 'purl', 'required');
        $this->form_validation->set_rules('currency_type', 'currency_type', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();
        
        $cryp = $post_data['currency_type'];
        $amount = $post_data['amount'];

        $apk_data = $this->app_config['allow_btcpay']['custom_data'];
        $dp_str = isset($apk_data['dp']) ? $apk_data['dp'] : "";
        $cur_key = explode("_",$dp_str);

        if(!in_array($cryp, $cur_key)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = $this->crypto_lang['invalid_currency'];
            $this->api_response();
        }

        $request_data = array();
        $request_data['checkout']['paymentMethods'] = $cur_key; //["BTC"]
        $request_data['checkout']['defaultPaymentMethod'] = $cryp; //"BTC"
        $request_data['checkout']['expirationMinutes'] = $this->EXP_MINUTES;
        $request_data['checkout']['monitoringMinutes'] = $this->EXP_MINUTES;
        $request_data['checkout']['paymentTolerance'] = 0;
        $request_data['checkout']['redirectURL'] = $post_data['purl'];
        $request_data['checkout']['redirectAutomatically'] = "true";
        $request_data['checkout']['requiresRefundEmail'] = "true";
        $request_data['checkout']['defaultLanguage'] = "en-US";
        $request_data['amount'] = $amount;
        $request_data['currency'] = $this->app_config['currency_abbr']['key_value']; // DO BY CONFG LATER

        $product_info = SITE_TITLE . ' deposit via BTCPAY';
        $email = isset($this->email) && !empty($this->email) ? $this->email : 'test@mail.com';
        $phoneno = isset($this->phone_no) ? $this->phone_no : '1234567890';
        $firstname = isset($this->user_name) ? $this->user_name : 'User';
        $surl = $post_data['surl'];
        $furl = $post_data['furl'];
        $this->PURL = str_replace('failure','pending',$furl);
        
        $this->load->model("finance/Finance_model");
        $promo_code = array();
        if (!empty($post_data['promo_code'])) {
            $promo_code = $this->validate_promo($post_data);
        }

        $deal = array();
        if (!empty($post_data['deal_id'])) {
            $deal = $this->validate_deal($post_data);
        }

        $_POST['payment_gateway_id'] = $this->pg_id;

        // GET transaction ID from Database after generating an order
        $this->load->model("finance/Finance_model"); 
        $txnid = $this->Finance_model->_generate_order_and_tx($amount, $this->user_id, $email, $phoneno, $product_info, $surl, $furl, $promo_code, $deal);
        
        //Creating a unique transaction id from our end
        $request_data['metadata']['orderId'] = $this->order_prefix.$txnid;
        $request_data['metadata']['buyerEmail'] = $email;
        $request_data = json_encode($request_data);

        $url = sprintf($this->BTCPAY_URL,$this->STORE_ID);

        //Curl Execution
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>$request_data,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: token '.$this->APP_ID,
        ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        
        //If log enabled, then saving logs
        if (LOG_TX) {            
            log_message('BTC PAY RESPONSE :', $response);
        }

        //Decoding response
        $res_arr = json_decode($response,true);

        //save PG invoice id 
        $pg_invoice_id = $res_arr['id'];
        $order_id = $res_arr['metadata']['orderId'];
        $org_order_id = substr($order_id,5);
        
        if(!empty($res_arr['id']))
        {
            $tr_data = array(
                'pg_order_id'   =>$order_id,
                'bank_txn_id'   =>$pg_invoice_id,
                'txn_amount'    =>$res_arr['amount'],
                'txn_date'      =>format_date('today'),
                'currency'      =>$res_arr['currency'],
                'gate_way_name' =>'BTCPAY',
                'payment_mode'  =>'BTC'
            );
           
            $this->db->update(TRANSACTION,$tr_data,['transaction_id'=>$org_order_id]);
        }

        if(!empty($res_arr['checkoutLink'])){
            
            $data_arr = array();
            $data_arr['deposit_crypto_amt']=$res_arr['amount'];
            $data_arr['deposit_crypto']=$res_arr['checkout']['paymentMethods'][0];
            $data_arr['qr_code']=$res_arr['checkoutLink'];

            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['message'] = $this->lang->line('pending_status');
            $this->api_response_arry['data'] = $data_arr;
            $this->api_response();
        }
    }
/**As BTC pay does not provide any values in call back so we did not implement any transaction status update method here ,
 * all the transaction will be by default pending and will be updated by cron only
 */
}
