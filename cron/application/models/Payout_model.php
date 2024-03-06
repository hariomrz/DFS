<?php

class Payout_model extends MY_Model
{

    public $db_user;

    public $pending_status = 3;
    public $fail_status = 4;
    public $success_status = 5;
    public function __construct()
    {
        parent::__construct();
        $this->db_user = $this->load->database('db_user', TRUE);
        $this->lang->load('general', $this->config->item('language'));

    }

    /**common fetch pending transactions */
    public function process_new_payout_pending_order()
    {
        $current_date = format_date();
        $process_date_time = date('Y-m-d H:i:s', strtotime('-3 minutes', strtotime($current_date)));
        $last_date_time = date('Y-m-d H:i:s', strtotime('-48 hours', strtotime($current_date)));
        $pg_id = array(1,17,3,8,34);
        $this->db = $this->db_user;
        $this->db->select("O.order_id, O.order_unique_id, O.winning_amount, O.user_id, O.source, O.date_added, O.withdraw_method, T.transaction_id, T.txn_date,T.pg_order_id,T.payment_gateway_id,T.transaction_status AS status")
            ->from(ORDER . " O")
            ->join(TRANSACTION . " T", "T.order_id = O.order_id AND T.transaction_status=3")
            ->where("O.status", 0)
            ->where("O.source", 8)
            ->where("O.type", 1)
            ->where("O.modified_date <= ", $process_date_time)
            ->where("O.modified_date >= ", $last_date_time)
            ->where_in("T.payment_gateway_id", $pg_id)
            ->order_by("O.order_id", "DESC")
            ->limit(50);

        $query = $this->db->get();
        $result = $query->result_array();
        //print_r($result);die;
        // echo $this->db_user->last_query();exit;
        $paytm_params = array();

        $this->load->helper('queue_helper');
        foreach ($result as $order) {
            if ($order['withdraw_method'] == 17) {
                if($order['pg_order_id']==null)
                {
                    continue;
                }
                $order['action'] = "cashfree_status_update";
                add_data_in_queue($order, 'payout');
            }elseif ($order['withdraw_method'] == 3) {
                // if($order['pg_order_id']==null)
                // {
                //     continue;
                // }
                $this->mpesa_payout_status_update($order);
                $order['action'] = "mpesa_status_update";
                add_data_in_queue($order, 'payout');
            }elseif ($order['withdraw_method'] == 1) {
                $order['action'] = "payumoney_status_update";
                $order['from_date'] = date('d-m-Y', strtotime($last_date_time));
                $order['to_date'] = date('d-m-Y', strtotime($process_date_time));
                $order['mrid'] = $order['transaction_id'].'_'.$order['order_unique_id'];
                add_data_in_queue($order, 'payout');
            }elseif ($order['withdraw_method'] == 8) {
                $order['action'] = "razorpayx_status_update";
                $order['txnid'] = $order['pg_order_id'];
                $this->razorpayx_status_update($order);
                // add_data_in_queue($order, 'payout');
            }
            elseif ($order['withdraw_method'] == 34) {
                $order['action'] = "juspay_status_update";
                $order['txnid'] = $order['pg_order_id'];
                $this->juspay_status_update($order);
                // add_data_in_queue($order, 'payout');
            }

        }
        return true;
    }

