<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Finance_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
	}

    /**
     * [get_all_site_user_detail description]
     * @MethodName get_all_site_user_detail
     * @Summary This function used for get all user list and return filter user list
     * @param      boolean  [User List or Return Only Count]
     * @return     [type]
     */
    public function get_all_withdrawal_request($count_only=FALSE)
    {
        $sort_field = 'ORD.date_added';
        $sort_order = 'DESC';
        $limit      = 10;
        $page       = 0;
        $post_data  = $this->input->post();

        if(isset($post_data['items_perpage'])) {
            $limit = $post_data['items_perpage'];
        }

        if(isset($post_data['current_page'])) {
            $page = $post_data['current_page']-1;
        }

        if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('real_amount','fullname','email','status','withdrawal_type','address','user_name','ORD.date_added','modified_date','winning_balance','winning_amount')))
        {
            $sort_field = $this->input->post('sort_field');
        }

        if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
        {
            $sort_order = $post_data['sort_order'];
        }

        $offset = $limit * $page;
        $status = isset($post_data['status']) ? $post_data['status'] : "";
        $type   = isset($post_data['type']) ? $post_data['type'] : "";
        $user_keyword   = !empty($post_data['keyword']) ? $post_data['keyword'] : "";
        $user_id    = isset($post_data['user_id']) ? $post_data['user_id'] : "";
        $pg_fee = "JSON_EXTRACT(ORD.custom_data, '$.pg_fee')";

        $bank_name = "JSON_EXTRACT(ORD.custom_data, '$.bank_name')";
        $ac_number = "JSON_EXTRACT(ORD.custom_data, '$.ac_number')";
        $ifsc_code = "JSON_EXTRACT(ORD.custom_data, '$.ifsc_code')";
        $micr_code = "JSON_EXTRACT(ORD.custom_data, '$.micr_code')";
        $texable_amounts = "JSON_EXTRACT(ORD.custom_data, '$.net_winning')";



        if(isset($post_data['csv']) && $post_data['csv'] == true)
        {
            $tz_diff = get_tz_diff($this->app_config['timezone']);
            $this->db->select('USR.user_unique_id, TXN.transaction_id,TXN.pg_order_id, TXN.txn_id as InstantTxnId, TXN.transaction_message,
                ORD.order_unique_id, ORD.order_id, ORD.winning_amount as WithdrawAmount,ORD.custom_data,ORD.tds');
            // $this->db->select("IF(JSON_UNQUOTE(JSON_EXTRACT(ORD.custom_data, '$.is_auto_withdrawal'))=0, CONCAT('Account No :',UBD.ac_number,' Bank : ',UBD.bank_name,' IFSC Code : ',UBD.ifsc_code,' UPI Id : ',UBD.upi_id), ORD.custom_data) as custom_details",FALSE);
            $this->db->select("(CASE ORD.source WHEN 8 THEN IFNULL({$pg_fee},0) ELSE 0 END) as pg_fee",FALSE);
            $this->db->select('(CASE TXN.transaction_status WHEN 1 THEN "Success" WHEN 2 THEN "Failed" WHEN 3 THEN "Instant Pending" WHEN 4 THEN "Instant Reject" WHEN 5 THEN "Instant Approved" ELSE (CASE ORD.status WHEN 1 THEN "Success" WHEN 2 THEN "Failed" ELSE "Pending" END) END) as status',FALSE);
            $this->db->select("CONVERT_TZ(ORD.date_added, '+00:00', '".$tz_diff."') AS order_date_added,CONVERT_TZ(ORD.modified_date, '+00:00', '".$tz_diff."') AS order_modified_date,
                USR.user_unique_id, USR.user_name, CONCAT(USR.first_name,' ',USR.last_name) AS full_name, USR.address as user_address, USR.email, USR.phone_no, USR.total_deposit, USR.winning_balance, USR.pan_no",FALSE);
            $this->db->select('IF(USR.pan_verified=1,"Verified","Not Verified") as pan_verified,UBD.upi_id AS "UPI/Crypto Wallet Address" ', FALSE);
          $this->db->select("IFNULL($bank_name,'-') as bank_name,IFNULL($ac_number,'-') as ac_number,IFNULL($ifsc_code,'-') as ifsc_code,IFNULL($texable_amounts,'0') as texable_amount,IFNULL($micr_code,'-') as micr_code",FALSE);
        }
        else
        {
            $this->db->select("IFNULL($bank_name,'-') as bank_name,IFNULL($ac_number,'-') as ac_number,IFNULL($ifsc_code,'-') as ifsc_code,IFNULL($micr_code,'-') as micr_code",FALSE);


            $this->db->select('USR.net_winning as texable_winning,USR.user_unique_id, USR.user_id, TXN.transaction_id,TXN.pg_order_id, TXN.txn_id, TXN.address, TXN.withdraw_type, TXN.payment_gateway_id, TXN.transaction_message, TXN.transaction_status as status, ORD.status as order_status,
                ORD.order_unique_id, ORD.order_id, ORD.type, ORD.source, ORD.source_id, ORD.real_amount, ORD.bonus_amount, ORD.winning_amount,
                ORD.custom_data, ORD.plateform,
                ORD.date_added as order_date_added,
                ORD.modified_date as order_modified_date,
                USR.phone_no,USR.user_name,CONCAT(USR.first_name," ",USR.last_name) AS full_name,USR.address as user_address,USR.user_unique_id,USR.pan_verified,USR.email,USR.total_deposit, USR.pan_no, USR.winning_balance,UBD.upi_id,ORD.tds',FALSE); 
                // UBD.bank_name,UBD.ac_number,UBD.ifsc_code,UBD.micr_code
            }

        $txn_join = "LEFT";
        if($this->app_config['auto_withdrawal']['key_value'] == 1)
        {
            if (isset($post_data['withdraw_method']) && $post_data['withdraw_method'] == 1)
            {
                $txn_join = "INNER";
            }
        }

        $this->db->from(ORDER." AS ORD")
                ->join(TRANSACTION. " TXN","TXN.order_id = ORD.order_id",$txn_join)
                ->join(USER_BANK_DETAIL." AS UBD","UBD.user_id = ORD.user_id and ORD.withdraw_method in(1,2,3,12,15,17,8)","LEFT")
                // ->join(TRANSACTION." AS TXN","TXN.transaction_id = ORD.source_id and ORD.source = 8","LEFT")
                ->join(USER." AS USR","USR.user_id = ORD.user_id")         
                ->where("ORD.type", 1)
                ->where("ORD.source", 8)
                ->order_by($sort_field, $sort_order);

        if($user_id != "") {
            $this->db->where("ORD.user_id",$user_id);
        }

        if($this->app_config['allow_crypto']['key_value']==1)
        {
            $this->db->where("UBD.type",2);
        }else{
            // $this->db->where("UBD.type",1);
        }

        if($status != "")
        {
            if ($this->app_config['auto_withdrawal']['key_value'] == 1)
            {
                if ($post_data['withdraw_method'] == 1)
                {
                    $this->db->where("TXN.transaction_status","$status");
                }
                else
                {
                    $this->db->where("ORD.status","$status");
                    $this->db->where("TXN.transaction_id IS NULL", NULL);
                }
            }
            else
            {
                $this->db->where("ORD.status","$status");
                $this->db->where("TXN.transaction_id IS NULL", NULL);
            }
        }
        else
        {
            if ($post_data['withdraw_method'] == 0 && $post_data['withdraw_method'] != "")
            {
                $this->db->where("TXN.transaction_id IS NULL", NULL);
            }
        }
        if($type != "") {
            $this->db->where("ORD.withdraw_method","$type");
        }

        if($user_keyword != '') {
            $this->db->like('CONCAT(IFNULL(USR.email,""),IFNULL(USR.first_name,""),IFNULL(USR.last_name,""),IFNULL(USR.user_name,""),CONCAT_WS(" ",USR.first_name,USR.last_name))', $user_keyword);
        }


        $fromdate   = isset($post_data['from_date']) ? $post_data['from_date'] : "";
        $todate     = isset($post_data['to_date']) ? $post_data['to_date'] : "";
        $filter_date_type   = isset($post_data['filter_date_type']) ? $post_data['filter_date_type'] : 0;
        //if custom date range
        if(isset($post_data['filter_date_type']) && $post_data['filter_date_type'] == "1" && $fromdate != '' && $todate != ''){
            $this->db->where("DATE_FORMAT(ORD.modified_date,'%Y-%m-%d %H:%i:%s') >= '".$fromdate."' and DATE_FORMAT(ORD.modified_date,'%Y-%m-%d %H:%i:%s') <= '".$todate."'");
        }else if($fromdate != '' && $todate != ''){
            $this->db->where("DATE_FORMAT(ORD.date_added,'%Y-%m-%d %H:%i:%s') >= '".$fromdate."' and DATE_FORMAT(ORD.date_added,'%Y-%m-%d %H:%i:%s') <= '".$todate."'");
        }
        $tempdb = clone $this->db;
        $total = 0;

        if(isset($post_data['csv']) && $post_data['csv'] == false)
        {
            $query = $this->db->get();
            $total = $query->num_rows();
            $tempdb->limit($limit,$offset);
            $sql = $tempdb->get();
            $result = $sql->result_array();
        }
        else{
            $result = $this->db->get()->result_array();
        }

        $result = ($result) ? $result : array();
        return array('result'=>$result,'total'=>$total);
    }

    /**
     * [update_roster description]
     * @MethodName update_roster
     * @Summary This function used update multiple player salary and status
     * @param      [int]  [league_id]
     * @param      [array]  [data_arr]
     * @return     [boolean]
     */
    public function update_withdrawal_request($data_arr)
    {
        $this->db->update_batch(ORDER, $data_arr, 'order_id');

        return $this->db->affected_rows();
    }

    /**
     * [change_withdrawal_status description]
     * @MethodName change_withdrawal_status
     * @Summary This function used to withdraw status
     * @param      [varchar]  [withdraw_transaction_id]
     * @param      [int]  [status]
     * @return     [boolean]
     */
    public function change_withdrawal_status($date_array)
    {
        $this->db->where("order_id", $date_array['order_id']);
        $order_status = $date_array['status'];
        if($date_array['status'] == 3) {
            $order_status = 0;
        } else if($date_array['status'] == 4) {
            $order_status = 2;
        } else if($date_array['status'] == 5) {
            $order_status = 1;
        }
        $this->db->update(ORDER, array('status'=>$order_status,'modified_date'=>format_date()));

        $this->db->where("order_id", $date_array['order_id']);
        if($date_array['status'] == 3) {
            $this->db->where("transaction_status", 0);
        }
        $this->db->update(TRANSACTION, array('transaction_status'=>$date_array['status']));

        return $this->db->affected_rows() || true;
    }

    public function update_withdraw_reject_balance($order_id){
        $order_info = $this->get_single_row('user_id,winning_amount,tds,custom_data,status',ORDER, array("order_id"=> $order_id,"source"=>"8","status"=>"2"));
        if(!empty($order_info) && $order_info['status'] == "2"){
            $custom_data = json_decode($order_info['custom_data'],TRUE);
            $this->db->set('winning_balance', 'winning_balance + '.$order_info['winning_amount'], FALSE);
            if($order_info['tds'] > 0 && !empty($custom_data) && isset($custom_data['net_winning']) && $custom_data['net_winning'] > 0){
                $this->db->set('net_winning', 'net_winning + '.$custom_data['net_winning'], FALSE);

            }
            $this->db->where("user_id", $order_info['user_id']);
            $this->db->update(USER);
        }
        return true;
    }

    public function get_single_withdraw_request($order_id,$status='all'){

        $query = $this->db->select('IFNULL(TXN.email,"--") AS email,
        TXN.address,
            TXN.payment_gateway_id,ORD.*,
            DATE_FORMAT(ORD.date_added,"%d-%b-%Y") as date_added,
            DATE_FORMAT(ORD.modified_date,"%d-%b-%Y") as modified_date,
            TXN.transaction_id,USR.user_name,CONCAT(USR.first_name," ",USR.last_name) AS full_name,
            USR.email as user_email,USR.address as user_address,USR.balance,USR.language',FALSE)
            //CONCAT(USRF.first_name," ",USRF.last_name) as friend_name,REF.refferal_id
                ->from(ORDER." AS ORD")
                ->join(TRANSACTION." AS TXN","TXN.transaction_id = ORD.source_id and ORD.source = 8","LEFT")
                //->join(REFFERAL." AS REF","REF.refferal_id = ORD.source_id and ORD.source = 4","LEFT")
                //->join(USER." AS USRF","USRF.user_id = REF.friend_id","LEFT")
                ->join(USER." AS USR","USR.user_id = ORD.user_id","LEFT")
                ->where("ORD.order_id",$order_id);

        /*$sql = $this->db->select("PWT.amount,PWT.user_id,U.balance,CONCAT_WS(' ',U.first_name,U.last_name) AS full_name,
            U.email,U.language",false)
            ->from(ORDER." AS PWT")
            ->join(USER." AS U", "U.user_id = PWT.user_id", "inner")
            ->where('PWT.order_id', $order_id);*/

        if($status != 'all'){
            $sql = $this->db->where('ORD.status', $status);
        }


        $sql = $this->db->get();
        $result = $sql->row_array();
        return ($result)?$result:array();

    }

    public function update_user_balance($data,$where)
    { //echo $data.'---'.$where; die;
        $this->db->where($where);
        $this->db->update(USER,$data);
        return $this->db->affected_rows();
    }

    /**
     * @Summary: This function is used for add notification in databse
     * @access: public
     */
    function add_notification($notification_type_id, $sender_user_id = '0', $receiver_user_id, $notification = '', $game_id=0,$game_unique_id = '')
    {
        $data = array(
            'notification_type_id' => $notification_type_id,
            'sender_user_id'       => $sender_user_id,
            'receiver_user_id'     => $receiver_user_id,
            'notification'         => $notification,
            'contest_id'	       => $game_id,
            'contest_unique_id'    => $game_unique_id,
            'is_read'              => '0',
            'created_date'         => format_date(),
        );
        $this->db->insert($this->db->dbprefix(NOTIFICATION), $data);

    }

//---!!!!---  For Payment Transaction

    /**
     * [get_all_transaction description]
     * @MethodName get_all_transaction
     * @Summary This function used for get all transaction history
     * @param      boolean  [transaction history or Return Only Count]
     * @return     [type]
     */
    public function get_all_transaction()
    {
        $sort_field = 'date_added';
        $sort_order = 'DESC';
        $limit      = 10;
        $page       = 0;
        $total = 0;
        $post_data  = $this->input->post();

        if(!empty($post_data['items_perpage']))
        {
            $limit = $post_data['items_perpage'];
        }

        if(!empty($post_data['current_page']))
        {
            $page = $post_data['current_page']-1;
            if($post_data['current_page']==1) {
             $total = $this->get_all_transaction_counts($post_data);
            }
        }

        if(!empty($post_data['sort_field']) && in_array($post_data['sort_field'],array('user_name','bonus_amount','real_amount', 'winning_amount','type','date_added','status','payment_gateway_id')))
        {
            $sort_field = $post_data['sort_field'];
        }

        if(!empty($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
        {
            $sort_order = $post_data['sort_order'];
        }

        $offset	= $limit * $page;

        $query = $this->db->select("ORD.order_id,ORD.source as source_ref, ORD.reference_id, ORD.type,ORD.source,ORD.source_id,
        IFNULL(ORD.cb_amount+ORD.real_amount, 0) as real_amount,ORD.bonus_amount,ORD.winning_amount,ORD.winning_amount,ORD.status,ORD.plateform,
        ORD.date_added as order_date_added,
        TXN.transaction_id,TXN.payment_gateway_id,TXN.txn_id,USR.user_name,CONCAT(USR.first_name,' ',USR.last_name) as name,
        USR.user_unique_id,USR.email,IFNULL(PC.promo_code,'-') as promo_code,
            PC.type as promo_code_type,ORD.points,ORD.custom_data,
            (CASE 
            WHEN TXN.payment_gateway_id =1 THEN 'payumoney' 
            WHEN TXN.payment_gateway_id = 2 THEN 'Paytm' 
            WHEN TXN.payment_gateway_id = 3 THEN 'Mpesa' 
            WHEN TXN.payment_gateway_id = 5 THEN 'Ipay' 
            WHEN TXN.payment_gateway_id = 6 THEN 'Paypal'
            WHEN TXN.payment_gateway_id = 7 THEN 'Paystack' 
            WHEN TXN.payment_gateway_id = 8 THEN 'Razorpay' 
            WHEN TXN.payment_gateway_id = 10 THEN 'Stripe' 
            WHEN TXN.payment_gateway_id = 13 THEN 'vPay' 
            WHEN TXN.payment_gateway_id = 14 THEN 'Ifantasy'
            WHEN TXN.payment_gateway_id = 15 THEN 'Crypto' 
            WHEN TXN.payment_gateway_id = 16 THEN 'Cashierpay' 
            WHEN TXN.payment_gateway_id = 17 THEN 'Cashfree'
            WHEN TXN.payment_gateway_id = 18 THEN 'Paylogic'
            WHEN TXN.payment_gateway_id = 19 THEN 'Btcpay'
            WHEN TXN.payment_gateway_id = 27 THEN 'Directpay'
            WHEN TXN.payment_gateway_id = 28 THEN 'Manual'
            WHEN TXN.payment_gateway_id = 33 THEN 'Phonepe'
            WHEN TXN.payment_gateway_id = 34 THEN 'Juspay'
            ELSE 'other' END) AS gate_way_name",FALSE);

            if (isset($post_data['csv']) && $post_data['csv'] == true) 	
                {                
                    $tz_diff = get_tz_diff($this->app_config['timezone']);                    
                    $this->db->select("CONVERT_TZ(ORD.date_added, '+00:00', '".$tz_diff."') AS order_date_added");
                }else{
                    $this->db->select("ORD.date_added");
                }

                $this->db->from(ORDER." AS ORD")
                ->join(TRANSACTION." AS TXN","TXN.order_id = ORD.order_id","LEFT")
                ->join(USER." AS USR","USR.user_id = ORD.user_id")
                ->join(PROMO_CODE_EARNING." PCE","PCE.order_id = ORD.order_id AND PCE.order_id != 0","LEFT" )
                ->join(PROMO_CODE." PC","PC.promo_code_id = PCE.promo_code_id","LEFT");
              //  ->where_in('ORD.source',array(6,30,31,32));
                
        if(isset($post_data['type']) && $post_data['type'] != "")
        {
            $this->db->where("ORD.type",$post_data['type']);
        }
        if(isset($post_data['status']) && $post_data['status'] != "")
        {
            $this->db->where("ORD.status",$post_data['status']);
        }

        if(isset($post_data['source']) && $post_data['source'] != "")
        {
            if($post_data['source']=='other')
            {
                $exclude_sources = [0,1,2,3,4,5,6,7,8,381,240,241,242,450,451,452,40,41,50,147,53,144,56,58,99]; //THESE SOURCE ARE TAKEN INDIVIDUALLY IN DISCRIPTION FILTER.
                $this->db->where_not_in("ORD.source",$exclude_sources);
            }else{
            $this->db->where("ORD.source",$post_data['source']);
            }
        }      

        if(isset($post_data['from_date']) && $post_data['from_date']!="" && isset($post_data['to_date']) && $post_data['to_date']!="")
        {
            $this->db->where("DATE_FORMAT(ORD.date_added,'%Y-%m-%d %H:%i:%s') >= '".format_date($post_data['from_date'],'Y-m-d H:i:s')."' and DATE_FORMAT(ORD.date_added,'%Y-%m-%d %H:%i:%s') <= '".format_date($post_data['to_date'],'Y-m-d H:i:s')."'");
        }

        if(isset($post_data['frombalance']) && $post_data['frombalance']!="" && isset($post_data['tobalance']) && $post_data['tobalance']!="")
        {
            $this->db->where("real_amount >= '".$post_data['frombalance']."' and real_amount <= '".$post_data['tobalance']."'");
        }

        if(isset($post_data['keyword']) && $post_data['keyword'] != "")
        {
            $this->db->like('CONCAT(IFNULL(USR.user_name,""),IFNULL(USR.user_unique_id,""),IFNULL(USR.email,""),IFNULL(TXN.txn_id,""))', $post_data['keyword']);
        }

        /* $tempdb = clone $this->db;
        $query = $this->db->get();
        $total = $query->num_rows(); */


        if(isset($post_data['csv']) && $post_data['csv'] == true)
        {
            $query = $this->db->get();
            $result = $query->result_array();
        }
        else
        {
            $sql = $this->db->order_by($sort_field, $sort_order)
                ->limit($limit,$offset)
                ->get();
            $result = $sql->result_array();

        //    echo $this->db->last_query();die('');


        }
        //  echo $this->db->last_query();die('dfdf');
        $result = ($result) ? $result : array();
        return array('result'=>$result,'total'=>$total);
    }

    function get_all_transaction_counts($post_data){

       $this->db->select("count(ORD.order_id) as total",FALSE)
        ->from(ORDER.' AS ORD');

        //$this->db->where_in('ORD.source',array(7));

        if(isset($post_data['type']) && $post_data['type'] != "")
        {
            $this->db->where("type",$post_data['type']);
        }

        if(isset($post_data['source']) && $post_data['source'] != "")
        {
            $this->db->where("source",$post_data['source']);
        }

        if(isset($post_data['from_date']) && $post_data['from_date']!="" && isset($post_data['to_date']) && $post_data['to_date']!="")
        {
            $this->db->where("DATE_FORMAT(ORD.date_added,'%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(ORD.date_added,'%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."'");
        }

        if(isset($post_data['frombalance']) && $post_data['frombalance']!="" && isset($post_data['tobalance']) && $post_data['tobalance']!="")
        {
            $this->db->where("real_amount >= '".$post_data['frombalance']."' and real_amount <= '".$post_data['tobalance']."'");
        }

        if(isset($post_data['keyword']) && $post_data['keyword'] != "")
        {
            $this->db->join(USER." AS USR","USR.user_id = ORD.user_id");
            $this->db->like('CONCAT(IFNULL(USR.user_name,""),IFNULL(USR.user_unique_id,""),IFNULL(USR.email,""))', $post_data['keyword']);
        }

        if(isset($post_data['status']) && $post_data['status'] != "")
        {
            $this->db->where("ORD.status",$post_data['status']);
        }

        $query = $this->db->get();
        $result = $query->result_array();

        //echo $this->db->last_query(); die;
        return ($result[0]['total'])?$result[0]['total']:0;
    }

    public function get_all_descriptions()
    {


        $sort_field	= 'added_date';
        $sort_order	= 'DESC';
        $limit		= 10;
        $page		= 0;
        $post_data = $this->input->post();

        if($post_data['items_perpage'])
        {
            $limit = $post_data['items_perpage'];
        }

        if($post_data['current_page'])
        {
            $page = $post_data['current_page']-1;
        }

        if($post_data['sort_field'] && in_array($post_data['sort_field'],array('added_date','description_key','english_description','portuguese_description')))
        {
            $sort_field = $post_data['sort_field'];
        }

        if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
        {
            $sort_order = $post_data['sort_order'];
        }

        $offset	= $limit * $page;

        $sql = $this->db->select("MD.master_description_id, MD.description_key,MD.english_description,MD.portuguese_description,DATE_FORMAT(MD.added_date,'%d-%b-%Y') AS added_date",FALSE)
            ->from(MASTERDESCRIPTION.' AS MD')
            ->order_by($sort_field, $sort_order);



        $tempdb = clone $this->db;
        $query = $this->db->get();
        $total = $query->num_rows();

        $sql = $tempdb->limit($limit,$offset)
            ->get();
        $result	= $sql->result_array();


        $records	= array();
        $result=($result)?$result:array();
        return array('result'=>$result,'total'=>$total);
    }

    public function get_order_detail($order_id)
    {
        return $this->db->where('order_id',$order_id)
            ->get(ORDER,1)
            ->row_array();
    }

    public function export_transaction()
    {
        $sort_field = 'date_added';
        $sort_order = 'DESC';
        $limit      = 10;
        $page       = 0;
        $total = 0;
        $post_data  = $this->input->post();

        // echo "<pre>";
        // print_r($post_data);die;


        $tz_diff = get_tz_diff($this->app_config['timezone']);
        // print_r($tz_diff);die;
        $query = $this->db->select("USR.user_unique_id, USR.email, USR.user_name,USR.phone_no,ORD.source,ORD.source_id, ORD.type,TXN.bank_txn_id as TransactionId,IFNULL(ORD.cb_amount+ORD.real_amount, 0) as real_amount,ORD.bonus_amount,ORD.status, ORD.winning_amount,ORD.points as Coins,CONVERT_TZ(ORD.date_added, '+00:00', '".$tz_diff."')
AS OrderDate,ORD.custom_data,IFNULL(PC.promo_code,'-') as promo_code",FALSE)
                ->from(ORDER." AS ORD")
                ->join(USER." AS USR","USR.user_id = ORD.user_id")
                ->join(TRANSACTION." AS TXN","TXN.transaction_id = ORD.source_id and ORD.source = 7","LEFT")
                ->join(PROMO_CODE_EARNING." PCE","PCE.order_id = ORD.order_id AND PCE.order_id != 0","LEFT" )
                ->join(PROMO_CODE." PC","PC.promo_code_id = PCE.promo_code_id","LEFT");


        if(isset($post_data['type']) && $post_data['type'] != "")
        {
            $this->db->where("ORD.type",$post_data['type']);
        }

        if(isset($post_data['status']) && $post_data['status'] != "")
        {
            $this->db->where("ORD.status",$post_data['status']);
        }

        if(isset($post_data['source']) && $post_data['source'] != "")
        {
            if($post_data['source']=='other')
            {
                $exclude_sources = [0,1,2,3,4,5,6,7,8,381,240,241,242,450,451,452,40,41,50,147,53,144,56,58,99]; //THESE SOURCE ARE TAKEN INDIVIDUALLY IN DISCRIPTION FILTER.
                $this->db->where_not_in("ORD.source",$exclude_sources);
            }else{
            $this->db->where("ORD.source",$post_data['source']);
            }
        }

        if(isset($post_data['keyword']) && $post_data['keyword'] != "")
        {
            $this->db->like('CONCAT(IFNULL(USR.user_name,""),IFNULL(USR.user_unique_id,""),IFNULL(USR.email,""))', $post_data['keyword']);
        }

        // echo "<pre>";
        // print_r($post_data);die;

        if(isset($post_data['from_date']) && $post_data['from_date']!="" && isset($post_data['to_date']) && $post_data['to_date']!="")
        {$this->db->where("DATE_FORMAT(ORD.date_added,'%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(ORD.date_added,'%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."'");
            
        }

        $query = $this->db->get();
        $result = $query->result_array();
        // echo $this->db->last_query();die;
        return array('result'=>$result);
    }

    /**
     * [get_all_site_user_detail description]
     * @MethodName get_all_site_user_detail
     * @Summary This function used for get all user list and return filter user list
     * @param      boolean  [User List or Return Only Count]
     * @return     [type]
     */
    public function get_withdrawal_summary($count_only=FALSE)
    {
        $sort_field = 'ORD.date_added';
        $sort_order = 'DESC';
        $limit      = 10;
        $page       = 0;
        $post_data  = $this->input->post();

        if(isset($post_data['items_perpage']))
        {
            $limit = $post_data['items_perpage'];
        }

        if(isset($post_data['current_page']))
        {
            $page = $post_data['current_page']-1;
        }

        if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('real_amount','fullname','email','status','withdrawal_type','address','user_name','ORD.date_added','modified_date','winning_balance','winning_amount')))
        {
            $sort_field = $this->input->post('sort_field');
        }

        if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
        {
            $sort_order = $post_data['sort_order'];
        }

        $offset = $limit * $page;
        $status = isset($post_data['status']) ? $post_data['status'] : "";
        $type   = isset($post_data['type']) ? $post_data['type'] : "";
        $user_keyword   = !empty($post_data['keyword']) ? $post_data['keyword'] : "";
        $user_id    = isset($post_data['user_id']) ? $post_data['user_id'] : "";
        $isIW = "JSON_UNQUOTE(JSON_EXTRACT(ORD.custom_data, '$.isIW'))";
        $pg_fees = "JSON_EXTRACT(ORD.custom_data, '$.pg_fee')";

         $txn_join = "LEFT";
        if($this->app_config['auto_withdrawal']['key_value'] == 1)
        {
            if (isset($post_data['withdraw_method']) && $post_data['withdraw_method'] == 1)
            {
                $txn_join = "INNER";
            }
        }
        
        $query = $this->db->select("
        SUM(ORD.winning_amount) AS total_withdrawal_request_amount,
        SUM(CASE WHEN ORD.status = 1 THEN ORD.winning_amount ELSE 0 END) AS total_withdrawal_approved_amount,
        SUM(CASE WHEN ORD.status = 2 THEN ORD.winning_amount ELSE 0 END) AS total_withdrawal_rejected_amount,
        SUM(CASE WHEN ORD.status = 0 THEN ORD.winning_amount ELSE 0 END) AS total_withdrawal_pending_amount,
        TRUNCATE(SUM(CASE WHEN ORD.status = 1 and {$isIW} =1 THEN IF(LOCATE('%', {$pg_fees}) > '0',(ORD.winning_amount * CAST(JSON_UNQUOTE(JSON_EXTRACT(custom_data, '$.pg_fee')) AS DECIMAL(10,2))) / 100, {$pg_fees}) ELSE 0 END),2) AS total_instant_withdrawal_approved_amount,
        ",FALSE)
        //CONCAT(USRF.first_name," ",USRF.last_name) as friend_name
         //REF.refferal_id,
         ->from(ORDER." AS ORD")
         ->join(TRANSACTION. " TXN","TXN.order_id = ORD.order_id",$txn_join)
         ->join(USER." AS USR","USR.user_id = ORD.user_id")
         ->where("ORD.type", 1)
         ->where("ORD.source", 8)
         ->order_by($sort_field, $sort_order);

        if($user_id != "") {
            $this->db->where("ORD.user_id",$user_id);
        }

        // if($status != "") {
        //     if($status == 1 || $status == 5) {
        //         //$this->db->where_in("TXN.transaction_status",array(1,5));
        //         $this->db->where("ORD.status",1);
        //     } else if($status == 0 || $status == 3) {
        //         //$this->db->where_in("TXN.transaction_status",array(0,3));
        //         $this->db->where("ORD.status",0);
        //     } else if($status == 2 || $status == 4) {
        //         //$this->db->where_in("TXN.transaction_status",array(2,4));
        //         $this->db->where("ORD.status",2);
        //     }
        // }   


        if($this->app_config['allow_crypto']['key_value']==1)
        {
            $this->db->where("UBD.type",2);
        }else{
            // $this->db->where("UBD.type",1);
        }

        if($status != "")
        {
            if ($this->app_config['auto_withdrawal']['key_value'] == 1)
            {
                if ($post_data['withdraw_method'] == 1)
                {
                    $this->db->where("TXN.transaction_status","$status");
                }
                else
                {
                    $this->db->where("ORD.status","$status");
                    $this->db->where("TXN.transaction_id IS NULL", NULL);
                }
            }
            else
            {
                $this->db->where("ORD.status","$status");
                $this->db->where("TXN.transaction_id IS NULL", NULL);
            }
        }
        else
        {
            if ($post_data['withdraw_method'] == 0 && $post_data['withdraw_method'] != "")
            {
                $this->db->where("TXN.transaction_id IS NULL", NULL);
            }
        }


        if($type != "") {
            $this->db->where("ORD.withdraw_method","$type");
        }
        
        if($user_keyword != '') {
            $this->db->like('CONCAT(IFNULL(USR.email,""),IFNULL(USR.first_name,""),IFNULL(USR.last_name,""),IFNULL(USR.user_name,""),CONCAT_WS(" ",USR.first_name,USR.last_name))', $user_keyword);
        }

        
        $fromdate   = isset($post_data['from_date']) ? $post_data['from_date'] : "";
        $todate     = isset($post_data['to_date']) ? $post_data['to_date'] : "";
        //if custom date range
        if($fromdate != '' && $todate != '') {
            $this->db->where("DATE_FORMAT(ORD.date_added,'%Y-%m-%d %H:%i:%s') >= '".$fromdate."' and DATE_FORMAT(ORD.date_added,'%Y-%m-%d %H:%i:%s') <= '".$todate."'");
        }
        $tempdb = clone $this->db;
        
         $sql = $tempdb->get();
         //echo $tempdb->last_query(); die;
         //exit();//   
        $result	= $sql->result_array();

        $result = ($result) ? $result[0] : array();
        return $result;
    }

    public function get_user_by_id($user_id)
    {
        $result = $this->db->select('U.winning_balance,U.address,U.city,U.zip_code,MS.name AS state')
						->from(USER.' U')
                        ->join(MASTER_STATE.' MS','MS.master_state_id = U.master_state_id','left')
						->where('user_id', $user_id)
						->get()->row_array();
		return $result;
    }

     /**
     * Used to update transaction data
     * @param array $data
     * @param int $transaction_id
     * @return int
     */
    function update_transaction($data, $transaction_id) {

        $this->db->where('transaction_id', $transaction_id)->update(TRANSACTION, $data);
        return $this->db->affected_rows();
    }

    /**
     * Used for get user txn detail
     * @param int $order_id
     * @return array
     */
    public function get_user_txn_detail($order_id){
        $tz_diff = get_tz_diff($this->app_config['timezone']);                    
        $this->db->select("O.order_id,O.user_id,O.order_unique_id,O.real_amount as amount,O.status,T.transaction_id,T.payment_gateway_id,T.txn_amount,T.txn_id,T.payment_mode,T.bank_txn_id,T.gate_way_name,IFNULL(CONCAT(U.first_name,' ',U.last_name),U.user_name) as full_name,U.user_name,U.email,U.phone_no,U.balance,IFNULL(U.pan_no,'') as pan_no,U.pan_verified,U.status as user_status", false);
        $this->db->select("CONVERT_TZ(O.date_added, '+00:00', '".$tz_diff."') AS date_added");
        $this->db->select("CONVERT_TZ(O.modified_date, '+00:00', '".$tz_diff."') AS modified_date");
        $this->db->select("CONVERT_TZ(T.txn_date, '+00:00', '".$tz_diff."') AS txn_date");
        $this->db->select("CONVERT_TZ(U.added_date, '+00:00', '".$tz_diff."') AS register_date");
        $this->db->from(ORDER.' AS O')
                ->join(TRANSACTION.' AS T', 'T.order_id = O.order_id', 'INNER')
                ->join(USER.' AS U', 'U.user_id = O.user_id', 'INNER')
                ->where("O.source", "7")
                ->where("O.order_id", $order_id);
        $result = $this->db->get()->row_array();
        return $result;
    }

}

/* End of file Finance_model.php */
/* Location: ./application/models/Finance_model.php */
