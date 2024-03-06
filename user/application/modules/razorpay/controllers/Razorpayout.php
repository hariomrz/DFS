<?php

class Razorpayout extends Common_Api_Controller {

    public $success_status = array("processed","payout.processed","payout_processed");
    public $failed_status = array("failed","payout.reversed","payout.failed","reversed");
    public $pending_status = array("processing");
    public $pg_id = 8;
    function __construct() {
        parent::__construct();
        $this->finance_lang = $this->lang->line("finance");
        if(isset($this->app_config['auto_withdrawal']) && $this->app_config['auto_withdrawal']['key_value'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = "No payout is available right now, please contact to admin.";
            $this->api_response();
        }
    }

    public function callback_post()
    {
        $response = $_POST;
        if(LOG_TX) error_log("\n\n\n".format_date().'###>>>>>>>>#### rAZORPAYX payout response'.json_encode($response).'  : ',3,'/var/www/html/cron/application/logs/payout.log');
        $this->load->model("Razorpay_model","rm");
        if(in_array($response['event'],$this->success_status) || in_array($response['event'],$this->failed_status))
        {
            if(isset($response['payload']['payout']['entity']) && !empty($response['payload']['payout']['entity']))
            {
                $response = $response['payload']['payout']['entity'];
                if(isset($response['id']) && isset($response['reference_id']))
                {
                    $txn = explode("_",$response['reference_id']);
                    $txn_check = $this->rm->check_txn_info($txn);
                    $key = $txn_check['transaction_id'].'_'.$txn_check['order_unique_id'];
                    if($txn_check && $key === $response['reference_id'])
                    {
                        $this->rm->update(ORDER,['payout_processed'=>1],['order_id'=>$txn_check['order_id']]);
                        $fail_status = 4;
                        $success_status = 5;

                        if(!in_array($txn_check['t_status'],[0,3]) && $txn_check['o_status']!=0)
                        {
                            return true;
                        }
                        $update_transaction_data = array();
                        $update_transaction_data["transaction_message"]     = $response['status_details']['description'];
                        $update_transaction_data["txn_amount"]              = $response['amount']/100;
                        $update_transaction_data["bank_txn_id"]             =$response['utr'];
                        //success case
                        if(in_array($response['status_details']['reason'],$this->success_status))
                        {
                            $update_transaction_data["transaction_status"]      = $success_status;
                            $this->rm->_common_update_payout_tx_status($txn_check, $update_transaction_data);
                        }elseif(in_array($response['status'],$this->failed_status)) // fail status
                        {
                            $update_transaction_data["transaction_status"]      = $fail_status;
                            $this->rm->_common_update_payout_tx_status($txn_check, $update_transaction_data);

                            $this->load->model("finance/Finance_model");
                            $this->Finance_model->update_user_balance($txn_check["user_id"], $txn_check, 'add');
                            $user_cache_key = "user_balance_".$txn_check["user_id"];
                            $this->delete_cache_data($user_cache_key);
                        }
                    }
                    return true;
                }
            }
        }

    }
}