     /**
     * juspay payout status check api
     */
    public function juspay_status_update($data) {
        $pending_status = 3;
        $fail_status    = 4;
        $success_status = 5;

        $this->db =  $this->db_user;
        $order_tx_info = $this->db_user->select("O.status AS o_status,T.transaction_status AS t_status,custom_data")
        ->from(ORDER.' O')
        ->join(TRANSACTION.' T','T.order_id = O.order_id','INNER')
        ->where('T.transaction_id',$data['transaction_id'])
        ->get()->row_array();
        //if order table & transaction table  status
        //print_r($order_tx_info);die;
        if($order_tx_info['t_status']!=3 && $order_tx_info['o_status']!=0) { 
            return true;
        }

        $this->load->library('Juspay_payout');
        $JP = new Juspay_payout($this->app_config['auto_withdrawal']['custom_data']);
        //$jp_order_id = $data['order_unique_id'].$data['order_id'];
        $jp_order_id = "JP_".$data['transaction_id'];
        
        $cross_check_status = $JP->get_transfer_status($jp_order_id);

        //print_r($cross_check_status); exit;
       
        if ($cross_check_status['status'] == "FULFILLMENTS_SUCCESSFUL") {
            $update_transaction_data = array();
            $update_transaction_data["gate_way_name"]           = "Juspay Payout";
            $update_transaction_data["transaction_message"]     = $cross_check_status['fulfillments'][0]['transactions'][0]['responseMessage'];
            $update_transaction_data["txn_amount"]              = $cross_check_status['amount'];
            $update_transaction_data["bank_txn_id"]             = $cross_check_status['fulfillments'][0]['id'];
            $update_transaction_data["txn_id"]                  = $data['transaction_id'];
            $update_transaction_data["transaction_status"]      = $success_status;
            $update_transaction_data["payment_mode"]            = $cross_check_status['fulfillments'][0]['transactions'][0]['fulfillmentMethod'];

            $order_custom_data = $order_tx_info['custom_data'];
            $order_custom_data = json_decode($order_custom_data, true);
            $order_custom_data['status'] = $cross_check_status['fulfillments'][0]['transactions'][0]['txnResponse'];
            $order_custom_data['processedOn'] = $cross_check_status['fulfillments'][0]['transactions'][0]['updatedAt'];
            $order_custom_data['transferMode'] = $cross_check_status['fulfillments'][0]['transactions'][0]['fulfillmentMethod'];
            $order_custom_data = json_encode($order_custom_data);
            $data["custom_data"] = $order_custom_data;
            //echo '<pre>'; print_r($update_transaction_data); exit;
            $this->_common_update_payout_tx_status($data, $update_transaction_data);
        } elseif($cross_check_status['status']== "FULFILLMENTS_FAILURE") {
            $update_transaction_data = array();
            $update_transaction_data["gate_way_name"]           = "Juspay Payout";
            $update_transaction_data["transaction_status"]      = $fail_status;
            $update_transaction_data["custom_data"]      = $order_tx_info['custom_data'];
            $this->_common_update_payout_tx_status($data, $update_transaction_data);
        }
        //else true will be returned finally
        return true;
    }

    /**
     * cashfree payout status check api
     */
    public function cashfree_payout_status_update($data)
    {
        $pending_status = 3;
        $fail_status = 4;
        $success_status = 5;

        $this->db =  $this->db_user;
        $order_tx_info = $this->db_user->select("O.status AS o_status,T.transaction_status AS t_status,custom_data")
        ->from(ORDER.' O')
        ->join(TRANSACTION.' T','T.order_id = O.order_id','INNER')
        ->where('T.transaction_id',$data['transaction_id'])
        ->get()->row_array();
        //if order table & transaction table  status
        if($order_tx_info['t_status']!=3 && $order_tx_info['o_status']!=0)
        {
            return true;
        }

        $this->load->library('Cashfree_payout');
        $CF = new Cashfree_payout($this->app_config['auto_withdrawal']['custom_data']);
        $cross_check_status = $CF->get_transfer_status($data['pg_order_id'], $data['transaction_id']);
        // order_id,transaction_id,source,user_id,winning_amount,date_added,payment_gateway_id
        // print_r($cross_check_status);die;
        if ($cross_check_status['subCode'] == 200 && strtolower($cross_check_status['data']['transfer']['status']) == "success") {
            $update_transaction_data = array();
            $update_transaction_data["gate_way_name"]           = "Cashfree Payout";
            $update_transaction_data["transaction_message"]     = $cross_check_status['message'];
            $update_transaction_data["txn_amount"]              = $cross_check_status['data']['transfer']['amount'];
            $update_transaction_data["bank_txn_id"]             = $cross_check_status['data']['transfer']['utr'];
            $update_transaction_data["txn_id"]                  = $data['transaction_id'];
            $update_transaction_data["transaction_status"]      = $success_status;
            $update_transaction_data["payment_mode"]            = $cross_check_status['data']['transfer']['transferMode'];

            $order_custom_data = $order_tx_info['custom_data'];
            $order_custom_data = json_decode($order_custom_data, true);
            $order_custom_data['status'] = $cross_check_status['data']['transfer']['status'];
            $order_custom_data['addedOn'] = $cross_check_status['data']['transfer']['addedOn'];
            $order_custom_data['processedOn'] = $cross_check_status['data']['transfer']['processedOn'];
            $order_custom_data['transferMode'] = $cross_check_status['data']['transfer']['transferMode'];
            $order_custom_data = json_encode($order_custom_data);
            $data["custom_data"] = $order_custom_data;

            $this->_common_update_payout_tx_status($data, $update_transaction_data);

        }elseif (strtolower($cross_check_status['status']) == "failed" || strtolower($cross_check_status['status']) == "reversed") {
            $update_transaction_data = array();
            $update_transaction_data["gate_way_name"]           = "Cashfree Payout";
            $update_transaction_data["transaction_message"]     = $cross_check_status['message'];
            $update_transaction_data["txn_id"]                  = $data['transaction_id'];
            $update_transaction_data["transaction_status"]      = $fail_status;
            $this->_common_update_payout_tx_status($data, $update_transaction_data);
        }elseif($cross_check_status['subCode'] == 200 && strtolower($cross_check_status['data']['transfer']['status']) == "failed")
        {
            $update_transaction_data = array();
            $update_transaction_data["gate_way_name"]           = "Cashfree Payout";
            $update_transaction_data["transaction_message"]     = $cross_check_status['message'];
            $update_transaction_data["txn_id"]                  = $data['transaction_id'];
            $update_transaction_data["transaction_status"]      = $fail_status;
            $this->_common_update_payout_tx_status($data, $update_transaction_data);
        }

        //else true will be returned finally
        return true;
    }

