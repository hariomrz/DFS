<?php
class Subscription_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * this method is using at 2 places , 
     * one is to check the existance of package on basis of subscription id and 
     * second one it to filter and list out all the packages. to show in frontend.
     * @param either subscription id or type and type will be optional.
     * @return single row in case one and an array in second case.
     */
    public function check_subscription($data)
    {
        $result = $this->db->select("*")
        ->from(SUBSCRIPTION);
        if(isset($data['subscription_id']) && $data['subscription_id']!='')
        {
            $this->db->where('subscription_id',$data['subscription_id']);
        }
        if(isset($data['type']) && $data['type']!='')
        {
            if($data['type']==1)
            {
                $this->db->where('android_id IS NOT NULL')
                ->where('ios_id IS NULL');    
            }
            else if($data['type']==2)
            {
                $this->db->where('android_id IS NULL')
                ->where('ios_id IS NOT NULL');  
            }
        }
        
        $result = $this->db->where('is_deleted','0')
        ->where('status','1');
        if(isset($data['subscription_id']) && $data['subscription_id']!='')
        {
           $result = $this->db->get()->row_array();
        }else{
            $result = $this->db->get()->result_array();
        }
        if($result)
        {
            return $result;
        }
        return false;
    } 

    public function check_already_subscribed($data)
    {
        $today = format_date();
        $result = $this->db->select("id,subscription_id,type,start_date,expiry_date")
        ->from(USER_SUBSCRIPTION)
        ->where('subscription_id',$data['subscription_id'])
        // ->where('type',$data['type'])
        ->where('user_id',$this->user_id)
        ->where('status','1')
        ->where('expiry_date >',$today)
        ->get()->row_array();
        if($result)
        {
            return 1;
        }

        $other_plan_check = $this->db->select('id')
        ->from(USER_SUBSCRIPTION)
        // ->where('type',$data['type'])
        ->where('user_id',$this->user_id)
        ->where('status','1')
        ->where('expiry_date >',$today)
        ->get()->row_array();
        if($other_plan_check)
        {
            return 2;
        }

        return 0;
    }

    public function cancel_subscription($post_data)
    {
        $this->db->where("user_id",$this->user_id)
        ->where("subscription_id",$post_data['subscription_id'])
        ->update(USER_SUBSCRIPTION,["status"=>'0']);
        $result = $this->db->affected_rows();
        if($result)
        {
            $user_subscription_detail = $this->db->select("S.name,S.amount,
            (CASE 
        WHEN US.type=1 THEN S.android_id
        WHEN US.type=2 THEN S.ios_id END) as product_id,US.receipt_id")
            ->from(SUBSCRIPTION." S")
            ->join(USER_SUBSCRIPTION." US","S.subscription_id = US.subscription_id and US.status = '0'","INNER")
            // ->join(SUBSCRIPTION." S","US.subscription_id = S.subscription_id","INNER")
            // ->where("U.user_id",$this->user_id)
            ->where("S.subscription_id",$post_data['subscription_id'])
            ->get()->row_array();
            return $user_subscription_detail;
        }
        return array();
    }

    public function update_subscription($oerder_id,$update_data)
    {
        $new_exp_date = $update_data['new_exp_date'];
        
        $get_subs_details = $this->db->select('custom_data')
        ->from(ORDER)
        ->where(array("order_id" => $oerder_id))
        ->get()->row_array();
        $get_subs_details = json_decode($get_subs_details['custom_data'],true);

        $amount = (int)$get_subs_details['amount'];
        $this->db->update(TRANSACTION,["txn_amount"=>$amount],["order_id"=>$oerder_id]);

        $is_exist = $this->db->select('id')
        ->from(USER_SUBSCRIPTION)
        ->where(["user_id"=>$this->user_id,"subscription_id"=>$get_subs_details['subscription_id'],"type"=>$get_subs_details['type']])
        ->get()->row_array();
        if($is_exist)
        {
            $this->db->update(USER_SUBSCRIPTION,["status"=>'1',"expiry_date"=>$new_exp_date],["id"=>$is_exist['id']]);
        }else{
            $update_data  = array(
                "user_id"                   => $this->user_id,
                "subscription_id"           => $get_subs_details['subscription_id'],
                "receipt_id"                => $update_data['responce_code'],
                "type"                      => $get_subs_details['type'],
                "status"                    => '1',
                "start_date"                => $update_data['txn_date'],
                "expiry_date"               => $new_exp_date,
            );

            $this->db->insert(USER_SUBSCRIPTION,$update_data);
        }
        return true;
    }
}

//Location : uer/applicatio/modules/subscription/models/Subscription_model.php
?>