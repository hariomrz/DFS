<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Referral_model extends MY_Model {
    
    public $db_user ;
    public function __construct()  {
       	parent::__construct();
		$this->db_user		= $this->load->database('db_user', TRUE);
    }

    /**
     * This function used to get user referral count
     * @param      
     * @return     [int]
     */
    function get_user_referral_count($user_id) {
        $affiliate_type = array(1,19,20,21);
        $query = $this->db_user->select(
                "sum(case when status = 1 and affiliate_type IN(" . implode(',', $affiliate_type) . ") then 1 else 0 end) as total_joined", FALSE)
                ->from(USER_AFFILIATE_HISTORY)
                ->where("user_id", $user_id)
                ->where("is_referral", 1)
                ->get();
        $num = $query->num_rows();
        $total_joined = 0;
        if($num > 0) {
            $row= $query->row_array();
            $total_joined   = $row['total_joined'];            
        }        
        return (int)$total_joined;
    }

    /**
     * This function used to inform about referal program to earning bonus / cash
     * @param      
     */
    function notify_user_for_more_referral() {
        $this->load->model('notification/Notify_nosql_model');
        
        $this->db_user->select('*');
        $this->db_user->from(AFFILIATE_MASTER);
        $this->db_user->where_in("affiliate_type", array(19,20,21));
        $affililate_master_detail = $this->db_user->get()->result_array();
        if(!empty($affililate_master_detail)) {
            $affililate_master_detail = array_column($affililate_master_detail, 'affiliate_type');
            
            $user_details = $this->get_all_user_with_device_ids();

            $current_date = format_date();
            $notify_data = array();            
            $notify_data["source_id"] = 0;
            $notify_data["notification_destination"] = 2; //Push
            $notify_data["added_date"] = $current_date;
            $notify_data["modified_date"] = $current_date;
            foreach($user_details as $user) {    
                if(empty($user['device_ids']) && empty($user["ios_device_ids"])) {
                    continue;
                } 
                $friend_count = 0;
                $total_joined = $this->get_user_referral_count($user['user_id']);
                if($total_joined < 5 && in_array(19, $affililate_master_detail)) { //$affiliate_type = 19;
                    $notify_data["notification_type"] = 156;      
                    $friend_count = 5;               
                } else if($total_joined < 10 && in_array(20, $affililate_master_detail)) { //$affiliate_type = 20;
                    $notify_data["notification_type"] = 159;                     
                    $friend_count = 10;
                } else if($total_joined < 15 && in_array(21, $affililate_master_detail)) { //$affiliate_type = 21;
                    $friend_count = 15;
                    $notify_data["notification_type"] = 162;                     
                } else {
                    continue;
                }
                $friend_count = $friend_count - $total_joined;
                $notify_data["user_id"] = $user['user_id'];
                $notify_data["device_ids"]          = isset($user['device_ids']) ? $user['device_ids'] : '';            
                $notify_data['ios_device_ids']      = isset($user["ios_device_ids"]) ? $user["ios_device_ids"] : '';
                $notify_data["content"] 		= json_encode(array('user_id' => $user['user_id'], 'device_ids' => $user['device_ids'], 'banner_image' => 'cash-coins.png'));
                $notify_data['custom_notification_subject'] = 'Steel Yourself 😃😃';
                $notify_data['custom_notification_text'] = 'You are '.$friend_count.' friends away from winning cash. Good Luck 👍💰';
                $this->Notify_nosql_model->send_notification($notify_data);
            }
        }              
    }

    /**
     * This function used to inform about referal program
     * @param      
     */
    function notify_user_for_referral() {
        $this->load->model('notification/Notify_nosql_model');
        
        $this->db_user->select('*');
        $this->db_user->from(AFFILIATE_MASTER);
        $this->db_user->where("affiliate_type", 1);
        $this->db_user->where("status", 1);
        $affililate_master_detail = $this->db_user->get()->result_array();
        if(!empty($affililate_master_detail)) {
            $affililate_master_detail = array_column($affililate_master_detail, 'affiliate_type');
            
            $user_details = $this->get_all_user_with_device_ids();

            $current_date = format_date();
            $notify_data = array();            
            $notify_data["source_id"] = 0;
            $notify_data["notification_destination"] = 2; //Push
            $notify_data["added_date"] = $current_date;
            $notify_data["modified_date"] = $current_date;
            $notify_data["notification_type"] = 37;     
            $notify_data['custom_notification_subject'] = 'Come On!';
            $push_notification_text = array(
                'Did you check our Refer a Friend page? 😵😵 More friends can give you more money. 👥💰👥💰',
                'If we get all friends who give us money, life would be so much fun🏄‍♂️ We have an idea for you. 🤩🤩 Click to check.',
                'Are you thinking of increasing your wallet balance ⁉️ Me too. Let\'s get going. Check out how 🤑🤑😵‍💫'
            ); 
            foreach($user_details as $user) {    
                if(empty($user['device_ids']) && empty($user["ios_device_ids"])) {
                    continue;
                } 
    
                $notify_data["device_ids"]          = isset($user['device_ids']) ? $user['device_ids'] : '';            
                $notify_data['ios_device_ids']      = isset($user["ios_device_ids"]) ? $user["ios_device_ids"] : '';
                
                $notify_data["user_id"] = $user['user_id'];
                $notify_data["content"] 		= json_encode(array('user_id' => $user['user_id'], 'device_ids' => $user['device_ids'], 'banner_image' => 'cash-coins.png'));
                
                $notify_data['custom_notification_text'] = $push_notification_text[array_rand($push_notification_text)];
                $this->Notify_nosql_model->send_notification($notify_data);
            }
        }              
    }
}