    public function mpesa_payout_status_update($data)
    {
        $failed_status_time = 20;//time in minute
        $this->db =  $this->db_user;
        $order_tx_info = $this->db_user->select("O.status AS o_status,T.transaction_status AS t_status,custom_data")
        ->from(ORDER.' O')
        ->join(TRANSACTION.' T','T.order_id = O.order_id','INNER')
        ->where('T.transaction_id',$data['transaction_id'])
        ->get()->row_array();
        //if order table & transaction table  status
        if($order_tx_info['t_status']!=3 && $order_tx_info['o_status']!=0)
        {
            return true;
        }


        if(in_array($data['status'],[1,5]))
        {
            $res_data = $data['response']['Result']['ResultParameters']['ResultParameter'];
            //mark txn success
            $update_transaction_data = array();
            $update_transaction_data["gate_way_name"]           = "Mpesa Payout";
            $update_transaction_data["transaction_message"]     = $data['response']['Result']['ResultDesc'];
            $update_transaction_data["txn_amount"]              = $res_data[0]['Value'];
            $update_transaction_data["bank_txn_id"]             = $res_data[1]['Value'];
            $update_transaction_data["txn_id"]                  = $data['transaction_id'];
            $update_transaction_data["transaction_status"]      = $this->success_status;
    
        }else{
            $minutes = (strtotime(format_date()) - strtotime($data['date_added'])) / 60;
            if($minutes > $failed_status_time)
            {
            $update_transaction_data = array();
            $update_transaction_data["gate_way_name"]           = "Mpesa Payout";
            $update_transaction_data["transaction_message"]     = "Marked Filed by cron";
            $update_transaction_data["txn_id"]                  = $data['transaction_id'];
            $update_transaction_data["transaction_status"]      = $this->fail_status;
            }

        }
        $this->_common_update_payout_tx_status($data, $update_transaction_data);

        return true;
    }

