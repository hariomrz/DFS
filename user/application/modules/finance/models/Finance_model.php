<?php
class Finance_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        $this->finance_lang = $this->lang->line("finance");
    }

    /** 
     * Used to get notification type details by source id
     * @param int $source
     * @return array
     */
    function notify_type_by_source($source) {
        $arr = array(
            "4" => ["notification_type" => 9, "subject" => $this->finance_lang["referral_bonus_subject"], "notification_destination" => 7],
            "8" => ["notification_type" => 7, "subject" => $this->finance_lang["withdraw_request_subject"], "notification_destination" => 7],
            "12" => ["notification_type" => 33, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 7],
            "13" => ["notification_type" => 34, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 7],
            "14" => ["notification_type" => 35, "subject" => $this->finance_lang["friend_with_benefits"], "notification_destination" => 7],
            "15" => ["notification_type" => 36, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 7],
            "16" => ["notification_type" => 37, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 7],
            "50" => ["notification_type" => 50, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "51" => ["notification_type" => 51, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "52" => ["notification_type" => 52, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "53" => ["notification_type" => 53, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "54" => ["notification_type" => 54, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "55" => ["notification_type" => 55, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "56" => ["notification_type" => 56, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "57" => ["notification_type" => 57, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "58" => ["notification_type" => 58, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "59" => ["notification_type" => 59, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "60" => ["notification_type" => 60, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "61" => ["notification_type" => 61, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "62" => ["notification_type" => 62, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "63" => ["notification_type" => 63, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "64" => ["notification_type" => 64, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "65" => ["notification_type" => 65, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "66" => ["notification_type" => 66, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "67" => ["notification_type" => 67, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "86" => ["notification_type" => 86, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "87" => ["notification_type" => 87, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "88" => ["notification_type" => 88, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "89" => ["notification_type" => 89, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "90" => ["notification_type" => 90, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "91" => ["notification_type" => 91, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "92" => ["notification_type" => 92, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "93" => ["notification_type" => 93, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "94" => ["notification_type" => 94, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "95" => ["notification_type" => 95, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "96" => ["notification_type" => 96, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "97" => ["notification_type" => 97, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "98" => ["notification_type" => 98, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "99" => ["notification_type" => 99, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "100" => ["notification_type" => 100, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "105" => ["notification_type" => 105, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "106" => ["notification_type" => 106, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "107" => ["notification_type" => 107, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "132" => ["notification_type" => 142, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "133" => ["notification_type" => 143, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "134" => ["notification_type" => 144, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "138" => ["notification_type" => 145, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "139" => ["notification_type" => 146, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "140" => ["notification_type" => 147, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "141" => ["notification_type" => 148, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "142" => ["notification_type" => 149, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "143" => ["notification_type" => 150, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            '144' => ["notification_type" => 138, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            '145' => ["notification_type" => 139, "subject" => $this->finance_lang["coin_redemption_reward_subject"], "notification_destination" => 5],
            '146' => ["notification_type" => 140, "subject" => $this->finance_lang["coin_redemption_reward_subject"], "notification_destination" => 5],
            '147' => ["notification_type" => 141, "subject" => $this->finance_lang["coin_redemption_reward_subject"], "notification_destination" => 5],
            //'40' => ["notification_type" => 40, "subject" => $this->finance_lang["prediction_joined_subject"], "notification_destination" => 3],
            "153" => ["notification_type" => 153, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "154" => ["notification_type" => 154, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "155" => ["notification_type" => 155, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "156" => ["notification_type" => 156, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "157" => ["notification_type" => 157, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "158" => ["notification_type" => 158, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "159" => ["notification_type" => 159, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "160" => ["notification_type" => 160, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "161" => ["notification_type" => 161, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "162" => ["notification_type" => 162, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "163" => ["notification_type" => 163, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "164" => ["notification_type" => 164, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "165" => ["notification_type" => 165, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "166" => ["notification_type" => 166, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "167" => ["notification_type" => 167, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "168" => ["notification_type" => 168, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "169" => ["notification_type" => 169, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "170" => ["notification_type" => 170, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "171" => ["notification_type" => 171, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "172" => ["notification_type" => 172, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "173" => ["notification_type" => 173, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "174" => ["notification_type" => 174, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "181" => ["notification_type" => 181, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "320" => ["notification_type" => 420, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "321" => ["notification_type" => 421, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            "470" => ["notification_type" => 580, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3],
            );

            if(isset($arr[$source]))
            {
               return  $arr[$source];
            }
            else
            {
                return 0;
            }

    }

     /**
     * Used for save data in oder table on withdraw request
     * @param array $data_arr
     * @return array
     */
    function withdraw_coins($data_arr) {
        $current_date = format_date();
        $amount = $data_arr["amount"];
        $order_data = array();
        $order_data["user_id"] = $data_arr['user_id'];
        $order_data["source"] = $data_arr['source'];
        $order_data["source_id"] = $data_arr['source_id'];
        $order_data["type"] = 1;
        $order_data["date_added"] = $current_date;
        $order_data["modified_date"] = $current_date;
        $order_data["plateform"] = 1;
        $order_data["season_type"] = 1;
        $order_data["status"] = $data_arr['status'];
        $order_data["real_amount"] = 0;
        $order_data["bonus_amount"] = 0;
        $order_data["winning_amount"] = 0;
        $order_data["points"] = $amount;
        $order_data["withdraw_method"] = 1;
        $order_data["reason"] = !empty($data_arr['reason']) ? $data_arr['reason'] : '';
        if (!empty($data_arr['email'])) {
            $order_data["email"] = $data_arr['email'];
        }
        $user_balance = $this->get_user_balance($data_arr["user_id"]);

        if(isset($data_arr['entry_type']))
        {
            $custom_data = array('entry_type' => $data_arr['entry_type']);
            $order_data["custom_data"] = json_encode($custom_data);
        }

        //If requested amount is greater then total amount.
        if (($user_balance["point_balance"] < $amount)) {
            throw new Exception($this->finance_lang["insufficent_amount"]);
        }
        $this->db->trans_start();
        $order_data['order_unique_id'] = $this->_generate_order_key();
        $this->db->insert(ORDER, $order_data);
        $order_id = $this->db->insert_id();
        if ($order_id) {
            $real_bal = $user_balance['real_amount'] - $order_data["real_amount"];
            $bonus_bal = $user_balance['bonus_amount'] - $order_data["bonus_amount"];
            $winning_bal = $user_balance['winning_amount'] - $order_data["winning_amount"];
            $point_bal = $user_balance['point_balance'] - $order_data["points"];
            // update user balance!
            $this->update_user_balance($order_data["user_id"], $order_data, 'withdraw');
            //$this->update_user_balance($order_data["user_id"], $real_bal, $bonus_bal, $winning_bal, $point_bal);

            if($amount > 0) {
                $this->load->helper('queue_helper');
                $coin_data = array(
                    'oprator' => 'withdraw', 
                    'user_id' => $order_data["user_id"], 
                    'total_coins' => $amount, 
                    'bonus_date' => format_date("today", "Y-m-d")
                );
                add_data_in_queue($coin_data, 'user_coins');
            }

            $withdraw_method_arr = array('0' => 'NA', '1' => 'Bank', '2' => 'PayTm', '3' => 'Paypal', '4' => 'Admin');
            $withdraw_method = ($withdraw_method_arr[$order_data["withdraw_method"]]) ? $withdraw_method_arr[$order_data["withdraw_method"]] : '';
            if (!empty($withdraw_method)) {
                $this->load->model('notification/Notify_nosql_model');

                $input["reason"] = 'Admin ';
                $input['payment_option'] = $withdraw_method;
                if (isset($order_data['reason']) && $order_data['reason'] != "") {
                    $input['reason'] = $order_data['reason'];
                }

                $user_detail = $this->get_single_row('email, user_name', USER, array("user_id" => $data_arr["user_id"]));
                $input["user_name"] = $user_detail['user_name'];
                $input["user_id"] = $user_detail['user_name'];
                $input["amount"] = $amount;

                if(!empty($data_arr['event']))
                {
                    $input["event"] = $data_arr['event'];
                }

                $tmp = array();
                $tmp["source_id"] = $order_data["source_id"];
                $tmp["user_id"] = $data_arr['user_id'];
                $tmp["to"] = $user_detail['email'];
                $tmp["user_name"] = $user_detail['user_name'];
                $tmp["added_date"] = $current_date;
                $tmp["modified_date"] = $current_date;
                $tmp["content"] = json_encode($input);

                $notify_data = $this->notify_type_by_source($data_arr["source"]);

                if(!empty($notify_data) && !in_array($data_arr["source"],array(147)))
                {
                    $tmp['notification_type'] = $notify_data['notification_type'];
                    $tmp['subject'] = $notify_data['subject'];
                    $tmp['notification_destination'] = $notify_data['notification_destination'];
                    $this->Notify_nosql_model->send_notification($tmp);
                }    
               
            }
        } else {
            $this->db->trans_rollback();
            throw new Exception('Something went wrong during contest join.');
        }
        $this->db->trans_complete();
        $this->db->trans_strict(FALSE);
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();

            $user_balance['point_balance'] = $point_bal;
            $user_balance['order_id'] = $order_id;
            return $user_balance;
        }
    }

    /**
     * Used to deposit fund in user account
     * @param array $input
     * @return int
     */
    function deposit_fund($input) {
        $this->finance_lang = $this->config->item('finance_lang');

        $order_id = $this->create_order($input);

        if ($order_id) { // Add notification
            if ($input["source"] != 7 && $input["source"] != 15 && $input["source"] != 550) {
                $this->load->model('notification/Notify_nosql_model');
                $today = format_date();
                $tmp = array();
                $user_detail = $this->get_single_row('email, user_name', USER, array("user_id" => $input["user_id"]));
                $input["user_name"] = $user_detail['user_name'];

                $notify_data = $this->notify_type_by_source($input["source"]);

                $tmp['notification_type'] = $notify_data['notification_type'];
                $tmp['subject'] = $notify_data['subject'];
                $tmp['notification_destination'] = $notify_data['notification_destination'];

                if(in_array($tmp['notification_type'], array(53, 54, 55))) {
                    $input['banner_image'] = ''; 
                    // $tmp['device_ids'] = $this->get_all_device_id(array($input["user_id"]));                       
                    $device_ids = $this->get_saperate_device_ids(array($input["user_id"]));
                    $tmp['device_ids'] = $device_ids['device_ids'];
                    $tmp['ios_device_ids'] = $device_ids['ios_device_ids'];
                    $tmp['custom_notification_subject'] = 'Your reference is successful 🎊🤝🎊';
                    $tmp['custom_notification_text'] = $user_detail['user_name'].' joined using your reference code. Check out what you earned 💰💰';
                } else if($tmp['notification_type'] == 420) {
                    $input['banner_image'] = ''; 
                    $device_ids = $this->get_saperate_device_ids(array($input["user_id"]));
                    $tmp['device_ids'] = $device_ids['device_ids'];
                    $tmp['ios_device_ids'] = $device_ids['ios_device_ids'];
                    $tmp['custom_notification_subject'] = 'Wallet Credited 🤩';
                    $tmp['custom_notification_text'] = $user_detail['user_name'].' joined using your affiliate code. Wallet credited with ₹ '.$input['amount'].'. Click here for more details 💰💰';
                }

                $tmp["subject"] = $this->finance_lang["deposit_email_subject"];
                $tmp["source_id"] = $input["source_id"];
                $tmp["notification_destination"] = (!empty($tmp["notification_destination"])) ? $tmp["notification_destination"] : 7; //  Web, Push, Email

                $tmp["user_id"] = $input['user_id'];
                $tmp["to"] = $user_detail['email'];
                $tmp["user_name"] = $user_detail['user_name'];
                $tmp["added_date"] = $today;
                $tmp["modified_date"] = $today;
                $tmp["content"] = json_encode($input);
                

                $this->Notify_nosql_model->send_notification($tmp);
            }
        } else {
            return 0;
        }
        return $order_id;
    }
    
    /**
     * Used to create order
     * @param array $input
     * @return int
     */
    function create_order($input) {
        $today = format_date();
        $orderData = array();
        $orderData["user_id"] = $input['user_id'];
        $orderData["source"] = $input['source'];
        $orderData["source_id"] = $input['source_id'];
        $orderData["type"] = 0;
        $orderData["date_added"] = $today;
        $orderData["modified_date"] = $today;
        $orderData["status"] = 0;
        $orderData["real_amount"] = 0;
        $orderData["bonus_amount"] = 0;
        $orderData["winning_amount"] = 0;
        $orderData["points"] = 0;
        $orderData["reason"] = isset($input['reason']) ? $input['reason'] : '';

        if(!empty($input['custom_data']))
        {
            $orderData["custom_data"] = $input['custom_data'];
        }

        $amount = $input['amount'];

        switch ($input['cash_type']) {
            case 0:
                $orderData["real_amount"] = $amount; // Real Money
                break;
            case 1:
                $orderData["bonus_amount"] = $amount; // Bonus Money 
                break;
            case 2:
                $orderData["points"] = $amount; // Point Balance
                break;
            case 4:
                $orderData["winning_amount"] = $amount; // Winning Balance 
                break;
            case 5:
                $orderData["cb_amount"] = $amount; // Cb Balance 
                break;
            default:
                return FALSE;
                break;
        }

        /* Add source for which status will be set to one */
        $status_one_srs = [0, 2, 4, 9, 10, 12, 13, 14, 16, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 30, 31, 32, 102, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 105, 106, 107, 132, 133, 134, 135, 136, 137, 138, 139, 140, 141, 142, 143,144,145,146,147,153,154,155,156,157,158,159,160,161,162,163,164,165,166,167,168,169,170,171,172,173,320,321,550];

        if (in_array($orderData["source"], $status_one_srs)) {
            $orderData["status"] = 1;
        } else if ($orderData["source"] == 3) {
            $orderData["real_amount"] = 0;
            $orderData["bonus_amount"] = 0;
            $orderData["winning_amount"] = $amount;
            $orderData["status"] = 1;
        }

        // new gst calculation
        if($orderData["source"] == 7){
            $gst_cal = gst_calculate($orderData["real_amount"],$this->app_config);
            $orderData["tds"] = isset($gst_cal['gst'])?$gst_cal['gst']:0;
            
            $gst_rate = isset($gst_cal['gst_rate'])?$gst_cal['gst_rate']:0;
            $gst_rate_arr = array('gst_rate'=>$gst_rate);
            if(!empty($orderData['custom_data']))
            {
                $custom_data_arr = json_decode($orderData['custom_data'],true);
                $custom_data_arr = array_merge($custom_data_arr,$gst_rate_arr);
                $orderData['custom_data'] = json_encode($custom_data_arr);
            }else{
                $orderData['custom_data'] = json_encode($gst_rate_arr);
            }
        }

        //$this->db->trans_start();
        $orderData['order_unique_id'] = $this->_generate_order_key();
        $this->db->insert(ORDER, $orderData);
        $order_id = $this->db->insert_id();

        if (!$order_id) {
            return FALSE;
        }else{
            $this->load->helper('queue_helper');
            $coin_data = array(
                'oprator' => 'add', 
                'user_id' => $input['user_id'], 
                'total_coins' => $orderData["points"], 
                'bonus_date' => format_date("today", "Y-m-d")
            );
            add_data_in_queue($coin_data, 'user_coins');
        }

        /*$user_balance = $this->get_user_balance($orderData["user_id"]);
        $real_bal = $user_balance['real_amount'] + $orderData["real_amount"];
        $bonus_bal = $user_balance['bonus_amount'] + $orderData["bonus_amount"];
        $winning_bal = $user_balance['winning_amount'] + $orderData["winning_amount"];
        $point_bal = $user_balance['point_balance'] + $orderData["points"];   // update point balance
        */
        // Update User balance for order with completed status .
        if ($orderData["status"] == 1) {
            $this->update_user_balance($orderData["user_id"], $orderData, 'add');
        }
        //$this->db->trans_complete();
        return $order_id;
    }

    function insert_order($orderData)
    {
        $this->db->insert(ORDER, $orderData);
        $order_id = $this->db->insert_id();
        return $order_id;
    }

    /**
     * Used for save data in oder table on withdraw request
     * @param array $data_arr
     * @return array
     */
    function withdraw($data_arr) {
        $current_date = format_date();
        $amount = $data_arr["amount"];
        $order_data = array();
        $order_data["user_id"] = $data_arr['user_id'];
        $order_data["source"] = $data_arr['source'];
        $order_data["source_id"] = $data_arr['source_id'];
        $order_data["type"] = 1;
        $order_data["date_added"] = $current_date;
        $order_data["modified_date"] = $current_date;
        $order_data["plateform"] = 1;
        $order_data["season_type"] = 1;
        $order_data["status"] = $data_arr['status'];
        $order_data["real_amount"] = 0;
        $order_data["bonus_amount"] = 0;
        $order_data["winning_amount"] = $amount;
        $order_data["points"] = 0;
        $order_data["tds"] = 0;
        $order_data["withdraw_method"] = 1;
        $pg_fee_pg_arr = [15,17,8]; //crypto & cashfree

        if(isset($data_arr['withdraw_method']) && !empty($data_arr['withdraw_method']))
        {
            $order_data["withdraw_method"] = $data_arr['withdraw_method'];
        }

        if(isset($data_arr['auto_withdrawal']))
        {
            $order_data["custom_data"] = json_encode($data_arr['auto_withdrawal']);
        }

        if(isset($data_arr['bank_detail']) && !empty($data_arr['bank_detail']))
        {
            $data_arr['bank_detail'] = json_encode($data_arr['bank_detail']);
            $order_data["custom_data"] = json_encode(array_merge(json_decode($order_data["custom_data"], true),json_decode($data_arr['bank_detail'], true)));
        }

        $order_data["reason"] = !empty($data_arr['reason']) ? $data_arr['reason'] : '';
        if (!empty($data_arr['email'])) {
            $order_data["email"] = $data_arr['email'];
        }
        $user_balance = $this->get_user_balance($data_arr["user_id"]);

        //If requested amount is greater then total amount.
        if (($user_balance["winning_amount"] < $amount)) {
            throw new Exception($this->finance_lang["insufficent_amount"]);
        }

        if(in_array($order_data["withdraw_method"],$pg_fee_pg_arr) && $data_arr['isIW']==1)
        // if($this->app_config['allow_crypto']['key_value']==1 || $this->app_config['cashfree_payout']['key_value']==1)
        {
            if(strstr($this->app_config['auto_withdrawal']['custom_data']['pg_fee'],'%'))
            {
                $pg_char_per = (float)substr($this->app_config['auto_withdrawal']['custom_data']['pg_fee'],0,-1);
                if($pg_char_per > 0)
                {
                    $pg_fee    = array('pg_fee'=>($pg_char_per*$amount)/100);
                    $order_data["custom_data"] = json_encode(array_merge(json_decode($order_data["custom_data"], true),$pg_fee));
                }
            }else{
                $pg_char_per = $this->app_config['auto_withdrawal']['custom_data']['pg_fee'];
                if($pg_char_per > 0)
                {
                    $pg_fee    = array('pg_fee'=>$pg_char_per);
                    $order_data["custom_data"] = json_encode(array_merge(json_decode($order_data["custom_data"], true),$pg_fee));
                }
            }
        }

        //tds calculation on net winning
        if($data_arr['source'] == 8 && $user_balance['net_winning'] > 0){
            if(isset($this->app_config['allow_tds']) && $this->app_config['allow_tds']['key_value'] == "1"){
                $tds_info = $this->app_config['allow_tds']['custom_data'];
                if(isset($tds_info['indian']) && $tds_info['indian'] == 1){
                    $taxable_amt = $order_data["winning_amount"];
                    if($user_balance['net_winning'] < $order_data["winning_amount"]){
                        $taxable_amt = $user_balance['net_winning'];
                    }
                    $order_data["tds"] = number_format((($taxable_amt * $tds_info['percent']) / 100),2,'.','');
                    $fy_arr = get_financial_year();
                    $tds_arr = array("fy"=>$fy_arr['fy'],"tds_rate"=>$tds_info['percent'],"net_winning"=>$taxable_amt);
                    $order_data["custom_data"] = json_encode(array_merge(json_decode($order_data["custom_data"],true),$tds_arr));
                    
                }
            }
        }

        if(!empty($order_data["custom_data"]))
        {
            json_encode($order_data["custom_data"]);
        }
        $this->db->trans_start();
        $order_data['order_unique_id'] = $this->_generate_order_key();
        $this->db->insert(ORDER, $order_data);
        $order_id = $this->db->insert_id();
        if ($order_id) {
            $real_bal = $user_balance['real_amount'] - $order_data["real_amount"];
            $bonus_bal = $user_balance['bonus_amount'] - $order_data["bonus_amount"];
            $winning_bal = $user_balance['winning_amount'] - $order_data["winning_amount"];
            $point_bal = $user_balance['point_balance'] - $order_data["points"];
            // update user balance!
            $this->update_user_balance($order_data["user_id"], $order_data, 'withdraw');
            //$this->update_user_balance($order_data["user_id"], $real_bal, $bonus_bal, $winning_bal, $point_bal);

            $withdraw_method_arr = array('0' => 'NA', '1' => 'Bank', '2' => 'PayTm', '3' => 'Paypal', '4' => 'Admin','15'=>'Crypto','17'=>'Cashfree','8'=>'Razorpayx', '34'=>'Juspay');
            $withdraw_method = ($withdraw_method_arr[$order_data["withdraw_method"]]) ? $withdraw_method_arr[$order_data["withdraw_method"]] : '';
            if (!empty($withdraw_method)) {
                $this->load->model('notification/Notify_nosql_model');

                $input["reason"] = 'Admin ';
                $input['payment_option'] = $withdraw_method;
                if (isset($order_data['reason']) && $order_data['reason'] != "") {
                    $input['reason'] = $order_data['reason'];
                }

                $user_detail = $this->get_single_row('email, user_name', USER, array("user_id" => $data_arr["user_id"]));
                $input["user_name"] = $user_detail['user_name'];
                $input["user_id"] = $user_detail['user_name'];
                $input["amount"] = $amount;
                if(isset($data_arr['isIW']) && $data_arr['isIW']==1)
                {
                $input["isIW"] = $data_arr['isIW'];
                $input["pg_fee"] = $data_arr['pg_fee'];
                }

                $tmp = array();
                $tmp["source_id"] = $order_data["source_id"];
                $tmp["user_id"] = $data_arr['user_id'];
                $tmp["to"] = $user_detail['email'];
                $tmp["user_name"] = $user_detail['user_name'];
                $tmp["added_date"] = $current_date;
                $tmp["modified_date"] = $current_date;
                $tmp["content"] = json_encode($input);

                $notify_data = $this->notify_type_by_source($data_arr["source"]);

                $tmp['notification_type'] = $notify_data['notification_type'];
                $tmp['subject'] = $notify_data['subject'];
                $tmp['notification_destination'] = $notify_data['notification_destination'];

                $this->Notify_nosql_model->send_notification($tmp);
            }
        } else {
            $this->db->trans_rollback();
            throw new Exception('Something went wrong during contest join.');
        }
        $this->db->trans_complete();
        $this->db->trans_strict(FALSE);
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
        }
        return array('order_id' => $order_id, 'order_unique_id' => $order_data['order_unique_id'],"tds"=>$order_data["tds"]);
    }
    
    /**
     * Used to update promo code earning details
     * @param array $data_arr
     * @param int $promo_code_earning_id
     * @return int
     */
    function update_promo_code_earning_details($data_arr, $promo_code_earning_id) {
        $this->db->where('promo_code_earning_id', $promo_code_earning_id)->update(PROMO_CODE_EARNING, $data_arr);
        return $this->db->affected_rows();
    }
    
    /**
     * Used to update deal earning details
     * @param array $data_arr
     * @param int $deal_earning_id
     * @return int
     */
    function update_deal_earning_details($data_arr, $deal_earning_id) {
        $this->db->where('deal_earning_id', $deal_earning_id)->update(DEALS_EARNING, $data_arr);
        return $this->db->affected_rows();
    }
    
    /**
     * Used to get pending withdrawal
     * @param int $user_id
     * @return array
     */
    function get_pending_withdrawal($user_id) {
        $this->db->select('ORD.order_unique_id, ORD.real_amount, ORD.bonus_amount, ORD.winning_amount, ORD.points, ORD.source, ORD.source_id, ORD.reason, ORD.type, ORD.date_added, ORD.status ', FALSE);
        $this->db->from(ORDER . " AS ORD");
        $this->db->where('ORD.source', 8);
        $this->db->where('ORD.status', 0);
        $this->db->where('ORD.user_id', $user_id);
        $this->db->order_by('ORD.order_id', 'DESC');
        $query = $this->db->get();
        return $query->row_array();
    }
    
    /**
     * Used to get transaction history
     * @param int $user_id
     * @param int $offset
     * @param int $limit
     * @return array
     */
    function get_transaction_history($user_id, $offset, $limit) {
        $source = $this->input->post('source'); //3 => gamewon,7 for deposit
        $only_bonus = $this->input->post('only_bonus');
        $only_coins = $this->input->post('only_coins');
        $only_winning = $this->input->post('only_winning');
        $only_real = $this->input->post('only_real');
        $this->db->select('ORD.order_unique_id, ORD.real_amount, ORD.bonus_amount, ORD.winning_amount, ORD.points, ORD.source, ORD.source_id, ORD.reason, ORD.type, ORD.date_added, ORD.status,ORD.custom_data,ORD.tds,ORD.is_process_gst,ORD.cb_amount', FALSE)
                ->from(ORDER . " AS ORD")
                ->limit($limit, $offset);

        // if (!empty($source)) {
        //     if($source == 3){
        //         //241 network fantasy
        //         $this->db->where_in('ORD.source', array("3","230","320","321","241","21"));
        //     }else{
        //         $this->db->where('ORD.source', $source);
        //     }
        // }

        if (!empty($only_bonus)) {
            $this->db->where('ORD.bonus_amount>', 0);
        }

        if (!empty($only_coins)) {
            $this->db->where('ORD.points>', 0);
        }

        if (!empty($only_winning)) {
            $this->db->where('ORD.winning_amount>', 0);
        }

        if (!empty($only_real)) {
            $this->db->where('ORD.real_amount>', 0);
        }

        $query = $this->db->where('ORD.user_id', $user_id)
                ->order_by('ORD.order_id', 'DESC')
                ->get();

        $is_debug = $this->input->post('is_debug');

        if($is_debug == 1)
        {
            echo $this->db->last_query();die;
        }
        return $query->result_array();
    }
   
    /**  Used to get user balance 
     * @param int $user_id
     * @return array
     */
    function get_user_balance($user_id) {
        $this->db->select("user_id,balance as real_amount, bonus_balance as bonus_amount, winning_balance as winning_amount,point_balance,phone_verfied,email_verified,pan_verified,is_bank_verified,pan_image,total_winning,net_winning,added_date,cb_balance");
        $this->db->from(USER);
        $this->db->where(array("user_id" => $user_id));
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row_array();
        return array(
            "bonus_amount" => ($result["bonus_amount"] && $result["bonus_amount"] > 0) ? $result["bonus_amount"] : 0,
            "real_amount" => ($result["real_amount"] && $result["real_amount"] > 0) ? $result["real_amount"] : 0,
            "winning_amount" => ($result["winning_amount"] && $result["winning_amount"] > 0) ? $result["winning_amount"] : 0,
            "point_balance" => ($result["point_balance"] && $result["point_balance"] > 0) ? $result["point_balance"] : 0,
            "net_winning" => ($result["net_winning"]) ? $result["net_winning"] : 0,
            "cb_balance" => ($result["cb_balance"]) ? $result["cb_balance"] : 0,
            'phone_verfied' => $result["phone_verfied"],
            'email_verified' => $result["email_verified"],
            'pan_verified' => $result["pan_verified"],
            'is_bank_verified' => $result["is_bank_verified"],
            'pan_image' => $result["pan_image"],
            'total_winning' => $result["total_winning"],
            'added_date' => $result["added_date"]

        );
    }

    /**  Used to update user balance 
     * @param int $user_id
     * @param float $real_bal
     * @param float $bonus_bal
     * @param float $winning_bal
     * @param float $point_bal  
     * @return int
     */
    function update_user_balance($user_id, $balance_arr,$oprator='add',$order_id='') {
        if(!empty($order_id)){
            $check_exist= $this->get_single_row('payout_processed',ORDER,['order_id'=>$order_id,"payout_processed"=>2]);
            if(!empty($check_exist)) return 0;
            $this->db->update(ORDER,['payout_processed'=>2],['order_id'=>$order_id]);
        }

        if(empty($balance_arr)){
            return false;
        }
        if(isset($balance_arr['real_amount']) && $balance_arr['real_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('balance', 'balance - '.$balance_arr['real_amount'], FALSE);
            }else{
                $this->db->set('balance', 'balance + '.$balance_arr['real_amount'], FALSE);
            }
            if(isset($balance_arr['source']) && $balance_arr['source'] == "7" && $oprator == 'add'){
                $this->db->set('total_deposit', 'total_deposit + '.$balance_arr['real_amount'], FALSE);
            }
        }
        if(isset($balance_arr['bonus_amount']) && $balance_arr['bonus_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('bonus_balance', 'bonus_balance - '.$balance_arr['bonus_amount'], FALSE);
            }else{
                $this->db->set('bonus_balance', 'bonus_balance + '.$balance_arr['bonus_amount'], FALSE);
            }
            $this->load->helper('queue_helper');
            $bonus_data = array('oprator' => $oprator, 'user_id' => $user_id, 'total_bonus' => $balance_arr['bonus_amount'], 'bonus_date' => format_date("today", "Y-m-d"));
            add_data_in_queue($bonus_data, 'user_bonus');
        }
        if(isset($balance_arr['winning_amount']) && $balance_arr['winning_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('winning_balance', 'winning_balance - '.$balance_arr['winning_amount'], FALSE);
            }else{
                $this->db->set('winning_balance', 'winning_balance + '.$balance_arr['winning_amount'], FALSE);
            }
        }
        if(isset($balance_arr['points']) && $balance_arr['points'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('point_balance', 'point_balance - '.$balance_arr['points'], FALSE);
            }else{
                $this->db->set('point_balance', 'point_balance + '.$balance_arr['points'], FALSE);
            }
            // $this->load->helper('queue_helper');
            // $coins_data = array('oprator' => $oprator, 'user_id' => $user_id, 'total_coins' => $balance_arr['points'], 'bonus_date' => format_date("today", "Y-m-d"));
            // add_data_in_queue($coins_data, 'user_coins');
        }
        //for tds deduction net winning update on withdrawal
        if(isset($balance_arr['source']) && $balance_arr['source'] == "8" && $oprator == "withdraw" && isset($balance_arr['tds']) && $balance_arr['tds'] > 0){
            $custom_data = json_decode($balance_arr['custom_data'],TRUE);
            if(isset($custom_data['net_winning']) && $custom_data['net_winning'] > 0){
                $this->db->set('net_winning', 'net_winning - '.$custom_data['net_winning'], FALSE);
            }       
        }

        //for gst bonus amount update
        if(isset($balance_arr['source']) && isset($balance_arr['cb_amount']) && $balance_arr['cb_amount'] > 0){
            if($oprator == "withdraw"){
                $this->db->set('cb_balance', 'cb_balance - '.$balance_arr['cb_amount'], FALSE);
            }else{
                $this->db->set('cb_balance', 'cb_balance + '.$balance_arr['cb_amount'], FALSE);
            }
        }

        $this->db->where('user_id', $user_id);
        $this->db->update(USER);
        return $this->db->affected_rows();
    }

    /**  Used to update user total deposit value
     * @param int $user_id
     * @param float $balance 
     * @return int
     */
    function update_user_total_deposit($user_id, $balance) {
        $this->db->set('total_deposit', 'total_deposit + ' . $balance, FALSE);
        $this->db->where('user_id', $user_id);
        $this->db->update(USER);
        return $this->db->affected_rows();
    }
    
    /**  Used to check user first order or not
     * @param int $user_id
     * @return bool
     */
    function check_first_deposit($user_id) {
        $sql = $this->db->select('order_id')
                        ->from(ORDER)
                        ->where('user_id', $user_id)
                        ->where('status', 1)
                        ->where('source', 7)
                        ->where('type', 0)
                        ->get()->num_rows();

        return ($sql == 1) ? true : false;
    }

    /**  Used to get order detail if pending
     * @param int $order_id
     * @return array
     */
    function get_pending_order_detail($order_id) {
        return $this->db->where('order_id', $order_id)
                        ->where('status != ', 1)
                        ->limit(1)
                        ->get(ORDER, 1)
                        ->row_array();
    }

    /**  Used to update order status from pending to failed or complete
     * @param int $order_id
     * @param int $status
     * @param int $source_id
     * @param string $reason
     * @return int
     */
    function update_order_status($order_id, $status, $source_id = 0, $reason = '') {
        $data = array(
            "status" => $status,
            "source_id" => $source_id,
            "modified_date" => format_date(),
            "reason" => $reason
        );
        $this->db->where('order_id', $order_id)
                ->where('status', 0)
                ->update(ORDER, $data);
        return $this->db->affected_rows();
    }

    /**
     * CREATE Transaction row
     * @param array $data Trasaction Insert Data
     * @return int Transaction ID/0
     */
    function create_transaction($data) {
        $this->db->insert(TRANSACTION, $data);
        return $this->db->insert_id();
    }

    /**
     * Used to update transaction data
     * @param array $data
     * @param int $transaction_id
     * @return int
     */
    function update_transaction($data, $transaction_id) {

        $this->db->where('transaction_id', $transaction_id)->update(TRANSACTION, $data);


        //echo $this-db->last_query();exit;
        return $this->db->affected_rows();
    }

    /**
     * used to get transaction details
     * @param int $transaction_id transaction ID
     * @return array
     */
    function get_transaction($transaction_id) {
        $txn_data = $this->db->where('transaction_id', $transaction_id)->get(TRANSACTION, 1)->row_array();
        return $txn_data;

    }

    /**
     * used to get transaction details
     * @param int $transaction_id transaction ID
     * @return array
     */
    function get_transaction_info($transaction_id) {
        $sql = $this->db->select("T.*,O.real_amount,O.status,O.tds", FALSE)
                ->from(TRANSACTION . " AS T")
                ->join(ORDER." AS O", "O.order_id = T.order_id", "INNER")
                ->where("T.transaction_id", $transaction_id)
                ->get();
        $txn_data = $sql->row_array();
        return $txn_data;

    }

    /**
     * Used to check promo code
     * @param array $input
     * @return array
     */
    function check_promo_code_details($input) {
        $current_date = format_date("today", "Y-m-d H:i:s");
        $sql = $this->db->select("PC.*,count(PCE.promo_code_earning_id) as total_used", FALSE)
                ->from(PROMO_CODE . " AS PC")
                ->join(PROMO_CODE_EARNING . " AS PCE", "PCE.promo_code_id = PC.promo_code_id AND PCE.is_processed='1' AND PCE.user_id='" . $this->user_id . "'", "LEFT")
                ->where("status", "1")
                ->where("promo_code", $input['promo_code'])
                ->where("DATE_FORMAT(start_date,'%Y-%m-%d %H:%i:%s') <= ", date('Y-m-d H:i:s', strtotime($current_date)))
                ->where("DATE_FORMAT(expiry_date,'%Y-%m-%d %H:%i:%s') >= ", date('Y-m-d H:i:s', strtotime($current_date)))
                ->group_by("PC.promo_code_id")
                ->get();
        return $sql->row_array();
    }

    /**
     * Used for get deal details
     * @param array $input request data
     * @return array
     */
    function check_deal_details($input) {
        $sql = $this->db->select("D.*,count(DE.deal_earning_id) as total_used", FALSE)
                ->from(DEALS . " AS D")
                ->join(DEALS_EARNING . " AS DE", "DE.deal_id = D.deal_id AND DE.is_processed='1' AND DE.user_id='" . $this->user_id . "'", "LEFT")
                ->where("D.status", "1")
                ->where("D.is_deleted", "0")
                ->where("D.deal_id", $input['deal_id'])
                ->group_by("D.deal_id")
                ->get();
        return $sql->row_array();
    }

    /**
     * Used for get order promo code details
     * @param int $order_id Order ID
     * @param int $user_id User ID
     * @return array
     */
    function get_order_promo_code_details($order_id, $user_id) {
        $sql = $this->db->select("PC.*,PCE.promo_code_earning_id,PCE.user_id,PCE.order_id,PCE.amount_received,PCE.is_processed", FALSE)
                ->from(PROMO_CODE_EARNING . " AS PCE")
                ->join(PROMO_CODE . " AS PC", "PC.promo_code_id = PCE.promo_code_id", "INNER")
                ->where("PCE.order_id", $order_id)
                ->where("PCE.user_id", $user_id)
                ->group_by("PC.promo_code_id")
                ->get();
        return $sql->row_array();
    }

    /**
     * Used for get order details
     * @param int $order_id Order ID
     * @param int $user_id User ID
     * @return array
     */
    function get_order_deal_details($order_id, $user_id) {
        $sql = $this->db->select("D.*,DE.deal_earning_id,DE.user_id,DE.order_id,DE.is_processed", FALSE)
                ->from(DEALS_EARNING . " AS DE")
                ->join(DEALS . " AS D", "D.deal_id = DE.deal_id", "INNER")
                ->where("DE.order_id", $order_id)
                ->where("DE.user_id", $user_id)
                ->where("D.status", 1)
                ->group_by("D.deal_id")
                ->get();
        return $sql->row_array();
    }

    /**
     * Used for generate order unique id
     * @return string
     */
    function _generate_order_key() {
        $this->load->helper('security');
        $salt = do_hash(time() . mt_rand());
        $new_key = substr($salt, 0, 20);
        return $new_key;
    }

    /**
     * Used for check order unique id exist or not
     * @param string $key
     * @return int
     */
    private function _order_key_exists($key) {
        $this->db->select('order_id');
        $this->db->where('order_unique_id', $key);
        $this->db->limit(1);
        $query = $this->db->get(ORDER);
        $num = $query->num_rows();
        if ($num > 0) {
            return true;
        }
        return false;
    }

    /**
     * generate order and transaction from platform
     * @param float $amount Amount to deposit
     * @param int $user_id User ID
     * @param int $email email
     * @param int $phone phone
     * @param int $productinfo product Description
     * @param string $surl Success URL
     * @param string $furl Failure URL
     * @return int Transaction ID
     */
    function _generate_order_and_tx($amount, $user_id, $email, $phone, $productinfo, $surl, $furl, $promo_code = array(), $deal = array()) {
        if(isset($_POST['payment_gateway_id']) && $_POST['payment_gateway_id']){
            $pg_id = $_POST['payment_gateway_id'];
        }else{
            $pg_id = 1;
        }

        //change cash type in case of in app 
        if($pg_id==9){
            $amount_arr = json_decode($amount,true);
            $coins = $amount_arr['coin_amount'];
            $deposit_data = array(
                "user_id" => $user_id,
                "amount" => $coins,
                "source" => 325,
                "source_id" => 0,
                "cash_type" => 2, // for coin cash 
                "reason" => '',
                "custom_data"=>$amount
            );
        }else if($pg_id==11){
            $amount_arr = json_decode($amount,true);
            $coins = $amount_arr['coins'];
            $deposit_data = array(
                "user_id" => $user_id,
                "amount" => $coins,
                "source" => 437,
                "source_id" => $amount_arr['subscription_id'],
                "cash_type" => 2, // for coin cash 
                "reason" => 'Inapp subscription',
                "custom_data"=>$amount
            );
        }
        else{
        $deposit_data = array(
            "user_id" => $user_id,
            "amount" => $amount,
            "source" => 7,
            "source_id" => 0,
            "cash_type" => 0, // for bonus cash 
            "reason" => ''
        );
        }

        // gst number add/update in user table and add in the order table
        $post_data = $this->input->post();
        if(isset($post_data['gst']) && !empty($post_data['gst'])){
            $earn_info = $this->get_single_row('gst_number', USER,['user_id'=>$user_id,'gst_number'=>$post_data['gst']]);
            if(empty($earn_info)){
                $this->db->update(USER,['gst_number'=>$post_data['gst']],['user_id'=>$user_id]);
                // profile cache remove
                $user_profile_cache_key = "user_profile_".$user_id;
                $this->delete_cache_data($user_profile_cache_key);

                $gst_number = $post_data['gst'];
            }else{
                $gst_number = $earn_info['gst_number'];
            }
            
            $get_num_arr = array('get_number'=>$gst_number);
            if(isset($deposit_data['custom_data']) && !empty($deposit_data['custom_data'])){
                $custom_data_arr = json_decode($deposit_data['custom_data'],true);
                $custom_data_arr = array_merge($custom_data_arr,$get_num_arr);
                $deposit_data['custom_data'] = $custom_data_arr;
            }else{
                $deposit_data['custom_data'] = $get_num_arr;
            }
            $deposit_data['custom_data'] = json_encode($deposit_data['custom_data']);
        }
        
        $order_id = $this->deposit_fund($deposit_data);

        if ($order_id && !empty($promo_code)) {
            $earn_info = $this->get_single_row('promo_code_earning_id,is_processed', PROMO_CODE_EARNING, array('order_id' => $order_id));

            $total_discount = $promo_code['discount'];
            if(!empty($promo_code)){
                if (isset($promo_code['value_type']) && $promo_code['value_type'] == "1") {
                    $total_discount = ($amount * $promo_code['discount']) / 100;
                    if ($total_discount > $promo_code['benefit_cap']) {
                        $total_discount = $promo_code['benefit_cap'];
                    }
                } else {
                    $total_discount = $promo_code['discount'];
                }
            }

            if (!empty($earn_info) && $earn_info["is_processed"] == 0) {
                $this->update_promo_code_earning_details(array("amount_received" => $total_discount), $earn_info["promo_code_earning_id"]);
            } elseif (empty($earn_info)) {
                $promo_earning = array();
                $promo_earning['promo_code_id'] = $promo_code["promo_code_id"];
                $promo_earning['user_id'] = $user_id;
                $promo_earning['order_id'] = $order_id;
                $promo_earning['amount_received'] = $total_discount;
                $promo_earning['added_date'] = format_date();
                $this->db->insert(PROMO_CODE_EARNING, $promo_earning);
            }
        }


        if ($order_id && !empty($deal)) {
            $deal_earn_info = $this->get_single_row('deal_earning_id,is_processed', DEALS_EARNING, array('order_id' => $order_id));
            if (!empty($deal_earn_info) && $deal_earn_info["is_processed"] == 0) {
                $this->update_deal_earning_details(array("bonus" => $deal['bonus'], 'cash' => $deal['cash'],
                    'coins' => $deal['coins']
                        ), $deal_earn_info["deal_earning_id"]);
            } elseif (empty($deal_earn_info)) {
                $deal_earning = array();
                $deal_earning['deal_id'] = $deal["deal_id"];
                $deal_earning['user_id'] = $user_id;
                $deal_earning['order_id'] = $order_id;
                $deal_earning['added_date'] = format_date();
                $this->db->insert(DEALS_EARNING, $deal_earning);
            }
        }

        $transaction = array('order_id' => $order_id,
            'payment_gateway_id' => $pg_id,
            'email' => $email,
            'phone' => $phone,
            'description' => $productinfo,
            'surl' => $surl,
            'furl' => $furl,
            'transaction_status' => 0,
            'withdraw_type' => 0
        );
        $txn_id = $this->create_transaction($transaction);
        $this->db->update(ORDER,['source_id'=>$txn_id],['order_id'=>$order_id]);
        return $txn_id;
    }
    
    /**
     * used to get transaction details
     * @param int $txn_id
     * @return array
     */
    function get_transaction_by_txn_id($txn_id) {
        return $this->db->where('txn_id', $txn_id)->get(TRANSACTION, 1)->row_array();
    }

    /**
     * add_first_deposit_bonus_for_referral to give referral amount on first deposit
     * @param
     * @return json array
     */
    function add_first_deposit_bonus_for_referral($user_detail, $friend_detail, $source_type) {
        /* $user_detail = for user who sent referral,$friend_detail = for user who used referral code */
        $friend_name = "Friend";
        if (!empty($friend_detail['first_name']) && !empty($friend_detail['last_name'])) {
            $friend_name = $friend_detail['first_name'] . ' ' . $friend_detail['last_name'];
        } elseif (!empty($friend_detail['user_name'])) {
            $friend_name = $friend_detail['user_name'];
        }

        $affililate_master_detail = $this->get_single_row('*', AFFILIATE_MASTER, array("affiliate_type" => 14));

        if (empty($affililate_master_detail)) {
            return TRUE;
        }

        $affililate_history = $this->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY, array("friend_id" => $friend_detail["user_id"], "status" => 1, "user_id" => $user_detail["user_id"], "affiliate_type" => 14));
        if (!empty($affililate_history)) {
            return TRUE;
        }

        $bouns_condition = array();
        $data_post = array();
        $data_post["friend_id"] = $friend_detail["user_id"];
        $data_post["friend_mobile"] = (!empty($friend_detail['phone_no'])) ? $friend_detail['phone_no'] : NULL;
        $data_post["user_id"] = $user_detail["user_id"];
        $data_post["status"] = 1;
        $data_post["source_type"] = $source_type;
        $data_post["affiliate_type"] = 14;
        $data_post["is_referral"] = 1;

        //for user who used referral code
        $data_post["friend_bonus_cash"] = $affililate_master_detail["user_bonus"];
        $data_post["friend_real_cash"] = $affililate_master_detail["user_real"];
        $data_post["friend_coin"] = $affililate_master_detail["user_coin"];

        //for user who sent referral(refer code)
        $data_post["user_bonus_cash"] = $affililate_master_detail["bonus_amount"];
        $data_post["user_real_cash"] = $affililate_master_detail["real_amount"];
        $data_post["user_coin"] = $affililate_master_detail["coin_amount"];

        $data_post["bouns_condition"] = json_encode($bouns_condition);


        $this->load->model("affiliate/Affiliate_model");
        $affililate_history_id = $this->Affiliate_model->add_affiliate_activity($data_post);
        if (empty($affililate_history_id)) {
            return TRUE;
        }

        /* ## Generate transactions for user who used referral code ### */
        if ($affililate_master_detail["user_bonus"] > 0) {
            $deposit_data_friend = array(
                "user_id" => $friend_detail["user_id"],
                "amount" => $affililate_master_detail["user_bonus"],
                "source" => 105, //New first deposit with referral - bonus cash
                "source_id" => $affililate_history_id,
                "plateform" => 1,
                "cash_type" => 1, // for bonus cash 
                "link" => FRONT_APP_PATH . 'my-wallet'
            );
            $this->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for real cash type
        if ($affililate_master_detail["user_real"] > 0) {
            $deposit_data_friend = array(
                "user_id" => $friend_detail["user_id"],
                "amount" => $affililate_master_detail["user_real"],
                "source" => 106, //New first deposit with referral - real cash
                "source_id" => $affililate_history_id,
                "plateform" => 1,
                "cash_type" => 0, //for real cash 
                "link" => FRONT_APP_PATH . 'my-wallet'
            );
            $this->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for coins type
        if ($affililate_master_detail["user_coin"] > 0) {
            $deposit_data_friend = array(
                "user_id" => $friend_detail["user_id"],
                "amount" => $affililate_master_detail["user_coin"],
                "source" => 107, //New first deposit with referral - coins(points)
                "source_id" => $affililate_history_id,
                "plateform" => 1,
                "cash_type" => 2, //for coins(point balance) 
                "link" => FRONT_APP_PATH . 'my-wallet'
            );
            $this->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for bonus cash type
        if ($affililate_master_detail["bonus_amount"] > 0) {
            $deposit_data_friend = array(
                "user_id" => $user_detail["user_id"],
                "amount" => $affililate_master_detail["bonus_amount"],
                "source" => 98, //New first deposit with referral - bonus cash
                "source_id" => $affililate_history_id,
                "plateform" => 1,
                "cash_type" => 1, //for bonus cash 
                "link" => FRONT_APP_PATH . 'my-wallet',
                "friend_name" => $friend_name
            );
            $this->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for real cash type
        if ($affililate_master_detail["real_amount"] > 0) {
            $deposit_data_friend = array(
                "user_id" => $user_detail["user_id"],
                "amount" => $affililate_master_detail["real_amount"],
                "source" => 99, //New first deposit with referral - real cash
                "source_id" => $affililate_history_id,
                "plateform" => 1,
                "cash_type" => 0, //for real cash 
                "link" => FRONT_APP_PATH . 'my-wallet',
                "friend_name" => $friend_name
            );
            $this->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for coins type
        if ($affililate_master_detail["coin_amount"] > 0) {
            $deposit_data_friend = array(
                "user_id" => $user_detail["user_id"],
                "amount" => $affililate_master_detail["coin_amount"],
                "source" => 100, //New first deposit referral - coins(points)
                "source_id" => $affililate_history_id,
                "plateform" => 1,
                "cash_type" => 2, //for coins(point balance) 
                "link" => FRONT_APP_PATH . 'my-wallet',
                "friend_name" => $friend_name
            );
            $this->deposit_fund($deposit_data_friend);
        }
        return TRUE;
    }

    /**
     * add_first_deposit_bonus_without_referral to give bonus amount on w/o referral on first deposit
     * @param
     * @return json array
     */
    function add_first_deposit_bonus_without_referral($user_id) {
        if (empty($user_id)) {
            return TRUE;
        }
        //get user details
        $user_detail = $this->get_single_row('user_id,user_name,email,phone_no', USER, array("user_id" => $user_id));
        if (empty($user_detail)) {
            return TRUE;
        }
        //check if affiliate master entry availalbe for email verify bonus w/o referral
        $affililate_master_detail = $this->get_single_row('*', AFFILIATE_MASTER, array("affiliate_type" => 15));
        //if no details available then return true without further processing.
        if (empty($affililate_master_detail)) {
            return TRUE;
        }
        //check if signup bonus already given to this user.
        $user_affililate_history = $this->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY, array("friend_id" => $user_id, "affiliate_type" => 15));
        if (!empty($user_affililate_history)) {
            return TRUE;
        }

        $bouns_condition = array();
        $data_post = array();
        $data_post["friend_id"] = $user_id;
        $data_post["friend_mobile"] = (!empty($user_detail['phone_no'])) ? $user_detail['phone_no'] : NULL;
        $data_post["user_id"] = 0; //FOR WITHOUT REFERRAL CASE
        $data_post["status"] = 1;
        $data_post["source_type"] = 0;
        //$data_post["amount_type"]		= 0;
        $data_post["affiliate_type"] = 15;
        $data_post["is_referral"] = 0;

        //for w/o referral case use only friend bonus/real/coin balance
        $data_post["friend_bonus_cash"] = $affililate_master_detail["user_bonus"];
        $data_post["friend_real_cash"] = $affililate_master_detail["user_real"];
        $data_post["friend_coin"] = $affililate_master_detail["user_coin"];

        $data_post["bouns_condition"] = json_encode($bouns_condition);

        $this->load->model("affiliate/Affiliate_model");
        $affililate_history_id = $this->Affiliate_model->add_affiliate_activity($data_post);
        if (empty($affililate_history_id)) {
            return TRUE;
        }

        //Entry on order table for bonus cash type
        if ($affililate_master_detail["user_bonus"] > 0) {

            $deposit_data_friend = array(
                "user_id" => $user_id,
                "amount" => $affililate_master_detail["user_bonus"],
                "source" => 95, //New first deposit - bonus cash
                "source_id" => $affililate_history_id,
                "plateform" => 1,
                "cash_type" => 1, // for bonus cash 
                "link" => FRONT_APP_PATH . 'my-wallet'
            );
            $this->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for real cash type
        if ($affililate_master_detail["user_real"] > 0) {
            $deposit_data_friend = array(
                "user_id" => $user_id,
                "amount" => $affililate_master_detail["user_real"],
                "source" => 96, //New first deposit - real cash
                "source_id" => $affililate_history_id,
                "plateform" => 1,
                "cash_type" => 0, //for real cash 
                "link" => FRONT_APP_PATH . 'my-wallet'
            );
            $this->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for coins type
        if ($affililate_master_detail["user_coin"] > 0) {
            $deposit_data_friend = array(
                "user_id" => $user_id,
                "amount" => $affililate_master_detail["user_coin"],
                "source" => 97, //New first deposit - coins(points)
                "source_id" => $affililate_history_id,
                "plateform" => 1,
                "cash_type" => 2, //for coins(point balance) 
                "link" => FRONT_APP_PATH . 'my-wallet'
            );
            $this->deposit_fund($deposit_data_friend);
        }
        return TRUE;
    }

    /*
     * function : get_deals
     * def: get all active deals
     * @params : 
     * @return : array deals
     */
    function get_deals() {
        $sql = $this->db->select('*')
                ->from(DEALS)
                ->where('status', 1)
                ->where('is_deleted', 0)
                ->order_by('amount',"ASC");
        //->join(ADS_POSITION . " as ADP", "ADP.ad_position_id = D.position_id", "inner");

        return $sql->get()->result_array();
    }

    public function sync_transaction_messages() {
        $this->db->select("source,en_message,hi_message,guj_message,fr_message,ben_message,pun_message,tam_message,th_message,ru_message,id_message,tl_message,zh_message,kn_message,es_message",FALSE);
        $this->db->from(TRANSACTION_MESSAGES);
        $query = $this->db->get();
        $resultList = $query->result_array();
        if($resultList) {
            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->delete_collection(COLL_TRANSACTION_MESSAGES);
            foreach ($resultList as $result) {
                $this->Notify_nosql_model->insert_nosql(COLL_TRANSACTION_MESSAGES,$result);
            }
        }
    }

    /**
     * generate withdraw order and transaction from platform
     * @param int $user_id User ID
     * @param int $email email
     * @param int $phone phone
     * @param array $post_input post input
     * @return int Transaction ID
     */
    function _generate_withdraw_order_and_tx($user_id, $email, $phone, $post_input) {         
        try {
            $post_input['user_id']       = $user_id;
            $post_input['source']        = 8;
            $post_input['source_id']     = 0;        
            $post_input['status']        = 0;
            $post_input['withdraw_method']= isset($post_input['withdraw_method']) ? $post_input['withdraw_method'] : 17;
            $order_data = $this->withdraw($post_input);
            if($order_data) {
                $order_id = $order_data['order_id'];
                switch($post_input['withdraw_method'])
                {
                    case 1:
                        $product_info = SITE_TITLE . ' withdraw via Payumoney';
                    break;
                    case 2:
                        $product_info = SITE_TITLE . ' withdraw via PayTm';
                    break;
                    case 17:
                        $product_info = SITE_TITLE . ' withdraw via Cashfree';
                    break;
                    case 3:
                        $product_info = SITE_TITLE . ' withdraw via Mpesa';
                    break;
                    case 8:
                        $product_info = SITE_TITLE . ' withdraw via Razorpayx';
                    break;
                    case 34:
                        $product_info = SITE_TITLE . ' withdraw via Juspay';
                    break;
                }
                $transaction = array('order_id' => $order_id,
                    'payment_gateway_id' =>  isset($post_input['withdraw_method']) ? $post_input['withdraw_method'] : 2,
                    'email' => $email,
                    'phone' => $phone,
                    'description' => $product_info,
                    'transaction_status' => ($post_input['isIW']==1) ? 3 :0,
                    'withdraw_type' => 0,
                    'txn_date' => format_date()
                );
                $transaction_id = $this->create_transaction($transaction);
            }
            $order_data['transaction_id'] = $transaction_id;
            $order_data["winning_amount"] = $post_input['amount'];
            $order_data['source'] = 8;
            $order_data['user_id'] = $user_id;
            $order_data['withdraw_method'] = $post_input['withdraw_method'];
            $order_data['date_added'] = format_date();
            return $order_data;
            
        } catch (Exception $e) {                
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Used to update transaction data by order id
     * @param array $data
     * @param int $order_id
     * @return int
     */
    function update_transaction_by_order_id($data, $order_id) {
        $this->db->where('order_id', $order_id)->update(TRANSACTION, $data);
    }

     /**
     * update order status to platform
     * 
     * @param int $order_detail order DETAILS 
     * @param array $update_data update data
     * @return bool Status updated or not
     */
    function update_payout_tx_status($order_detail, $update_data = array()) {
        $order_id = $order_detail['order_id'];
        $this->update_transaction_by_order_id($update_data, $order_id);
        $status_type = $update_data['transaction_status'];
        if(!empty($status_type)) {
            $reason = "";

            if ($status_type == 3) {
                $status_type = 0;
            } else if (($status_type == 1 || $status_type == 5) && $order_detail['source'] == 8) {
                $status_type = 1;
            } else if (($status_type == 2 || $status_type == 4) && $order_detail['source'] == 8) {
                $status_type = 2;
                $reason = $this->lang->line('withdraw_failed');
                $this->update_user_balance($order_detail["user_id"], $order_detail, 'add');
            }
    
            $source_id = 0;
            $this->update_order_status($order_id, $status_type, $source_id, 'by your recent withdraw');
        
            $user_data = $this->get_single_row('user_name, email', USER, array("user_id" => $order_detail["user_id"]));
                
            $msg_content = array(
                "amount"    => $order_detail['winning_amount'],
                "reason"    => $reason, 
                "user_id"   => $order_detail['user_id'],
                "cash_type" => "0",
                "plateform" => "1",
                "source"    => "7",
                "source_id" => "0",
                "date_added"=> $order_detail['date_added']
            );
            $msg_content["payment_option"] = 'PayTm'; 
           
        
            // SOME CONFUSING BECAUSE STATUS IN DB IS DIFFER
            if($status_type == 1 || $status_type == 5) {
                $notify_data["notification_type"] = 25; // 25-ApproveWithdrawRequest
            }
            
            if($status_type == 2 || $status_type == 4) {
                $notify_data["notification_type"] = 26; // 26-RejectWtihdrawRequest
            }
            $today = format_date();
            $notify_data["source_id"] = 0;
            $notify_data["notification_destination"] = 7; //  Web, Push, Email
            $notify_data["user_id"] =  $order_detail['user_id'];
            $notify_data["to"] = $user_data['email'];
            $notify_data["user_name"] = $user_data['user_name'];
            $notify_data["added_date"] = $today;
            $notify_data["modified_date"] = $today;
            $notify_data["content"] = json_encode($msg_content);

            if($status_type == 1 || $status_type == 5) {
                $notify_data["subject"] = $this->lang->line("withdraw_email_approve_subject");                
            } else {
                $notify_data["subject"] = $this->lang->line("withdraw_email_reject_subject");
            }

            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($notify_data);            
        }
    }
    
    /** to determine first deposit for tracking user
     * @param
     * @return json array
     */
    public function first_deposit_for_tracking_user($user_id)
    {
        $sql = $this->db->select('UT.affiliate_reference_id,UT.user_track_id')
                        ->from(ORDER." O")
                        ->join(USER_TRACK." AS UT","UT.user_id = O.user_id","INNER")
                        ->where('O.user_id', $user_id)
                        ->where('O.status', 1)
                        ->where('O.source', 7)
                        ->where('O.type', 0)
                        ->get()->result_array();

        return (count($sql) == 1) ? $sql[0] : array();
    }
        /**
    * function to get content from common_content table
    */

    public function get_wallet_content($content_key = 'wallet'){

        if($this->lang_abbr=='' || $this->lang_abbr ==null)
        {
            $this->lang_abbr = 'en';
        }
        $result = $this->db->select("IFNULL({$this->lang_abbr}_header ,en_header) AS header,IFNULL({$this->lang_abbr}_body ,en_body) AS body")
        ->from(COMMON_CONTENT)
        ->where('status',1)
        ->where('content_key',$content_key);
        $result = $this->db->get()->row_array();
        // echo $this->db->last_query();exit;
        return $result;
    }


    /**
     * get coin expiry of user
     */
    function get_user_coin_going_to_expire($user_id,$day ='+5', $count=FALSE)
    {
        $days  = $this->app_config['allow_coin_expiry']['custom_data']['push_before_days'];
        $today_date = format_date("today", "Y-m-d");
        $next_date = date("Y-m-d", strtotime('+'.$days.' day',strtotime($today_date)));
        $coin_expiry_limit = $this->app_config['allow_coin_expiry']['custom_data']['ce_days_limit'];
        $coins_start_date   = date("Y-m-d", strtotime(-$coin_expiry_limit.' day',strtotime($today_date)));
        $coins_end_date     = date("Y-m-d", strtotime(-$coin_expiry_limit.' day',strtotime($next_date)));

        if($count) {
            $this->db->select('IFNULL(SUM(total_coins-used_coins),0) as camt');
        } else {
            $this->db->select('(total_coins-used_coins) as camt, bonus_date as coin_date, DATE_ADD(bonus_date, INTERVAL '.$coin_expiry_limit.' DAY) as coin_expiry_date');
        }

        $this->db->from(USER_BONUS_CASH);
        $this->db->where('user_id', $user_id);
        $this->db->where('is_coin_exp', 1);
        $this->db->where('bonus_date >= ', $coins_start_date);
        $this->db->where('bonus_date <= ', $coins_end_date);
        $this->db->order_by('bonus_cash_id', 'ASC');
        $query = $this->db->get();        
        if($query->num_rows() > 0) {
            if($count) {
                $result = $query->row_array();
                return $result['camt'];
            } else {
                return $query->result_array();
            }
        }
        return 0;
    }

    /**  Used to get user bonus cash going to expire
     * @param int $user_id
     * @return array
     */
    function get_user_bonus_cash_going_to_expire($user_id, $day='+7', $count=FALSE) {
        $today_date = format_date("today", "Y-m-d");
        $next_date = date("Y-m-d", strtotime($day.' day',strtotime($today_date)));

        $bonus_exiry_limit = isset($this->app_config['bonus_expiry_limit'])?$this->app_config['bonus_expiry_limit']['key_value']:0;
        
        $bonus_start_date = date("Y-m-d", strtotime(-$bonus_exiry_limit.' day',strtotime($today_date)));
        $bonus_end_date = date("Y-m-d", strtotime(-$bonus_exiry_limit.' day',strtotime($next_date)));
        if($count) {
            $this->db->select('IFNULL(SUM(total_bonus-used_bonus),0) as bamt');
        } else {
            $this->db->select('(total_bonus-used_bonus) as bamt, bonus_date, DATE_ADD(bonus_date, INTERVAL '.$bonus_exiry_limit.' DAY) as bonus_expiry_date');
        }      

        $this->db->from(USER_BONUS_CASH);
        $this->db->where('user_id', $user_id);
        $this->db->where('is_expired', 1);
        $this->db->where('bonus_date >= ', $bonus_start_date);
        $this->db->where('bonus_date <= ', $bonus_end_date);
        $this->db->order_by('bonus_cash_id', 'ASC');
        $query = $this->db->get();        
        if($query->num_rows() > 0) {
            if($count) {
                $result = $query->row_array();
                return $result['bamt'];
            } else {
                return $query->result_array();
            }
        }
        return 0;
    }

    function get_promo_codes() {
        $sort_field	= 'added_date';
		$sort_order	= 'DESC';
		$limit		= 5;
		$page		= 0;

        $offset	= $limit * $page;
        $today_date = format_date('today', 'Y-m-d H:i:s');

		$this->db->select("promo_code, cash_type, value_type, discount, benefit_cap",FALSE);
		$this->db->select('IFNULL(min_amount, 0) as min_amount');
        $this->db->select('IFNULL(max_amount, 0) as max_amount');
        $this->db->select('IFNULL(description, "") as description');
        $this->db->from(PROMO_CODE);		
        $this->db->where('mode',0);    
        $this->db->where_in('type', array(0,1,2));
        $this->db->where('status="1"');
        $this->db->where("DATE_FORMAT(start_date,'%Y-%m-%d %H:%i:%s') <= ", $today_date);
        $this->db->where("DATE_FORMAT(expiry_date,'%Y-%m-%d %H:%i:%s') >= ", $today_date);
		$this->db->order_by($sort_field, $sort_order);
		$this->db->limit($limit,$offset);
        $query = $this->db->get();
		$result	= $query->result_array();
        return $result;
    }



    public function get_promo_used_count($promoid){
        $sql = $this->db->select('count(promo_code_earning_id) as total_used')
                        ->from(PROMO_CODE_EARNING)
                        ->where('is_processed', "1")
                        ->where('promo_code_id', $promoid)
                        ->get();
        $rs = $sql->row_array();
        return $rs;
    }

    public function generate_cashfree_user_id()
    {
        $user_info = $this->get_single_row('user_unique_id,user_id',USER,['user_id'=>$this->user_id]);
        $user_cf_id = $user_info['user_unique_id'].$user_info['user_id'];
        $this->update(USER_BANK_DETAIL, ['beneficiary_id'=>$user_cf_id], ['user_id'=>$this->user_id]);
        return $user_cf_id;
    }
    public function get_user_by_id($user_id)
    {
        $result = $this->db->select('is_bank_verified,U.address,U.city,U.zip_code,MS.name AS state')
						->from(USER.' U')
                        ->join(MASTER_STATE.' MS','MS.master_state_id = U.master_state_id','left')
						->where('user_id', $user_id)
						->get()->row_array();
		return $result;
    }

    public function update_ord($data)
    {
        $order_id = $data['order_id'];
        unset($data['order_id']);
        $this->db->update(ORDER,['custom_data'=>$data['custom_data'],'status'=>$data['status']],['order_id'=>$order_id]);
        return true;
    }

    public function get_txn_of_day()
    {
        $current_date = format_date('today','Y-m-d');
        $where = array(
            "source"=>8,
            "status"=>1,
            "user_id"=>$this->user_id,
            "date_added >= "=>$current_date.' 00:00:00'
        );
        $result = $this->db->select("count(order_id) AS txns")
        ->from(ORDER)
        ->where($where)
        ->get()->row_array();
        return $result['txns'];
    }

    public function update_order_transaction($transaction_id,$trans_data,$order_data)
    {
        error_log("\n >>>> transaction id  ".$transaction_id."\n\n\n",3,'/var/www/html/cron/application/logs/cashfree.log');
        error_log("\n >>>> transaction data  ".$trans_data."\n\n\n",3,'/var/www/html/cron/application/logs/cashfree.log');
        error_log("\n >>>> order data  ".$order_data."\n\n\n",3,'/var/www/html/cron/application/logs/cashfree.log');
        
        $this->db->trans_start();
        //check once 
        $is_pending = $this->db->select('status,transaction_status')
        ->from(ORDER.' O')
        ->join(TRANSACTION.' T',"T.transaction_status= O.status","INNER")
        ->where("T.transaction_id",$transaction_id)
        ->where("O.status",0)
        ->where("T.transaction_status",0)
        ->limit(1)
        ->get()->row_array();

        error_log("\n >>>> get txn query  ".$this->db->last_query()."\n\n\n",3,'/var/www/html/cron/application/logs/cashfree.log');
        error_log("\n >>>> txn data   ".$is_pending."\n\n\n",3,'/var/www/html/cron/application/logs/cashfree.log');

        if(!$is_pending)
        {
            return 0;
        }

        $this->db->where('transaction_id', $transaction_id)->update(TRANSACTION, $trans_data);

        error_log("\n >>>> get txn query  ".$this->db->last_query()."\n\n\n",3,'/var/www/html/cron/application/logs/cashfree.log');
        

        $ord_data = array(
            "status" => $order_data["status"],
            "source_id" => $order_data["source_id"],
            "modified_date" => format_date(),
            "reason" => $order_data["reason"]
        );
        $this->db->where('order_id', $order_data["order_id"])
                ->update(ORDER, $ord_data);

        error_log("\n >>>> get txn query  ".$this->db->last_query()."\n\n\n",3,'/var/www/html/cron/application/logs/cashfree.log');
        
        
        $affected_rows =  $this->db->affected_rows();

        if(!$affected_rows || $this->db->trans_status()==FALSE)
        {
            $this->db->trans_rollback();
        }

        $this->db->trans_complete();
        $this->db->trans_commit();

        return $affected_rows;
    }

    public function filter_multiple_hit($txnid)
    {
        $is_repeat = false;
        $this->db->trans_strict(TRUE);
        $this->db->trans_start();
        $order = $this->db->select("status,order_id,payout_processed")->from(ORDER)->where(['source_id'=>$txnid])->get()->row_array();
        if($order['payout_processed']==0){
            $this->db->update(ORDER,['payout_processed'=>1],['source_id'=>$txnid]);
        }
        $this->db->trans_complete();
        $this->db->trans_commit();
        return $order;
    }

    /**
    * Function used for get user tds details
    * @param array $post_data
    * @return array
    */
    public function get_tds_detail($post_data)
    {
        $return_arr = array("total_net_winning"=>0,"tds_paid"=>0);
        $this->db->select("SUM(UT.net_winning) as net_winning", FALSE);
        $this->db->from(USER_TDS_REPORT." AS UT");
        $this->db->where("UT.user_id",$post_data['user_id']);
        $this->db->where("UT.scheduled_date >= ",$post_data['start_date']);
        $this->db->where("UT.scheduled_date <= ",$post_data['end_date']);
        $result = $this->db->get()->row_array();
        if(!empty($result['net_winning'])){
            $return_arr['total_net_winning'] = $result['net_winning'];
        }

        $this->db->select("SUM(O.tds) as tds_paid", FALSE);
        $this->db->from(ORDER." AS O");
        $this->db->where("O.user_id",$post_data['user_id']);
        $this->db->where("O.status !=","2");
        $this->db->where_in("O.source",[8,130,535]);
        $this->db->where("O.date_added >= ",$post_data['start_date']);
        $this->db->where("O.date_added <= ",$post_data['end_date']);
        $tds = $this->db->get()->row_array();
        if(!empty($tds['tds_paid'])){
            $return_arr['tds_paid'] = $tds['tds_paid'];
        }
        return $return_arr;
    }

    /**
    * Function used for get user tds documents list
    * @param array $post_data
    * @return array
    */
    public function get_tds_document($user_id)
    {
        if(!$user_id){
            return array();
        }
        $this->db->select("UT.id,UT.fy,IFNULL(UT.gov_id,'') as gov_id,UT.file_name,UT.date_added", FALSE);
        $this->db->from(USER_TDS_CERTIFICATE." AS UT");
        $this->db->where("UT.user_id",$user_id);
        $this->db->order_by("UT.id","DESC");
        $result = $this->db->get()->result_array();
        $result = ($result) ? $result : array();
        return array('result'=>$result, 'total'=> count($result));
    }

    /**
    * Function used for get tds report list
    * @param array $post_data
    * @return array
    */
    public function get_tds_report($post_data)
    {
        $this->db->select("UT.entity_name,UT.scheduled_date,UT.total_entry,UT.total_winning,UT.net_winning", FALSE);
        $this->db->from(USER_TDS_REPORT." AS UT");
        $this->db->where("UT.user_id",$post_data['user_id']);
        $this->db->where("UT.scheduled_date >= ",$post_data['start_date']);
        $this->db->where("UT.scheduled_date <= ",$post_data['end_date']);
        $this->db->order_by("UT.scheduled_date","ASC");
        $result = $this->db->get()->result_array();
        $result = ($result) ? $result : array();
        return $result;
    }

    /**
    * Function used for get gst report list
    * @param array $post_data
    * @return array
    */
    public function get_gst_report($post_data)
    {
        $this->db->select("GR.invoice_id,GR.user_name,GR.txn_amount as deposit_amount,GR.txn_date as deposit_date,GR.cgst,GR.sgst,GR.igst,GR.gst_number", FALSE);
        $this->db->from(GST_REPORT." AS GR");
        $this->db->where("GR.user_id",$post_data['user_id']);
        $this->db->where("GR.txn_type",2);
        $this->db->where("GR.txn_date >= ",$post_data['start_date']);
        $this->db->where("GR.txn_date <= ",$post_data['end_date']);
        $this->db->where("GR.invoice_type",1);
        $this->db->order_by("GR.txn_date","ASC");
        $result = $this->db->get()->result_array();
        $result = ($result) ? $result : array();
        return $result;
    }

}
