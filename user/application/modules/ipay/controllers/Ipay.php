<?php

class Ipay extends Common_Api_Controller{

	public $ipay_success = 'aei7p7yrx4ae34';
	public $ipay_failure = 'fe2707etr5s4wq';
	public $ipay_already = 'cr5i3pgy9867e1';
	public $NOTIFY_EMAIL=1;
	public $RESPONSE_FORMAT=0; //RESPONSE FORMATE 0-> CALL BACK URL, 1->COMMA SAPERATED DATA STREAM, 2-> JSON FORMAT
	public $pg_id =5;
	public $IPAY_PG_MODE='';
	public $IPAY_MERCHANT_KEY='';
	public $IPAY_HASHKEY='';
	public $IPAY_CURR='';

	function __construct(){
		parent::__construct();

		if(isset($this->app_config['allow_ipay']) && $this->app_config['allow_ipay']['key_value'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = "Sorry, Ipay not enabled. please contact admin.";
            $this->api_response();
        }
        $this->finance_lang = $this->lang->line("finance");

        $this->IPAY_PG_MODE      = $this->app_config['allow_ipay']['custom_data']['pg_mode'];
        $this->IPAY_MERCHANT_KEY = $this->app_config['allow_ipay']['custom_data']['merchant_key'];
        $this->IPAY_HASHKEY         = $this->app_config['allow_ipay']['custom_data']['hashkey'];
        $this->IPAY_CURR  = $this->app_config['currency_abbr']['key_value']; // VALUES ARE "INR","USD"
		$this->load->helper('form');
	}
	//function to add real and bonas cash to user
	public function deposit_post(){
		$this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|callback_decimal_numeric|callback_validate_deposit_amount');
		$this->form_validation->set_rules('furl', 'furl', 'required');
		$this->form_validation->set_rules('surl', 'surl', 'required');
		if ($this->form_validation->run() == FALSE) {
			$this->send_validation_errors();
		}
		$post_data = $this->post();
		$user_id = $this->user_id;
		$surl = $post_data['surl'];
		$furl = $post_data['furl'];
		$product_info = SITE_TITLE . ' deposit via Ipay';
		$email = isset($this->email) ? $this->email : '';
		$phoneno = isset($this->phone_no) ? $this->phone_no : '1234567890';
		$firstname = isset($this->user_name) ? $this->user_name : 'User';
		$amount = $post_data['amount'];
		$amount = number_format($amount, 2, '.', '');
		$this->load->model("finance/Finance_model");
		$promo_code = array();
		if (!empty($post_data['promo_code'])) {
			$promo_code = $this->validate_promo($post_data);
		}
		$deal = array();
		if (!empty($post_data['deal_id'])) {
			$deal = $this->validate_deal($post_data);
		}
        // till now for me order id and invoice number are same txnid as billow
        $_POST['payment_gateway_id'] = $this->pg_id;
		$txnid = $this->Finance_model->_generate_order_and_tx($amount, $user_id, $email, $phoneno, $product_info, $surl, $furl, $promo_code, $deal);
		$fields =[
        		"live"=> ($this->IPAY_PG_MODE=='TEST') ? 0:1, //SHOULD be 0 when testing and 1 While in production
                "oid"=> (string) $txnid,//ORDER ID from your MERCHANT WEBSITE
                "inv"=> (string) $txnid,  //invoice number/ if you dont have, you the orderID
                "ttl"=> $amount, //amount of money to be transacted by the customer
                "tel"=> $phoneno,//telephone number of the customer
                "eml"=> $email,//email address of the customer
                'vid'=> $this->IPAY_MERCHANT_KEY, //the MERCHANT ID assigned by the IPAY
                "curr"=> $this->IPAY_CURR, //CURRENY in USE
                "cbk"=> IPAY_CALLBACK_URL,// CALL BACK URL
                "cst"=> $this->NOTIFY_EMAIL,//EMAIL NOTIFICATION 1->YES , 0->NO
                "crl"=> $this->RESPONSE_FORMAT, //RESPONSE FORMATE 0-> CALL BACK URL[IN PRODUCTION], 1->COMMA SAPERATED DATA STREAM, 2-> JSON FORMAT [IN DEVELOPMENT]
                "p1"=> "",
                "p2"=> "",
                "p3"=> "",
                "p4"=> ""
            ];
            $datastring =  $fields['live'].$fields['oid'].$fields['inv'].$fields['ttl'].$fields['tel'].$fields['eml'].$fields['vid'].$fields['curr'].$fields['p1'].$fields['p2'].$fields['p3'].$fields['p4'].$fields['cbk'].$fields['cst'].$fields['crl'];

            $hashkey =$this->IPAY_HASHKEY;/* On the Hashkey, provide the key provided from the IPAY Team, ensure no spaces provided */
            $generated_hash = hash_hmac('sha1',$datastring , $hashkey);
            $fields['hsh']= $generated_hash;

            if (LOG_TX) {
            	$test_data = json_encode($fields);
            	$this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "1"));
            }
            $this->data['fields'] = $fields;
            $this->data['url'] = IPAY_ACTION;
	        // Post variable to view with HASH and auto submit form there
            $this->data['data'] = $this->load->view('ipay/deposit', $this->data, true);
            $this->api_response_arry['data'] = $this->data['data'];
            $this->api_response();

        }
         /**
     * update order status to platform 
     * 
     * @param int $transaction_id Transaction ID
     * @param int $status_type Status Type
     * @return bool Status updated or not
     */
         private function _update_tx_status($transaction_id, $status_type, $update_data = array()) {
         	$trnxn_rec = $this->Finance_model->get_transaction($transaction_id);
        if ($status_type == 1) {   // GET order_id from transaction ID
        	if (!empty($trnxn_rec)) {

        		$this->update_order_status($trnxn_rec["order_id"], $status_type, $transaction_id, 'by your recent transaction', $this->pg_id);

        	}
        }
        // CALL Platform API to update transaction
        $data = array();
        $data['transaction_status'] = $status_type;
        $data['payment_gateway_id']=$this->pg_id;
        $data['currency']=$this->IPAY_CURR;
        $data['gate_way_name'] = "Ipay";
        $data['is_checksum_valid'] = "1";
        if (!empty($update_data)) {

        	//$data['payment_mode'] = isset($payu_response['mode']) ? $payu_response['mode'] : "";
        	$data['mid'] = $update_data['vendor'];
	        $data['txn_id'] = isset($update_data['ivm']) ? $update_data['ivm'] : "";
	        $data['bank_txn_id'] = isset($update_data['txncd']) ? $update_data['txncd'] : "";
	        $data['txn_amount'] = isset($update_data['amount']) ? $update_data['amount'] : 0;
	        $data['phone'] = isset($update_data['phone']) ? $update_data['phone'] : 0;
	        $data['transaction_message'] = isset($payu_response['error_Message']) ? $payu_response['error_Message'] : "";
	        $data['txn_date'] = format_date();
        	//$data = array_merge($data, $update_data);
        }

        if ($trnxn_rec['transaction_status'] == 0) {
        	$res = $this->Finance_model->update_transaction($data, $transaction_id);
        	//echo $this->db->last_query();exit;
        }

        // When Transaction has been failed , order status will also become fails
        if ($status_type == 2) {
        	$sql = "UPDATE " . $this->db->dbprefix(ORDER) . " AS O
        	INNER JOIN " . $this->db->dbprefix(TRANSACTION) . " AS T ON T.order_id = O.order_id
        	SET O.status = T.transaction_status
        	WHERE T.transaction_id = $transaction_id AND O.status = 0 ";

        	$this->db->query($sql);
        }
        if ($res) {
        	return $trnxn_rec;
        } else {
            //return $trnxn_rec;
        }

        return FALSE;
    }
    public function payment_callback_get(){
    	//echo "jakdf";exit;

    	$furl = FRONT_APP_PATH;
    	if (!$this->input->get()) {
    		redirect($furl, 'location', 301);
    	}
				//$val = IPAY_MERCHANT_KEY; //assigned iPay Vendor ID... hard code it here.
				$updateTransectionData = array();
				$updateTransectionData['vendor']=$this->IPAY_MERCHANT_KEY;
				if($this->input->get()):
					$updateTransectionData['id']= $this->input->get('id');
					$updateTransectionData['ivm']= $this->input->get('ivm');
					$updateTransectionData['qwh']= $this->input->get('qwh');
					$updateTransectionData['afd']= $this->input->get('afd');
					$updateTransectionData['poi']= $this->input->get('poi');
					$updateTransectionData['uyt']= $this->input->get('uyt');
					$updateTransectionData['ifd']= $this->input->get('ifd');
					$updateTransectionData['status']= $this->input->get('status');
					$updateTransectionData['amount']= $this->input->get('mc');
					$updateTransectionData['txncd']= $this->input->get('txncd');
					$updateTransectionData['phone']= $this->input->get('msisdn_idnum');
				else:
					$updateTransectionData['id']= 0;
					$updateTransectionData['ivm']= 0;
					$updateTransectionData['qwh']= 0;
					$updateTransectionData['afd']= 0;
					$updateTransectionData['poi']= 0;
					$updateTransectionData['uyt']= 0;
					$updateTransectionData['ifd']= 0;
					$updateTransectionData['status']= 0;
					$updateTransectionData['amount']= 0;
					$updateTransectionData['txncd']= 0;
					$updateTransectionData['phone']= 0;
				endif;
				//print_r($updateTransectionData); exit;
				$this->load->model("finance/Finance_model");
				if (LOG_TX) {
					$test_data = json_encode($updateTransectionData);
					$this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "2"));
				}
				//layer one check start
				if(trim($updateTransectionData['status'])==$this->ipay_success){
				//layer two check start	
				$transection_id = trim($updateTransectionData['ivm']);
				$server_tr_id = $this->Finance_model->get_single_row('surl,furl,transaction_status,transaction_id', TRANSACTION, array("transaction_id" => $transection_id));
				if($transection_id === $server_tr_id['transaction_id']){
						$transection_record = $this->_update_tx_status($transection_id,1,$updateTransectionData);
						//echo "Transaction Success";exit;
						redirect($server_tr_id['surl'], 'location', 301);
					
				}
				else {
					$transection_record = $this->_update_tx_status($transection_id,2,$updateTransectionData);
					redirect($server_tr_id['furl'], 'location', 301);
				}
				}
				else {
					redirect(BASE_APP_PATH, 'location', 301);
				}
			}
		}
		?>
