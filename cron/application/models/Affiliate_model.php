<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Affiliate_model extends MY_Model {
    
    public $db_user ;
    public function __construct()  {
       	parent::__construct();
		$this->db_user		= $this->load->database('db_user', TRUE);
        
    }

    /**
     * This function used to inform about referal program
     * @param      
     */
    function notify_user_for_affiliate_program() {
        
        $this->load->model('notification/Notify_nosql_model');
        $current_date = format_date();
        //Case 1- User is not an affiliate. 
        //Case 2 - User is an affiliate. But he did not earn any commission in last 30 days.
        $this->db_user->select('U.user_id, U.user_name,IF(AL.device_type=1,GROUP_CONCAT(AL.device_id),"") device_ids,IF(AL.device_type=2,GROUP_CONCAT(AL.device_id),"") ios_device_ids, SUM(IFNULL(O.winning_amount, 0)),AL.device_type',false);
        $this->db_user->from(USER.' U');
		$this->db_user->join(ACTIVE_LOGIN.' AL','AL.user_id=U.user_id AND AL.device_id IS NOT NULL');
        $this->db_user->join(ORDER.' O',"O.user_id=U.user_id AND O.winning_amount > 0 AND O.source IN (320,321) AND O.date_added >= '{$current_date}'", 'LEFT');
        $this->db_user->where('U.status',1);
        $this->db_user->group_by('U.user_id');
        $user_details = $this->db_user->get()->result_array();
        
        $notify_data = array();            
        $notify_data["source_id"] = 0;
        $notify_data["notification_destination"] = 2; //Push
        $notify_data["added_date"] = $current_date;
        $notify_data["modified_date"] = $current_date;
        $notify_data["notification_type"] = 422;     
        $notify_data['custom_notification_subject'] = '🤝 Partner Us 🤝';
        $push_notification_text = array(
            'Jack earned ₹1500 cash from our Affiliate program. You too can win by clicking here 💰💰',
            'Sheeba earned $3450 cash by being our Affiliate. Explore now to check our Affiliate program  🤑💰',
            'Pallavi earned €1060 cash. Check out our Affiliate program for details now 🤑💰'
        ); 
        foreach($user_details as $user) {    

            if($user['winning_amount'] > 0) {
                continue;
            }
            if(empty($user['device_ids']) && empty($user["ios_device_ids"])) {
                continue;
            } 

            $notify_data["device_ids"]          = isset($user['device_ids']) ? $user['device_ids'] : '';            
            $notify_data['ios_device_ids']      = isset($user["ios_device_ids"]) ? $user["ios_device_ids"] : '';
            
            $notify_data["user_id"]     = $user['user_id'];
            $notify_data["content"] 	= json_encode(array('user_id' => $user['user_id'], 'device_ids' => $user['device_ids'], 'banner_image' => 'loudspeaker.png'));
            
            $notify_data['custom_notification_text'] = $push_notification_text[array_rand($push_notification_text)];
            $this->Notify_nosql_model->send_notification($notify_data);
        }
    }
}