    public function payumoney_payout_status_update($data)
    {
        // "mrid"=>'10_7a91e925280b05980e43',
        // $data = array(
        //     "mrid"=>'9_bbda47e1693eafed932c',
        //     "from_date"=>'22-01-2023',
        //     "to_date"=>'23-01-2023',
        // );
        if(empty($data))
        {
            return true;
        }

        $order_tx_info = $this->db_user->select("O.status AS o_status,T.transaction_status AS t_status,custom_data")
        ->from(ORDER.' O')
        ->join(TRANSACTION.' T','T.order_id = O.order_id','INNER')
        ->where('T.transaction_id',$data['transaction_id'])
        ->get()->row_array();
        //if order table & transaction table  status
        if($order_tx_info['t_status']!=3 && $order_tx_info['o_status']!=0)
        {
            return true;
        }

        $this->load->library('payu_payout',$this->app_config['auto_withdrawal']['custom_data']);
        $from_date = str_replace('-','%2F',$data['from_date']);
        $to_date = str_replace('-','%2F',$data['to_date']);
        $filter = 'from='.$from_date.'&do='.$to_date.'&merchantRefId='.$data['mrid'];
        $result = $this->payu_payout->get_txn_status($filter);
        if(LOG_TX==1)
        error_log("\n".'Cron Payment payu : '.json_encode($result).'<br>',3,'/var/www/html/jcdc/api/cron/application/logs/payu.log');
        $update_transaction_data = array();
        $update_transaction_data["gate_way_name"]           = "Payumoney Payout";
        if(isset($result['data']['transactionDetails']) && !empty($result['data']['transactionDetails']))
        {
            $result = $result['data']['transactionDetails'][0];
            if(isset($result['txnStatus']) && $result['txnStatus']=='SUCCESS'){
                // $update_transaction_data = array();
                $update_transaction_data["transaction_message"]     = "Transaction Success";
                $update_transaction_data["txn_amount"]              = $result['amount'];
                $update_transaction_data["bank_txn_id"]             = $result['bankTransactionRefNo'];
                $update_transaction_data["txn_id"]                  = $data['transaction_id'];
                $update_transaction_data["transaction_status"]      = $this->success_status;
                $update_transaction_data["payment_mode"]            = $result['transferType'];
            }elseif(isset($result['txnStatus']) && $result['txnStatus']=='FAILED'){
                // $update_transaction_data = array();
                $update_transaction_data["transaction_message"]     = "Transaction Failed";
                $update_transaction_data["txn_id"]                  = $data['transaction_id'];
                $update_transaction_data["bank_txn_id"]             = $result['bankTransactionRefNo'];
                $update_transaction_data["transaction_status"]      = $this->fail_status;
            }
        }else{
            $update_transaction_data["transaction_message"]     = "Transaction Failed";
            $update_transaction_data["txn_id"]                  = $data['transaction_id'];
            $update_transaction_data["bank_txn_id"]             = $result['bankTransactionRefNo'] ? $result['bankTransactionRefNo'] : '';
            $update_transaction_data["transaction_status"]      = $this->fail_status;
            if(LOG_TX)
            error_log("\n".'transacti id  : '.$data['transaction_id'].' is marked fail because pg does not have record'.json_encode($result).'<br>',3,'/var/www/html/jcdc/api/cron/application/logs/payu.log');
        }
        $this->_common_update_payout_tx_status($data, $update_transaction_data);
        return true;
    }

    public function razorpayx_status_update($data)
    {
        $s_status = array("processed","payout.processed","payout_processed");
        $f_status = array("failed","payout.reversed","payout.failed","reversed");
        $p_status = array("processing");
        $failed_status_time = 20;//time in minute
        $this->db =  $this->db_user;
        $pg_order_id = $data['txnid'];
        $order_tx_info = $this->db_user->select("O.status AS o_status,T.transaction_status AS t_status,custom_data")
        ->from(ORDER.' O')
        ->join(TRANSACTION.' T','T.order_id = O.order_id','INNER')
        ->where('T.transaction_id',$data['transaction_id'])
        ->get()->row_array();
        //if order table & transaction table  status
        if($order_tx_info['t_status']!=3 && $order_tx_info['o_status']!=0)
        {
            return true;
        }
        $this->load->library('razorpayx',$this->app_config['auto_withdrawal']['custom_data']);
        $razorpayx_txn_status = $this->razorpayx->razorpayx_txn_status($pg_order_id);
        $txnid = explode("_",$razorpayx_txn_status['reference_id']);
        $update_transaction_data = array();
        $update_transaction_data["gate_way_name"]           = "Razorpayx Payout";
        $update_transaction_data["transaction_message"]     =  $razorpayx_txn_status['status_details']['description'];
        $update_transaction_data["txn_amount"]              =  $razorpayx_txn_status['amount']/100;
        $update_transaction_data["bank_txn_id"]             = $razorpayx_txn_status['utr'];
        $update_transaction_data["txn_id"]                  = $txnid[0];
        // print_r($razorpayx_txn_status);die;
        
        if(in_array($razorpayx_txn_status['status_details']['reason'],$s_status))
        {
            //mark txn success
            $update_transaction_data["transaction_status"]      = $this->success_status;
    
        }elseif(in_array($razorpayx_txn_status['status'],$f_status)) // fail status
        {
            $update_transaction_data["transaction_status"]      = $this->fail_status;
        }else{
            $minutes = (strtotime(format_date()) - strtotime($data['date_added'])) / 60;
            if($minutes > $failed_status_time)
            {
            $update_transaction_data["transaction_message"]     = "Marked Filed by cron";
            $update_transaction_data["txn_id"]                  = $data['transaction_id'];
            $update_transaction_data["transaction_status"]      = $this->fail_status;
            }
        }
        $this->_common_update_payout_tx_status($data, $update_transaction_data);
        return true;
    }

}